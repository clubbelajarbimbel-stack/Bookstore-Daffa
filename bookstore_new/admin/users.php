<?php
// admin/users.php - table of all orders with the name + address of the user who made them
require_once '../includes/config.php';
require_admin();
$base = '../';
$page_title = 'User & Pesanan';

$rows = db_all("SELECT u.name, u.address, o.id, o.book_title, o.order_date, o.payment_method, o.payment_status, o.order_status
                FROM orders o JOIN users u ON o.user_id = u.id
                ORDER BY o.order_date DESC, o.id DESC");

include '../includes/header.php';
?>

<h2 style="text-align:center;">list dan pesanan user yg sudah daftar</h2>

<!-- >>> CHANGE HERE: to add a column, add a <th> in the first row and a <td> in the loop below
     (and add the column to the SELECT above) -->
<div class="table-wrap">
<table class="list-table">
  <tr><th>NO</th><th>nama</th><th>Alamat</th><th>Tanggal Pesan</th><th>Pesanan</th><th>Pembayaran</th><th>Status</th></tr>
  <?php $no = 1; foreach ($rows as $r): ?>
    <tr>
      <td><?= $no++ ?></td>
      <td><?= e($r['name']) ?></td>
      <td><?= e($r['address'] ?: '-') ?></td>
      <td><?= date('d M Y', strtotime($r['order_date'])) ?></td>
      <td><?= e($r['book_title']) ?></td>
      <td><?= e(payment_label($r)[0]) ?></td>
      <td><?= e(order_label($r)[0]) ?></td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?>
    <tr><td colspan="7">Belum ada pesanan.</td></tr>
  <?php endif; ?>
</table>
</div>

<div class="btn-row" style="justify-content:center;">
  <a class="btn btn-primary" href="orders.php">kelola pesanan</a>
  <a class="btn" href="dashboard.php">Back</a>
</div>

<?php include '../includes/footer.php'; ?>
