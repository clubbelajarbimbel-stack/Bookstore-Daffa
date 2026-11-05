<?php
// checkout.php - choose payment method (COD / bank transfer), enter address, place the order
require_once 'includes/config.php';
require_login();
if (current_role() === 'admin') go('admin/dashboard.php');   // admins cannot buy
$base = '';
$page_title = 'Checkout';

// ---------- 1. get the cart ----------
$rows = db_all("SELECT ci.quantity, b.id AS book_id, b.title, b.price, b.stock, b.cover_image, b.cover_color
                FROM cart_items ci JOIN books_data b ON ci.book_id = b.id WHERE ci.user_id = ?", 'i', [$_SESSION['user_id']]);
if (count($rows) === 0) go('cart.php');      // empty cart -> nothing to check out

$subtotal = 0;
foreach ($rows as $r) $subtotal += $r['price'] * $r['quantity'];
$shipping = SHIPPING_FEE;                    // >>> CHANGE HERE: the shipping fee is set in includes/config.php
$total    = $subtotal + $shipping;           // >>> CHANGE HERE: to add a discount or tax, change this line

// ---------- 2. start with the address saved in the user's profile ----------
$profile = db_one("SELECT address FROM users WHERE id = ?", 'i', [$_SESSION['user_id']]);
$address = $profile['address'] ?? '';
$method  = '';
$error   = '';

// ---------- 3. the form was submitted ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method  = $_POST['payment_method'] ?? '';
    $address = trim($_POST['address'] ?? '');
    $proof_name = null;     // file name of the transfer proof (only for bank transfer)

    // 3a. check the form
    if ($method !== 'cod' && $method !== 'transfer') {
        $error = 'Pilih metode pembayaran dulu ya.';
    } elseif ($address === '') {
        $error = 'Alamat pengiriman wajib diisi.';
    }

    // 3b. bank transfer needs a proof file (jpg / png / webp / pdf)
    if ($error === '' && $method === 'transfer') {
        $f = $_FILES['proof'] ?? null;
        if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) {
            $error = 'Upload bukti transfer dulu ya sebelum checkout.';
        } elseif ($f['error'] !== UPLOAD_ERR_OK) {
            $error = 'Upload bukti gagal. Coba file yang lebih kecil.';
        } elseif ($f['size'] > PROOF_MAX_BYTES) {
            $error = 'Ukuran bukti transfer maksimal ' . (PROOF_MAX_BYTES / 1024 / 1024) . ' MB.';
        } else {
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'pdf'])) {      // >>> CHANGE HERE: allowed file types
                $error = 'Format bukti harus JPG, PNG, WEBP, atau PDF.';
            } else {
                $dir = __DIR__ . '/assets/uploads/bukti/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $proof_name = 'bukti_' . time() . '_' . $_SESSION['user_id'] . '.' . $ext;   // unique file name
                if (!move_uploaded_file($f['tmp_name'], $dir . $proof_name)) {
                    $error = 'Gagal menyimpan bukti transfer. Pastikan folder assets/uploads/bukti bisa ditulis.';
                }
            }
        }
    }

    // 3c. check the stock one last time (someone may have bought the book meanwhile)
    if ($error === '') {
        foreach ($rows as $r) {
            $cur = db_one("SELECT stock FROM books_data WHERE id = ?", 'i', [$r['book_id']]);
            if (!$cur || (int)$cur['stock'] < (int)$r['quantity']) {
                $error = 'Stok "' . $r['title'] . '" tidak cukup. Perbarui keranjangmu dulu.';
                break;
            }
        }
    }

    // 3d. everything is OK -> save the order
    if ($error === '') {
        // short text like "Book A x2, Book B x1" (saved in orders.book_title)
        $titles = [];
        foreach ($rows as $r) $titles[] = $r['title'] . ' x' . $r['quantity'];
        $summary = mb_substr(implode(', ', $titles), 0, 150);

        // COD = pay later, transfer = waiting for the admin to check the proof
        $pay_status = ($method === 'transfer') ? 'menunggu_verifikasi' : 'belum_dibayar';

        // (1) the order itself
        $order_id = db_run("INSERT INTO orders (user_id, book_title, total, order_date, payment_method, payment_status, order_status, shipping_cost, shipping_address, payment_proof)
                            VALUES (?, ?, ?, CURDATE(), ?, ?, 'menunggu', ?, ?, ?)",
                           'isdssdss', [$_SESSION['user_id'], $summary, $total, $method, $pay_status, $shipping, $address, $proof_name]);

        // (2) every book of the order + (3) take it out of the stock
        foreach ($rows as $r) {
            db_run("INSERT INTO order_items (order_id, book_id, title, price, quantity) VALUES (?, ?, ?, ?, ?)",
                   'iisdi', [$order_id, $r['book_id'], $r['title'], $r['price'], $r['quantity']]);
            db_run("UPDATE books_data SET stock = stock - ? WHERE id = ?", 'ii', [$r['quantity'], $r['book_id']]);
        }

        // (4) empty the cart
        db_run("DELETE FROM cart_items WHERE user_id = ?", 'i', [$_SESSION['user_id']]);

        go('selesai.php?id=' . $order_id);
    }
}

