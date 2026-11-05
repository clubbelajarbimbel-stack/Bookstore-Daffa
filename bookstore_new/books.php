<?php
// books.php - list of all books, with SEARCH (by title) and CATEGORY filter
require_once 'includes/config.php';
$base = '';
$page_title = 'Books';

// what the user typed in the search box / which category they clicked (from the URL: books.php?q=...&category=...)
$search = trim($_GET['q'] ?? '');
$cat_id = (int)($_GET['category'] ?? 0);

$categories = db_all("SELECT * FROM categories ORDER BY name");

// build the query: always search by title, add the category filter only if one is chosen
$sql    = "SELECT * FROM books_data WHERE title LIKE ?";
$types  = 's';
$params = ['%' . $search . '%'];
if ($cat_id > 0) {
    $sql     .= " AND category_id = ?";
    $types   .= 'i';
    $params[] = $cat_id;
}
$sql .= " ORDER BY created_at DESC";      // >>> CHANGE HERE: sort order (e.g. "ORDER BY title ASC" or "ORDER BY price ASC")

$books = db_all($sql, $types, $params);

include 'includes/header.php';
?>

<form method="get">
  <input class="search-bar" type="text" name="q" placeholder="search your book here" value="<?= e($search) ?>">
</form>

<!-- category buttons -->
<div class="chip-row">
  <a class="chip <?= $cat_id === 0 ? 'active' : '' ?>" href="books.php?q=<?= urlencode($search) ?>">All</a>
  <?php foreach ($categories as $c): ?>
    <a class="chip <?= $cat_id === (int)$c['id'] ? 'active' : '' ?>"
       href="books.php?q=<?= urlencode($search) ?>&category=<?= $c['id'] ?>"><?= e($c['name']) ?></a>
  <?php endforeach; ?>
</div>

<!-- book cards. >>> CHANGE HERE: to show more info on each card (e.g. category), add a line inside .book-card -->
<div class="book-grid">
  <?php if (count($books) === 0): ?>
    <p>Tidak ada buku ditemukan.</p>
  <?php endif; ?>

  <?php foreach ($books as $b): ?>
    <a class="book-card" href="book_detail.php?id=<?= $b['id'] ?>">
      <div class="book-cover" style="background: <?= e($b['cover_color']) ?>;"><?= cover_inner($b) ?></div>
      <div class="book-title-sm"><?= e($b['title']) ?></div>
      <div class="book-price"><?= rupiah($b['price']) ?></div>
      <?= stock_badge($b['stock']) ?>
    </a>
  <?php endforeach; ?>
</div>

<?php include 'includes/footer.php'; ?>
