<?php
// admin/orders.php - ALL orders. The admin confirms / rejects payments, processes, completes or cancels orders.
require_once '../includes/config.php';
require_admin();
$base = '../';
$page_title = 'Pesanan Masuk';
$flash = '';          // message shown at the top
$flash_ok = true;     // true = green message, false = red message

// =========================================================
// 1. A BUTTON WAS PRESSED (confirm / reject / complete / cancel)
//    Order life:
//      transfer: menunggu_verifikasi --confirm_payment--> lunas + diproses --complete--> selesai
//                                    --reject_payment---> ditolak + dibatalkan
//      cod:      menunggu --confirm_order--> diproses --complete--> selesai (+ lunas)
//      cancel:   menunggu / diproses --cancel--> dibatalkan (stock goes back)
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['action'])) {
    $oid    = (int)$_POST['order_id'];
    $action = $_POST['action'];
    $note   = mb_substr(trim($_POST['note'] ?? ''), 0, 250);     // optional reason typed by the admin
    $note   = ($note !== '') ? $note : null;

    $o = db_one("SELECT * FROM orders WHERE id = ?", 'i', [$oid]);

    if (!$o) {
        $flash = 'Pesanan tidak ditemukan.';
        $flash_ok = false;

    } else {
        // a transfer order that is waiting for the admin to check the proof
        $transfer_pending = ($o['payment_method'] === 'transfer'
                          && $o['payment_status'] === 'menunggu_verifikasi'
                          && $o['order_status'] === 'menunggu');

        if ($action === 'confirm_payment' && $transfer_pending) {
            db_run("UPDATE orders SET payment_status='lunas', order_status='diproses', confirmed_at=NOW(), admin_note=NULL WHERE id=?", 'i', [$oid]);
            $flash = "Pembayaran pesanan #$oid dikonfirmasi.";

        } elseif ($action === 'reject_payment' && $transfer_pending) {
            restore_stock($oid);
            db_run("UPDATE orders SET payment_status='ditolak', order_status='dibatalkan', admin_note=? WHERE id=?", 'si', [$note, $oid]);
            $flash = "Pembayaran pesanan #$oid ditolak dan stok dikembalikan.";

        } elseif ($action === 'confirm_order' && $o['payment_method'] === 'cod' && $o['order_status'] === 'menunggu') {
            db_run("UPDATE orders SET order_status='diproses', confirmed_at=NOW() WHERE id=?", 'i', [$oid]);
            $flash = "Pesanan #$oid (bayar di tempat) dikonfirmasi dan mulai diproses.";

        } elseif ($action === 'complete' && $o['order_status'] === 'diproses') {
            // COD: the money arrives when the book arrives, so it also becomes "lunas"
            db_run("UPDATE orders SET order_status='selesai', payment_status='lunas' WHERE id=?", 'i', [$oid]);
            $flash = "Pesanan #$oid ditandai selesai.";

        } elseif ($action === 'cancel' && ($o['order_status'] === 'menunggu' || $o['order_status'] === 'diproses') && $o['payment_status'] !== 'lunas') {
            restore_stock($oid);
            db_run("UPDATE orders SET order_status='dibatalkan', admin_note=? WHERE id=?", 'si', [$note, $oid]);
            $flash = "Pesanan #$oid dibatalkan dan stok dikembalikan.";

        } else {
            $flash = 'Aksi tidak bisa dilakukan untuk status pesanan saat ini.';
            $flash_ok = false;
        }
    }
}

// =========================================================
// 2. THE LIST (with a filter: semua / menunggu / diproses / selesai / dibatalkan)
// =========================================================
$filter = $_GET['status'] ?? 'semua';
$valid  = ['semua', 'menunggu', 'diproses', 'selesai', 'dibatalkan'];
if (!in_array($filter, $valid)) $filter = 'semua';

// how many orders for each filter button
$counts = ['semua' => 0];
foreach (db_all("SELECT order_status, COUNT(*) AS n FROM orders GROUP BY order_status") as $c) {
    $counts[$c['order_status']] = (int)$c['n'];
    $counts['semua'] += (int)$c['n'];
}

// waiting orders first, then newest first
$sql = "SELECT o.*, u.name AS user_name, u.email AS user_email, u.address AS user_address
        FROM orders o JOIN users u ON o.user_id = u.id";
if ($filter === 'semua') {
    $orders = db_all($sql . " ORDER BY (o.order_status = 'menunggu') DESC, o.id DESC");
} else {
    $orders = db_all($sql . " WHERE o.order_status = ? ORDER BY o.id DESC", 's', [$filter]);
}

include '../includes/header.php';
?>

<h2 style="text-align:center;">pesanan masuk</h2>

<?php if ($flash): ?><div class="form-msg <?= $flash_ok ? 'ok' : '' ?>"><?= e($flash) ?></div><?php endif; ?>

<!-- filter buttons -->
<div class="chip-row" style="justify-content:center;">
  <?php foreach ($valid as $f): ?>
    <a class="chip <?= $filter === $f ? 'active' : '' ?>" href="orders.php?status=<?= $f ?>"><?= ucfirst($f) ?> (<?= $counts[$f] ?? 0 ?>)</a>
  <?php endforeach; ?>