// WhatsApp link: 0859... -> 62859...
$wa_digits = preg_replace('/\D+/', '', SHOP_WA);
if (strpos($wa_digits, '0') === 0) $wa_digits = '62' . substr($wa_digits, 1);

include 'includes/header.php';
?>

<h2>checkout</h2>
<p class="detail-desc">Periksa pesananmu, pilih metode pembayaran, lalu selesaikan checkout.</p>

<form class="co-grid" method="post" enctype="multipart/form-data">

  <!-- ========== LEFT: order summary ========== -->
  <div class="box">
    <h3>Ringkasan Pesanan</h3>
    <?php foreach ($rows as $r): ?>
      <div class="co-item">
        <div class="co-thumb" style="background:<?= e($r['cover_color']) ?>;">
          <?php if (!empty($r['cover_image'])): ?><img src="assets/uploads/<?= e($r['cover_image']) ?>" alt=""><?php endif; ?>
        </div>
        <div class="co-info">
          <b><?= e($r['title']) ?></b><br>
          <small><?= rupiah($r['price']) ?> &times; <?= (int)$r['quantity'] ?></small>
        </div>
        <b><?= rupiah($r['price'] * $r['quantity']) ?></b>
      </div>
    <?php endforeach; ?>

    <div class="co-sum">
      <div><span>Subtotal</span><span><?= rupiah($subtotal) ?></span></div>
      <div><span>Ongkir</span><span><?= rupiah($shipping) ?></span></div>
      <div class="co-grand"><span>Total Pembayaran</span><span><?= rupiah($total) ?></span></div>
    </div>
  </div>

  <!-- ========== RIGHT: payment + address ========== -->
  <div>
    <?php if ($error): ?><div class="form-msg"><?= e($error) ?></div><?php endif; ?>

    <div class="box">
      <h3>Metode Pembayaran</h3>

      <!-- >>> CHANGE HERE: to add a payment method, add another <label> like these,
           and also allow its value in checkout.php (step 3a) and in the database column payment_method -->
      <label class="pay-option">
        <input type="radio" name="payment_method" value="cod" <?= $method === 'cod' ? 'checked' : '' ?>>
        <b>🚚 Bayar di Tempat</b>
        <small>Anda akan melakukan pembayaran ketika buku sudah diterima.</small>
      </label>

      <label class="pay-option">
        <input type="radio" name="payment_method" value="transfer" <?= $method === 'transfer' ? 'checked' : '' ?>>
        <b>🏦 Transfer Bank</b>
        <small>Transfer manual, lalu upload bukti pembayarannya di sini.</small>
      </label>

      <!-- this box is only shown when "Transfer Bank" is selected (see the script below) -->
      <div class="transfer-box" id="transfer-box" style="display:none;">
        <p>Transfer ke rekening berikut, lalu upload bukti transfernya:</p>
        <p><b><?= e(BANK_NAME . ' - ' . BANK_ACCOUNT) ?></b><br>a.n. <?= e(BANK_HOLDER) ?></p>
        <p>Nominal transfer: <b><?= rupiah($total) ?></b></p>
        <p>💬 Konfirmasi ke <a href="https://wa.me/<?= $wa_digits ?>" target="_blank"><u><?= e(SHOP_WA) ?></u></a> setelah transfer</p>

        <label for="proof"><b>Upload Bukti Transfer</b></label><br>
        <input type="file" id="proof" name="proof" accept="image/jpeg,image/png,image/webp,application/pdf">
        <br><small>JPG, PNG, WEBP, atau PDF - maks. <?= (int)(PROOF_MAX_BYTES / 1024 / 1024) ?> MB.</small>
        <img id="proof-preview" alt="Pratinjau bukti transfer">
      </div>
    </div>

    <div class="box">
      <h3>Alamat Pengiriman</h3>
      <textarea class="field" name="address" placeholder="Nama jalan, nomor rumah, kota, kode pos" required><?= e($address) ?></textarea>
    </div>

    <div class="btn-row">
      <button class="btn btn-primary" type="submit">Checkout &rarr;</button>
      <a class="btn" href="cart.php">Back</a>
    </div>
  </div>
</form>

<script>
// Show the bank-transfer box only when "Transfer Bank" is chosen, and show a preview of the proof picture.
var radios   = document.querySelectorAll('input[name="payment_method"]');
var box      = document.getElementById('transfer-box');
var proof    = document.getElementById('proof');
var preview  = document.getElementById('proof-preview');

function updateBox() {
  var chosen = document.querySelector('input[name="payment_method"]:checked');
  var isTransfer = chosen && chosen.value === 'transfer';
  box.style.display = isTransfer ? 'block' : 'none';
  proof.required = isTransfer;                 // the proof file is required for transfer
}
for (var i = 0; i < radios.length; i++) {
  radios[i].addEventListener('change', updateBox);
}

proof.addEventListener('change', function () {
  var file = proof.files[0];
  if (file && file.type.indexOf('image/') === 0) {
    preview.src = URL.createObjectURL(file);
    preview.style.display = 'block';
  } else {
    preview.style.display = 'none';
  }
});

updateBox();   // run once when the page opens
</script>

<?php include 'includes/footer.php'; ?>
