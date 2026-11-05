<?php
// login.php - log in with email + password
require_once 'includes/config.php';
$base = '';
$page_title = 'Login';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email dan password wajib diisi.';
    } else {
        // find the user with this email
        $user = db_one("SELECT id, name, password, role FROM users WHERE email = ?", 's', [$email]);

        // password_verify compares the typed password with the scrambled one in the database
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role']      = $user['role'];
            // >>> CHANGE HERE: where to go after login (admin / buyer)
            go($user['role'] === 'admin' ? 'admin/dashboard.php' : 'books.php');
        } else {
            $error = 'Email atau password salah.';
        }
    }
}

include 'includes/header.php';
?>

<div class="card-panel">
  <h2>LOGIN</h2>
  <?php if ($error): ?><div class="form-msg"><?= e($error) ?></div><?php endif; ?>
  <form method="post">
    <input class="field" type="email" name="email" placeholder="masukan email" required>
    <input class="field" type="password" name="password" placeholder="masukan Password" required>
    <button class="btn btn-primary btn-block" type="submit">confirm</button>
  </form>
  <p style="text-align:center;">Belum punya akun? <a href="register.php"><u>Register di sini</u></a></p>
</div>

<?php include 'includes/footer.php'; ?>
