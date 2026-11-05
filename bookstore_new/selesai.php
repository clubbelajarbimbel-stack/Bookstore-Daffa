<?php
// selesai.php - "Order successful" page shown after checkout
require_once 'includes/config.php';
require_login();
$base = '';

$id = (int)($_GET['id'] ?? 0);
$o = db_one("SELECT * FROM orders WHERE id = ? AND user_id = ?", 'ii', [$id, $_SESSION['user_id']]);
if (!$o) go('orders.php');

// >>> CHANGE HERE: the messages. First block = bank transfer, second block = pay on delivery (COD)
if ($o['payment_method'] === 'transfer') {
    $title = 'Bukti Terkirim!';
    $sub   = 'Terima kasih, bukti pembayaranmu sudah kami terima.';
    $head  = 'Menunggu konfirmasi admin';
    $desc  = 'Admin akan memeriksa bukti transfermu. Setelah dikonfirmasi, status pesanan berubah menjadi "Sudah dibayar" dan buku segera dikemas.';
} else {
    $title = 'Pesanan Berhasil!';
    $sub   = 'Terima kasih, pesananmu sudah kami catat.';
    $head  = 'Pesananmu segera diproses!';
    $desc  = 'Siapkan pembayaran saat buku sudah sampai di alamat tujuan. Admin akan mengonfirmasi pesananmu lebih dulu.';
}

$page_title = $title;
include 'includes/header.php';
?>

<div class="box done-card">
  <div class="done-check">&#10003;</div>
  <h2><?= e($title) ?></h2>
  <p><?= e($sub) ?></p>

  <h3><?= e($head) ?></h3>
  <p class="detail-desc"><?= e($desc) ?></p>
  <p>🚚 <b>Estimasi tiba:</b> 2 – 3 hari kerja</p>   <!-- >>> CHANGE HERE: delivery estimate text -->

  <div class="done-meta">
    <span>No. pesanan <b>#<?= (int)$o['id'] ?></b></span>
    <span>Metode <b><?= method_label($o['payment_method']) ?></b></span>
    <span>Total <b><?= rupiah($o['total']) ?></b></span>
  </div>

  <div class="btn-row" style="justify-content:center;">
    <a class="btn btn-primary" href="orders.php">Lihat Pesanan Saya &rarr;</a>
    <a class="btn" href="books.php">Back</a>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
