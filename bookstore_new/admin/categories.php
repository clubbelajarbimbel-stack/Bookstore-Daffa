<?php
// admin/categories.php - ADD a category, and list the categories (click one to rename it)
require_once '../includes/config.php';
require_admin();
$base = '../';
$page_title = 'Kategori';
$error = '';

// "Add" button
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $name = trim($_POST['name'] ?? '');
    if ($name === '') {
        $error = 'Nama kategori wajib diisi.';
    } else {
        db_run("INSERT IGNORE INTO categories (name) VALUES (?)", 's', [$name]);   // IGNORE = skip if the name already exists
        go('categories.php');
    }
}

// Delete a category (it works when a form posts "delete_id"; there is no delete button on the page yet).
// Books of a deleted category are NOT deleted - their category just becomes empty.
// >>> CHANGE HERE: to get a delete button, add a small <form method="post" action="categories.php"> in
//     category_edit.php with a button named delete_id whose value is the category id.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    db_run("DELETE FROM categories WHERE id = ?", 'i', [(int)$_POST['delete_id']]);
    go('categories.php');
}

$categories = db_all("SELECT * FROM categories ORDER BY name");

include '../includes/header.php';
?>

<h2 style="text-align:center;">tambahan kategori baru</h2>
<?php if ($error): ?><div class="form-msg"><?= e($error) ?></div><?php endif; ?>
<form method="post" style="max-width:600px; margin:0 auto 40px;">
  <input class="field" type="text" name="name" placeholder="categories name" required>
  <div class="btn-row" style="justify-content:center;">
    <button class="btn btn-primary" type="submit" name="add" value="1">Add</button>
  </div>
</form>

<h2 style="text-align:center;">edit kategori mu</h2>
<div class="chip-row" style="justify-content:center;">
  <?php foreach ($categories as $c): ?>
    <a class="chip" href="category_edit.php?id=<?= $c['id'] ?>"><?= e($c['name']) ?></a>
  <?php endforeach; ?>
</div>

<div style="text-align:center; margin-top:30px;">
  <a class="btn" href="dashboard.php">Back</a>
</div>

<?php include '../includes/footer.php'; ?>