</div>

<?php if (!$orders): ?><p style="text-align:center;">Belum ada pesanan di kategori ini.</p><?php endif; ?>

<?php foreach ($orders as $o):
  $pay   = payment_label($o);
  $state = order_label($o);
  $items = get_order_items($o['id']);
  $addr  = $o['shipping_address'] ?: $o['user_address'];
  $transfer_pending = ($o['payment_method'] === 'transfer' && $o['payment_status'] === 'menunggu_verifikasi' && $o['order_status'] === 'menunggu');
  $proof_url = '../assets/uploads/bukti/' . rawurlencode((string)$o['payment_proof']);
  $proof_ext = strtolower(pathinfo((string)$o['payment_proof'], PATHINFO_EXTENSION));
?>
  <div class="box">
    <div class="order-head">
      <div>
        <b>Pesanan #<?= (int)$o['id'] ?> - <?= e($o['user_name']) ?></b>
        <span class="order-date"><?= date('d M Y', strtotime($o['order_date'])) ?> · <?= method_label($o['payment_method']) ?> · <?= e($o['user_email']) ?></span>
      </div>
      <div>
        <span class="badge badge-<?= $pay[1] ?>"><?= e($pay[0]) ?></span>
        <span class="badge badge-<?= $state[1] ?>"><?= e($state[0]) ?></span>
      </div>
    </div>

    <div class="order-body">
      <div>
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
        <p class="order-note">📍 <?= $addr ? nl2br(e($addr)) : '-' ?></p>
        <?php if ($o['admin_note']): ?><p class="order-note">📝 Catatan: <?= e($o['admin_note']) ?></p><?php endif; ?>
      </div>

      <!-- payment proof (picture or pdf) -->
      <div class="proof-col">
        <?php if (!empty($o['payment_proof'])): ?>
          <b>Bukti transfer</b><br>
          <?php if ($proof_ext === 'pdf'): ?>
            <a class="btn btn-sm" target="_blank" href="<?= $proof_url ?>">Buka PDF bukti</a>
          <?php else: ?>
            <img class="proof-thumb" src="<?= $proof_url ?>" alt="Bukti transfer" onclick="openProof(this.src)">
            <small>Klik untuk perbesar</small>
          <?php endif; ?>
        <?php elseif ($o['payment_method'] === 'transfer'): ?>
          <b>Bukti transfer</b><br><small>Tidak ada file.</small>
        <?php else: ?>
          <b>Bayar di tempat</b><br><small>Tidak perlu bukti transfer.</small>
        <?php endif; ?>
      </div>
    </div>

    <!-- the buttons that fit the current status of this order -->
    <div class="order-actions">
      <?php if ($transfer_pending): ?>
        <form method="post">
          <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
          <button class="btn btn-primary btn-sm" name="action" value="confirm_payment" onclick="return confirm('Konfirmasi bahwa pembayaran sudah masuk?')">✓ Konfirmasi pembayaran</button>
        </form>
        <form method="post">
          <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
          <input class="field" type="text" name="note" placeholder="alasan penolakan (opsional)">
          <button class="btn btn-danger btn-sm" name="action" value="reject_payment" onclick="return confirm('Tolak pembayaran ini? Stok akan dikembalikan.')">✕ Tolak</button>
        </form>

      <?php elseif ($o['payment_method'] === 'cod' && $o['order_status'] === 'menunggu'): ?>
        <form method="post">
          <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
          <button class="btn btn-primary btn-sm" name="action" value="confirm_order">✓ Konfirmasi pesanan</button>
        </form>
        <form method="post">
          <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
          <input class="field" type="text" name="note" placeholder="alasan batal (opsional)">
          <button class="btn btn-danger btn-sm" name="action" value="cancel" onclick="return confirm('Batalkan pesanan ini? Stok akan dikembalikan.')">Batalkan</button>
        </form>

      <?php elseif ($o['order_status'] === 'diproses'): ?>
        <form method="post">
          <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
          <button class="btn btn-primary btn-sm" name="action" value="complete" onclick="return confirm('Tandai pesanan ini selesai?')">📦 Tandai selesai<?= $o['payment_method'] === 'cod' ? ' &amp; sudah dibayar' : '' ?></button>
        </form>
        <?php if ($o['payment_status'] !== 'lunas'): ?>
          <form method="post">
            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
            <button class="btn btn-danger btn-sm" name="action" value="cancel" onclick="return confirm('Batalkan pesanan ini? Stok akan dikembalikan.')">Batalkan</button>
          </form>
        <?php endif; ?>

      <?php else: ?>
        <small>Tidak ada aksi lagi untuk pesanan ini.</small>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>

<div style="text-align:center; margin-top:24px;">
  <a class="btn" href="dashboard.php">Back</a>
</div>

<!-- big picture shown when a proof thumbnail is clicked (click it again to close) -->
<div id="proof-modal" class="proof-modal" onclick="this.style.display='none'">
  <img id="proof-modal-img" alt="Bukti transfer">
</div>
<script>
function openProof(src) {
  document.getElementById('proof-modal-img').src = src;
  document.getElementById('proof-modal').style.display = 'flex';
}
</script>

<?php include '../includes/footer.php'; ?>
