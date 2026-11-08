<?php
// admin/dashboard.php - admin home: numbers, menu buttons, and the book list (click a book to edit it)
require_once '../includes/config.php';
require_admin();
$base = '../';                 // pages inside admin/ need '../' to find css and images
$page_title = 'Kelola Buku';

$search = trim($_GET['q'] ?? '');
$books  = db_all("SELECT * FROM books_data WHERE title LIKE ? ORDER BY created_at DESC", 's', ['%' . $search . '%']);

// numbers at the top: how many titles, total stock, low stock, out of stock
$sum = db_one("SELECT COUNT(*) AS titles,
                      COALESCE(SUM(stock), 0) AS total_stock,
                      COALESCE(SUM(stock <= 0), 0) AS empty_cnt,
                      COALESCE(SUM(stock > 0 AND stock <= " . (int)LOW_STOCK_LIMIT . "), 0) AS low_cnt
               FROM books_data");

// how many orders are waiting for the admin
$pending = (int)db_one("SELECT COUNT(*) AS n FROM orders WHERE order_status = 'menunggu'")['n'];

// how many cancelled orders still need a refund
$refund = (int)db_one("SELECT COUNT(*) AS n FROM orders WHERE admin_note LIKE '%PERLU REFUND%'")['n'];

include '../includes/header.php';
?>

<form method="get">
  <input class="search-bar" type="text" name="q" placeholder="change youre book" value="<?= e($search) ?>">
</form>

<!-- number boxes. >>> CHANGE HERE: labels, or add another box -->
<div class="stat-row">
  <div class="stat-card"><div class="stat-num"><?= (int)$sum['titles'] ?></div><div class="stat-lbl">Judul buku</div></div>
  <div class="stat-card"><div class="stat-num"><?= (int)$sum['total_stock'] ?></div><div class="stat-lbl">Total stok</div></div>
  <div class="stat-card <?= $sum['low_cnt'] > 0 ? 'stat-warn' : '' ?>"><div class="stat-num"><?= (int)$sum['low_cnt'] ?></div><div class="stat-lbl">Stok menipis</div></div>
  <div class="stat-card <?= $sum['empty_cnt'] > 0 ? 'stat-bad' : '' ?>"><div class="stat-num"><?= (int)$sum['empty_cnt'] ?></div><div class="stat-lbl">Stok habis</div></div>
  <a class="stat-card <?= $pending > 0 ? 'stat-warn' : '' ?>" href="orders.php"><div class="stat-num"><?= $pending ?></div><div class="stat-lbl">Pesanan menunggu</div></a>
  <a class="stat-card <?= $refund > 0 ? 'stat-bad' : '' ?>" href="orders.php?status=dibatalkan"><div class="stat-num"><?= $refund ?></div><div class="stat-lbl">Perlu refund</div></a>
</div>

<!-- menu buttons. >>> CHANGE HERE: rename / add / remove admin buttons -->
<div class="btn-row">
  <a class="btn btn-primary" href="orders.php">pesanan masuk<?= $pending > 0 ? ' (' . $pending . ')' : '' ?></a>
  <a class="btn" href="stock.php">kelola stok</a>
  <a class="btn" href="book_form.php">add book</a>
  <a class="btn" href="categories.php">add categories</a>
  <a class="btn" href="categories.php">edit categories</a>
  <a class="btn" href="users.php">daftar user &amp; pesanan</a>
</div>

<br>

<!-- the books. Clicking one opens book_form.php?id=... to edit it -->
<div class="book-grid">
  <?php foreach ($books as $b): ?>
    <a class="book-card" href="book_form.php?id=<?= $b['id'] ?>">
      <div class="book-cover" style="background: <?= e($b['cover_color']) ?>;"><?= cover_inner($b, '../') ?></div>
      <div class="book-title-sm"><?= e($b['title']) ?></div>
      <div class="book-price"><?= rupiah($b['price']) ?></div>
      <?= stock_badge($b['stock']) ?>
    </a>
  <?php endforeach; ?>
</div>

<?php include '../includes/footer.php'; ?>
