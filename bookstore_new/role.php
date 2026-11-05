<?php
// role.php - "Who are you" screen: choose Admin or Buyer (saved in the database).
// Not linked in the menu - open it by typing  localhost/bookstore_new/role.php  after logging in.
require_once 'includes/config.php';
require_login();
$base = '';
$page_title = 'Who are you';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role'])) {
    $role = ($_POST['role'] === 'admin') ? 'admin' : 'buyer';
    $_SESSION['role'] = $role;
    db_run("UPDATE users SET role = ? WHERE id = ?", 'si', [$role, $_SESSION['user_id']]);
    go('role_transition.php');
}

include 'includes/header.php';
?>

<div class="center-screen">
  <div class="avatar-circle">👤</div>
  <h2>Who are You</h2>
  <form method="post" class="btn-row">
    <button class="btn" name="role" value="admin" type="submit">Admin</button>
    <button class="btn" name="role" value="buyer" type="submit">buyer</button>
  </form>
</div>

<?php include 'includes/footer.php'; ?>
