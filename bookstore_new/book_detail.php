<?php
// book_detail.php - one book + "Add to cart" button
require_once 'includes/config.php';
$base = '';

$id = (int)($_GET['id'] ?? 0);
$book = db_one("SELECT * FROM books_data WHERE id = ?", 'i', [$id]);
if (!$book) go('books.php');          // book not found -> back to the list

$msg = '';

// the "Add to cart" button was pressed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!is_logged_in()) go('login.php');

    // is this book already in this user's cart?
    $row = db_one("SELECT id, quantity FROM cart_items WHERE user_id = ? AND book_id = ?", 'ii', [$_SESSION['user_id'], $id]);
    $in_cart = $row ? (int)$row['quantity'] : 0;

    if ((int)$book['stock'] <= 0) {
        $msg = 'Maaf, stok buku ini sedang habis.';
    } elseif ($in_cart + 1 > (int)$book['stock']) {
        $msg = 'Stok tidak cukup. Di keranjangmu sudah ada ' . $in_cart . ' buku ini dan stok tersisa ' . (int)$book['stock'] . '.';
    } elseif ($row) {
        // already in cart -> quantity + 1
        db_run("UPDATE cart_items SET quantity = quantity + 1 WHERE id = ?", 'i', [$row['id']]);
    } else {
        // not in cart yet -> add a new row
        db_run("INSERT INTO cart_items (user_id, book_id, quantity) VALUES (?, ?, 1)", 'ii', [$_SESSION['user_id'], $id]);
    }

    if ($msg === '') go('cart.php');   // success -> show the cart
}

$page_title = $book['title'];
include 'includes/header.php';
?>

<div class="detail-wrap">
  <div class="detail-cover" style="background: <?= e($book['cover_color']) ?>;">
    <?php if (!empty($book['cover_image'])): ?>
      <img src="assets/uploads/<?= e($book['cover_image']) ?>" alt="<?= e($book['title']) ?>">
    <?php endif; ?>
  </div>

  <div class="detail-info">
    <h2><?= e($book['title']) ?></h2>
    <p class="detail-desc"><?= nl2br(e($book['description'])) ?></p>
    <div class="detail-price"><?= rupiah($book['price']) ?></div>
    <p><?= stock_badge($book['stock']) ?></p>

    <?php if ($msg): ?><div class="form-msg"><?= e($msg) ?></div><?php endif; ?>

    <div class="btn-row">
      <form method="post">
        <?php if ((int)$book['stock'] > 0): ?>
          <button class="btn btn-primary" type="submit" name="add_to_cart" value="1">Add to cart</button>
        <?php else: ?>
          <button class="btn btn-primary" type="button" disabled>Stok habis</button>
        <?php endif; ?>
      </form>
      <a class="btn" href="books.php">Back</a>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
