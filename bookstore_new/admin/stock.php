<?php
// admin/stock.php - manage stock: type a number + Simpan, or use the - / + buttons
require_once '../includes/config.php';
require_admin();
$base = '../';
$page_title = 'Kelola Stok';
$msg = '';

// a stock form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
    $book_id = (int)$_POST['book_id'];

    if (isset($_POST['adjust'])) {
        // the - or + button: change by -1 or +1 (never below 0)
        db_run("UPDATE books_data SET stock = GREATEST(0, stock + ?) WHERE id = ?", 'ii', [(int)$_POST['adjust'], $book_id]);
    } else {
        // the Simpan button: set the exact number typed
        db_run("UPDATE books_data SET stock = ? WHERE id = ?", 'ii', [max(0, (int)($_POST['stock'] ?? 0)), $book_id]);
    }
    go('stock.php?saved=1');
}
if (isset($_GET['saved'])) $msg = 'Stok berhasil diperbarui.';

$search = trim($_GET['q'] ?? '');
$books = db_all("SELECT b.id, b.title, b.stock, b.cover_image, b.cover_color, c.name AS category_name
                 FROM books_data b LEFT JOIN categories c ON b.category_id = c.id
                 WHERE b.title LIKE ?
                 ORDER BY b.stock ASC, b.title ASC", 's', ['%' . $search . '%']);    // lowest stock first

include '../includes/header.php';
?>

<h2 style="text-align:center;">kelola stok buku</h2>
<p class="detail-desc" style="text-align:center;">Diurutkan dari stok paling sedikit. Stok berkurang otomatis saat ada pesanan, dan kembali otomatis kalau pesanan ditolak/dibatalkan.</p>
<?php if ($msg): ?><div class="form-msg ok"><?= e($msg) ?></div><?php endif; ?>

<form method="get">
  <input class="search-bar" type="text" name="q" placeholder="cari judul buku" value="<?= e($search) ?>">
</form>

<div class="table-wrap">
<table class="list-table">
  <tr><th>Cover</th><th>Judul</th><th>Kategori</th><th>Stok</th><th>Ubah stok</th></tr>
  <?php foreach ($books as $b): ?>
    <tr>
      <td>
        <div class="mini-cover" style="background:<?= e($b['cover_color']) ?>;">
          <?php if (!empty($b['cover_image'])): ?><img src="../assets/uploads/<?= e($b['cover_image']) ?>" alt=""><?php endif; ?>
        </div>
      </td>
      <td style="text-align:left;"><?= e($b['title']) ?></td>
      <td><?= e($b['category_name'] ?: '-') ?></td>
      <td><?= stock_badge($b['stock']) ?></td>
      <td>
        <form method="post" class="stock-form">
          <input type="hidden" name="book_id" value="<?= $b['id'] ?>">
          <!-- Simpan is first in the HTML so the Enter key saves; style="order:N" makes it appear LAST on screen -->
          <button class="btn btn-sm btn-primary" style="order:4;" type="submit">Simpan</button>
          <button class="btn btn-sm" style="order:1;" type="submit" name="adjust" value="-1">&minus;</button>
          <input class="field stock-input" style="order:2;" type="number" min="0" name="stock" value="<?= (int)$b['stock'] ?>">
          <button class="btn btn-sm" style="order:3;" type="submit" name="adjust" value="1">+</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
</div>

<div style="text-align:center;">
  <a class="btn" href="dashboard.php">Back</a>
</div>

<?php include '../includes/footer.php'; ?>
