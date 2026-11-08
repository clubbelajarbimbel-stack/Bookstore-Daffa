<?php
// header.php - top of every page (navigation bar).
// Each page sets $page_title (browser tab title) and $base ('' or '../') BEFORE including this file.
if (!isset($base)) $base = '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($page_title) ? e($page_title) . ' - ' . SHOP_NAME : SHOP_NAME ?></title>
<link rel="stylesheet" href="<?= $base ?>assets/css/style.css">
</head>
<body>

<!-- ============ NAVIGATION BAR ============ -->
<!-- >>> CHANGE HERE: add / remove / rename menu links in this block -->
<div class="navbar">
  <a class="brand" href="<?= $base ?>index.php">📖 <?= SHOP_NAME ?></a>

  <div class="nav-links">
    <?php if (is_logged_in()): ?>
      <span>Hai, <?= e($_SESSION['user_name']) ?></span>
      <a href="<?= $base ?>books.php">Books</a>
      <a href="<?= $base ?>about.php">About</a>
      <a href="<?= $base ?>contact.php">Contact</a>
      <?php if (current_role() === 'admin'): ?>
        <a href="<?= $base ?>admin/dashboard.php">Admin</a>
      <?php else: ?>
        <a href="<?= $base ?>cart.php">Cart</a>
        <a href="<?= $base ?>orders.php">Pesanan</a>
      <?php endif; ?>
      <a href="<?= $base ?>logout.php">Logout</a>
    <?php else: ?>
      <a href="<?= $base ?>login.php">Login &amp; register</a>
      <a href="<?= $base ?>books.php">Books</a>
      <a href="<?= $base ?>about.php">About</a>
      <a href="<?= $base ?>contact.php">Contact</a>
    <?php endif; ?>
  </div>
</div>

<div class="content">
