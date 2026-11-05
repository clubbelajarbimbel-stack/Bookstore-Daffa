<?php
// register.php - create a new BUYER account
require_once 'includes/config.php';
$base = '';
$page_title = 'Register';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. read what the user typed in the form
    $name      = trim($_POST['name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $address   = trim($_POST['address'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // 2. check it
    if ($name === '' || $email === '' || $password === '') {
        $error = 'Nama, email, dan password wajib diisi.';
    } elseif ($password !== $password2) {
        $error = 'Konfirmasi password tidak cocok.';
    } elseif (db_one("SELECT id FROM users WHERE email = ?", 's', [$email])) {
        $error = 'Email sudah terdaftar. Silakan login.';
    } else {
        // 3. save the user (password is scrambled with password_hash, never saved as plain text)
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $new_id = db_run("INSERT INTO users (name, email, password, address, role) VALUES (?, ?, ?, ?, 'buyer')",
                         'ssss', [$name, $email, $hash, $address]);

        // 4. log the user in right away
        $_SESSION['user_id']   = $new_id;
        $_SESSION['user_name'] = $name;
        $_SESSION['role']      = 'buyer';
        go('books.php');
    }
}

include 'includes/header.php';
?>

<!-- >>> CHANGE HERE: to add a form field, add an <input> here, read it above with $_POST['...'],
     add the column in the database, and add it to the INSERT. -->
<div class="card-panel">
  <h2>REGISTER</h2>
  <?php if ($error): ?><div class="form-msg"><?= e($error) ?></div><?php endif; ?>
  <form method="post">
    <input class="field" type="text" name="name" placeholder="masukan nama" required>
    <input class="field" type="email" name="email" placeholder="masukan email" required>
    <input class="field" type="text" name="address" placeholder="masukan alamat">
    <input class="field" type="password" name="password" placeholder="masukan Password" required>
    <input class="field" type="password" name="password2" placeholder="ulangi Password" required>
    <button class="btn btn-primary btn-block" type="submit">confirm</button>
  </form>
  <p style="text-align:center;">Sudah punya akun? <a href="login.php"><u>Login di sini</u></a></p>
</div>

<?php include 'includes/footer.php'; ?>
