<?php
// orders.php - the buyer's own orders and their status
require_once 'includes/config.php';
require_login();
$base = '';
$page_title = 'Pesanan Saya';

$orders = db_all("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC", 'i', [$_SESSION['user_id']]);

include 'includes/header.php';
?>

<h2>pesanan saya</h2>

<?php if (!$orders): ?>
  <p>Kamu belum punya pesanan. <a href="books.php"><u>Cari buku</u></a></p>
<?php endif; ?>

<?php foreach ($orders as $o):
  $pay   = payment_label($o);     // [text, color]
  $state = order_label($o);       // [text, color]
  $items = get_order_items($o['id']);
?>
  <div class="box">
    <div class="order-head">
      <div>
        <b>Pesanan #<?= (int)$o['id'] ?></b>
        <span class="order-date"><?= date('d M Y', strtotime($o['order_date'])) ?> · <?= method_label($o['payment_method']) ?></span>
      </div>
      <div>
        <span class="badge badge-<?= $pay[1] ?>"><?= e($pay[0]) ?></span>
        <span class="badge badge-<?= $state[1] ?>"><?= e($state[0]) ?></span>
      </div>
    </div>

    <ul class="order-items">
      <?php foreach ($items as $it): ?>
        <li><span><?= e($it['title']) ?> &times; <?= (int)$it['quantity'] ?></span><span><?= rupiah($it['price'] * $it['quantity']) ?></span></li>
      <?php endforeach; ?>
      <?php if (!$items): ?>
        <li><span><?= e($o['book_title']) ?></span><span></span></li>
      <?php endif; ?>
      <?php if ((float)$o['shipping_cost'] > 0): ?>
        <li><span>Ongkir</span><span><?= rupiah($o['shipping_cost']) ?></span></li>
      <?php endif; ?>
      <li class="order-total"><span>Total</span><span><?= rupiah($o['total']) ?></span></li>
    </ul>

    <?php if (!empty($o['shipping_address'])): ?>
      <p class="order-note">📍 <?= nl2br(e($o['shipping_address'])) ?></p>
    <?php endif; ?>

    <!-- a message that explains the payment / order status. >>> CHANGE HERE: the wording -->
    <?php if ($o['payment_status'] === 'lunas'): ?>
      <p class="order-note ok">✓ Pembayaran sudah dikonfirmasi admin<?= $o['payment_method'] === 'cod' ? ' (dibayar saat barang diterima)' : '' ?>.</p>
    <?php elseif ($o['payment_status'] === 'menunggu_verifikasi'): ?>
      <p class="order-note">⏳ Bukti transfermu sedang diperiksa admin.</p>
    <?php elseif ($o['payment_status'] === 'ditolak'): ?>
      <p class="order-note bad">✕ Pembayaran ditolak<?= $o['admin_note'] ? ': ' . e($o['admin_note']) : '.' ?> Hubungi kami di WhatsApp <?= e(SHOP_WA) ?>.</p>
    <?php elseif ($o['order_status'] === 'dibatalkan'): ?>
      <p class="order-note bad">Pesanan dibatalkan<?= $o['admin_note'] ? ': ' . e($o['admin_note']) : '.' ?></p>
    <?php endif; ?>

    <?php if (!empty($o['payment_proof'])): ?>
      <p class="order-note"><a target="_blank" href="assets/uploads/bukti/<?= e($o['payment_proof']) ?>"><u>Lihat bukti transfer yang kamu upload</u></a></p>
    <?php endif; ?>
  </div>
<?php endforeach; ?>

<div class="btn-row">
  <a class="btn" href="books.php">Back</a>
</div>

<?php include 'includes/footer.php'; ?>
