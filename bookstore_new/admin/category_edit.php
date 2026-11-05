<?php
// admin/category_edit.php - rename ONE category (category_edit.php?id=3)
require_once '../includes/config.php';
require_admin();
$base = '../';
$page_title = 'Ubah Kategori';

$id  = (int)($_GET['id'] ?? 0);
$cat = db_one("SELECT * FROM categories WHERE id = ?", 'i', [$id]);
if (!$cat) go('categories.php');

// "change" button
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change'])) {
    $name = trim($_POST['name'] ?? '');
    if ($name !== '') {
        db_run("UPDATE categories SET name = ? WHERE id = ?", 'si', [$name, $id]);
    }
    go('categories.php');
}

include '../includes/header.php';
?>

<h2 style="text-align:center;">ubah kategori baru</h2>
<form method="post" style="max-width:600px; margin:0 auto;">
  <input class="field" type="text" name="name" value="<?= e($cat['name']) ?>" required>
  <div class="btn-row" style="justify-content:center;">
    <button class="btn btn-primary" type="submit" name="change" value="1">change</button>
    <a class="btn btn-danger" href="categories.php">cancel</a>
  </div>
</form>

<?php include '../includes/footer.php'; ?>
