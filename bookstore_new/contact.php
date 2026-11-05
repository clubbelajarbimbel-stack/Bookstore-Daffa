<?php
// contact.php - contact info. The values come from config.php (SHOP_ADDRESS, SHOP_PHONE, ...)
require_once 'includes/config.php';
$base = '';
$page_title = 'Kontak';
include 'includes/header.php';
?>

<div class="center-screen">
  <h1>contact us!</h1>
  <!-- >>> CHANGE HERE: add / remove a contact line (the values themselves are in includes/config.php) -->
  <p>📍 Alamat<br><b><?= e(SHOP_ADDRESS) ?></b></p>
  <p>📞 Telepon<br><b><?= e(SHOP_PHONE) ?></b></p>
  <p>📱 WhatsApp<br><b><?= e(SHOP_WA) ?></b></p>
  <p>📧 Email<br><b><?= e(SHOP_EMAIL) ?></b></p>
  <p>🕒 Jam Operasional<br><b><?= e(SHOP_HOURS) ?></b></p>
  <a class="btn" href="index.php">Back</a>
</div>

<?php include 'includes/footer.php'; ?>
