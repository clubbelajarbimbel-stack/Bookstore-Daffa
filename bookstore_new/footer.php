<?php
// footer.php - bottom of every page. The shop info comes from config.php.
if (!isset($base)) $base = '';
?>
</div> <!-- end .content -->

<!-- >>> CHANGE HERE: footer text -->
<div class="footer">
  <div class="footer-grid">
    <div>
      <h4><?= SHOP_NAME ?></h4>
      <p>Toko buku yang hadir untuk memenuhi kebutuhan membaca dan belajar. Good books. Better you.</p>
      <a href="<?= $base ?>about.php"><b>Siapa kami? &rarr;</b></a>
    </div>
    <div>
      <h4>Hubungi Kami</h4>
      <p>📍 <?= e(SHOP_ADDRESS) ?></p>
      <p>📞 <?= e(SHOP_PHONE) ?></p>
      <p>📱 WhatsApp <?= e(SHOP_WA) ?></p>
      <p>📧 <?= e(SHOP_EMAIL) ?></p>
      <p>🕒 <?= e(SHOP_HOURS) ?></p>
    </div>
    <div>
      <h4>Menu</h4>
      <p><a href="<?= $base ?>books.php">Books</a></p>
      <p><a href="<?= $base ?>about.php">Tentang Kami</a></p>
      <p><a href="<?= $base ?>contact.php">Kontak</a></p>
      <?php if (is_logged_in() && current_role() !== 'admin'): ?>
        <p><a href="<?= $base ?>cart.php">Keranjang</a></p>
        <p><a href="<?= $base ?>orders.php">Pesanan Saya</a></p>
      <?php endif; ?>
    </div>
  </div>
  <div class="footer-bottom">&copy; <?= date('Y') ?> <?= SHOP_NAME ?> - Good books. Better you.</div>
</div>

</body>
</html>
