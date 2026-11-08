<?php
// admin/book_form.php - ADD a new book  (book_form.php)  or  EDIT / DELETE a book  (book_form.php?id=5)
require_once '../includes/config.php';
require_admin();
$base = '../';

$id = (int)($_GET['id'] ?? 0);      // 0 = adding a new book

// empty book (for the "add" form)
$book = ['title' => '', 'description' => '', 'price' => '', 'stock' => 0,
         'category_id' => null, 'cover_color' => '#4B4F58', 'cover_image' => null];

// editing -> load the existing book
if ($id) {
    $found = db_one("SELECT * FROM books_data WHERE id = ?", 'i', [$id]);
    if ($found) $book = $found;
}

$categories = db_all("SELECT * FROM categories ORDER BY name");
$error = '';

// ---------- DELETE button ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete']) && $id) {
    db_run("DELETE FROM books_data WHERE id = ?", 'i', [$id]);
    go('dashboard.php');
}

// ---------- SAVE button (add or edit) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    // 1. read the form
    $title  = trim($_POST['title'] ?? '');
    $desc   = trim($_POST['description'] ?? '');
    $price  = (float)($_POST['price'] ?? 0);
    $stock  = max(0, (int)($_POST['stock'] ?? 0));
    $cat_id = (int)($_POST['category_id'] ?? 0) ?: null;     // empty choice -> null
    $color  = $_POST['cover_color'] ?? '#4B4F58';

    // 2. cover picture: keep the old one unless a new file was chosen
    $cover_image = $book['cover_image'];
    if (!empty($_FILES['cover_image']['name']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {          // >>> CHANGE HERE: allowed picture types
            $filename = uniqid('cover_') . '.' . $ext;                         // unique name so files never overwrite each other
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], '../assets/uploads/' . $filename)) {
                $cover_image = $filename;
            }
        } else {
            $error = 'Format gambar harus jpg, jpeg, png, gif, atau webp.';
        }
    }

    // 3. check + save
    if ($title === '') {
        $error = 'Judul buku wajib diisi.';
    }
    if ($error === '') {
        if ($id) {
            db_run("UPDATE books_data SET title=?, description=?, price=?, stock=?, category_id=?, cover_color=?, cover_image=? WHERE id=?",
                   'ssdiissi', [$title, $desc, $price, $stock, $cat_id, $color, $cover_image, $id]);
        } else {
            db_run("INSERT INTO books_data (title, description, price, stock, category_id, cover_color, cover_image) VALUES (?, ?, ?, ?, ?, ?, ?)",
                   'ssdiiss', [$title, $desc, $price, $stock, $cat_id, $color, $cover_image]);
        }
        go('dashboard.php');
    }
}

$page_title = $id ? 'Edit Buku' : 'Tambah Buku';
include '../includes/header.php';
?>

<?php if ($error): ?><div class="form-msg"><?= e($error) ?></div><?php endif; ?>

<div class="detail-wrap">
  <!-- preview of the cover -->
  <div class="detail-cover" style="background:<?= e($book['cover_color']) ?>;">
    <?php if (!empty($book['cover_image'])): ?>
      <img id="cover-preview" src="../assets/uploads/<?= e($book['cover_image']) ?>" alt="">
    <?php else: ?>
      <img id="cover-preview" src="" alt="" style="display:none;">
    <?php endif; ?>
  </div>

  <div class="detail-info">
    <!-- >>> CHANGE HERE: to add a field (e.g. author): add an <input> here, read it in step 1 above,
         add the column in the database, then add it to BOTH the UPDATE and the INSERT (and the types: s / i / d) -->
    <form method="post" enctype="multipart/form-data">
      <input class="field" type="text" name="title" placeholder="add Title" value="<?= e($book['title']) ?>" required>
      <textarea class="field" name="description" placeholder="Add description"><?= e($book['description']) ?></textarea>
      <input class="field" type="number" min="0" name="price" placeholder="Harga dalam Rupiah, contoh: 75000" value="<?= e($book['price']) ?>" required>

      <label>📦 Stok buku</label>
      <input class="field" type="number" min="0" name="stock" placeholder="Jumlah stok, contoh: 20" value="<?= (int)$book['stock'] ?>" required>

      <select class="field" name="category_id">
        <option value="">Pilih kategori</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= $c['id'] ?>" <?= (int)$book['category_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
        <?php endforeach; ?>
      </select>

      <label>📷 Upload gambar sampul buku di sini</label><br>
      <input type="file" name="cover_image" accept="image/*" onchange="showPreview(this)">
      <br><br>

      <label>Warna cover (dipakai kalau tidak upload gambar)</label>
      <input class="field" type="color" name="cover_color" value="<?= e($book['cover_color']) ?>" style="height:56px;">

      <div class="btn-row">
        <button class="btn btn-primary" type="submit" name="save" value="1">Add</button>
        <a class="btn" href="dashboard.php">Back</a>
        <?php if ($id): ?>
          <button class="btn btn-danger" type="submit" name="delete" value="1" onclick="return confirm('Hapus buku ini?')">delete book</button>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<script>
// show the chosen picture in the preview box before saving
function showPreview(input) {
  if (input.files && input.files[0]) {
    var img = document.getElementById('cover-preview');
    img.src = URL.createObjectURL(input.files[0]);
    img.style.display = 'block';
  }
}
</script>

<?php include '../includes/footer.php'; ?>
