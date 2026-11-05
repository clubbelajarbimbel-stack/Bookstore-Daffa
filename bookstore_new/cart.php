<?php
// cart.php - the logged-in user's shopping cart
require_once 'includes/config.php';
require_login();
$base = '';
$page_title = 'Keranjang';

// the "hapus" (remove) button was pressed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_id'])) {
    db_run("DELETE FROM cart_items WHERE id = ? AND user_id = ?", 'ii', [(int)$_POST['remove_id'], $_SESSION['user_id']]);
    go('cart.php');
}

// get the cart rows (cart_items joined with the book info)
$rows = db_all("SELECT ci.id AS cart_id, ci.quantity, b.* FROM cart_items ci
                JOIN books_data b ON ci.book_id = b.id WHERE ci.user_id = ?", 'i', [$_SESSION['user_id']]);

// total price + check that no book is more than the stock
$total = 0;
$stock_problem = false;
foreach ($rows as $r) {
    $total += $r['price'] * $r['quantity'];
    if ((int)$r['quantity'] > (int)$r['stock']) $stock_problem = true;
}

include 'includes/header.php';
?>

<h2>keranjang buku anda</h2>

<div class="cart-grid">
  <?php if (count($rows) === 0): ?>
    <p>Keranjang kamu masih kosong. <a href="books.php"><u>Cari buku</u></a></p>
  <?php endif; ?>

  <?php foreach ($rows as $r): ?>
    <div class="cart-item">
      <div class="book-cover" style="background: <?= e($r['cover_color']) ?>;"><?= cover_inner($r) ?></div>
      <div class="book-title-sm"><?= e($r['title']) ?> &times; <?= $r['quantity'] ?></div>
      <div class="book-price"><?= rupiah($r['price'] * $r['quantity']) ?></div>
      <?php if ((int)$r['quantity'] > (int)$r['stock']): ?>
        <span class="stock-badge stock-out">Stok tersisa <?= max(0, (int)$r['stock']) ?></span>
      <?php endif; ?>
      <form method="post">
        <button class="cart-remove" type="submit" name="remove_id" value="<?= $r['cart_id'] ?>">hapus</button>
      </form>
    </div>
  <?php endforeach; ?>
</div>

<?php if (count($rows) > 0): ?>
  <p><b>Total: <?= rupiah($total) ?></b></p>
  <?php if ($stock_problem): ?>
    <div class="form-msg">Ada buku di keranjang yang jumlahnya melebihi stok. Hapus dulu buku tersebut, lalu tambahkan lagi sesuai stok yang tersedia.</div>
  <?php endif; ?>
  <div class="btn-row">
    <?php if (!$stock_problem): ?>
      <a class="btn btn-primary" href="checkout.php">bayar sekarang</a>
    <?php endif; ?>
    <a class="btn" href="books.php">back</a>
  </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
