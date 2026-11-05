<?php
// role_transition.php - "enjoy Managing / enjoy Browsing" screen shown after choosing a role
require_once 'includes/config.php';
require_login();
$base = '';
$is_admin = (current_role() === 'admin');
$page_title = $is_admin ? 'Enjoy Managing' : 'Enjoy Browsing';
include 'includes/header.php';
?>

<div class="center-screen">
  <h1><?= $is_admin ? 'enjoy Managing!' : 'enjoy Browsing!' ?></h1>
  <div class="btn-row">
    <a class="btn" href="<?= $is_admin ? 'admin/dashboard.php' : 'books.php' ?>">Continue</a>
    <?php if ($is_admin): ?>
      <a class="btn" href="admin/book_form.php">edit book</a>
    <?php endif; ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
