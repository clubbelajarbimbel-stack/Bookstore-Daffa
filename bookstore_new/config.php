<?php
// =========================================================
// config.php  -  loaded by EVERY page.
// It has: database connection, shop settings, helper functions.
// =========================================================

// ---------------------------------------------------------
// 1. DATABASE
// >>> CHANGE HERE if your MySQL user / password / database name is different
// ---------------------------------------------------------
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'bookstore_db';

$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();   // needed for login ($_SESSION)

// ---------------------------------------------------------
// 2. SHOP SETTINGS
// >>> CHANGE HERE: shop name, address, phone, bank account, shipping fee
// ---------------------------------------------------------
define('SHOP_NAME',    'Paper Moon Bookstore');
define('SHOP_ADDRESS', 'Jl. Mawar No. 25, Jakarta, Indonesia');
define('SHOP_PHONE',   '(021) 555-1234');
define('SHOP_WA',      '085947673983');
define('SHOP_EMAIL',   'info@papermoonbookstore.com');
define('SHOP_HOURS',   'Senin – Sabtu, 08.00 – 20.00 WIB');

define('BANK_NAME',    'BCA');
define('BANK_ACCOUNT', '1234567890');
define('BANK_HOLDER',  'PaperPulse Indonesia');

define('SHIPPING_FEE',    10000);            // >>> CHANGE HERE: flat shipping cost per order
define('LOW_STOCK_LIMIT', 5);                // >>> CHANGE HERE: stock <= this number shows "Sisa X" (low stock)
define('PROOF_MAX_BYTES', 5 * 1024 * 1024);  // max size of the transfer proof file (5 MB)

// ---------------------------------------------------------
// 3. DATABASE HELPERS (so every query is only ONE line)
//    $sql    = the query, with ? where a value goes
//    $types  = one letter per ?  ->  s = text, i = whole number, d = decimal
//    $params = the values, in the same order as the ?
//
//    Example:
//      $book = db_one("SELECT * FROM books_data WHERE id = ?", 'i', [$id]);
// ---------------------------------------------------------

// Run a SELECT, get ALL rows (an array of rows)
function db_all($sql, $types = '', $params = []) {
    global $conn;
    $stmt = mysqli_prepare($conn, $sql);
    if ($types !== '') {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
}

// Run a SELECT, get ONLY THE FIRST row (or null if nothing found)
function db_one($sql, $types = '', $params = []) {
    $rows = db_all($sql, $types, $params);
    return $rows ? $rows[0] : null;
}

// Run INSERT / UPDATE / DELETE. Returns the new id (for INSERT).
function db_run($sql, $types = '', $params = []) {
    global $conn;
    $stmt = mysqli_prepare($conn, $sql);
    if ($types !== '') {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    return mysqli_insert_id($conn);
}

// Like db_run, but returns HOW MANY rows were changed (0 = nothing matched)
function db_changed($sql, $types = '', $params = []) {
    global $conn;
    $stmt = mysqli_prepare($conn, $sql);
    if ($types !== '') {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_affected_rows($stmt);
}

// ---------------------------------------------------------
// 4. SMALL HELPERS
// ---------------------------------------------------------

// Safe text for HTML. Use it on anything that comes from the database or the user.
function e($text) {
    return htmlspecialchars((string)$text);
}

// Redirect to another page and stop.
function go($url) {
    header('Location: ' . $url);
    exit;
}

// 75000 -> "Rp. 75.000"   (>>> CHANGE HERE if you want another currency format)
function rupiah($num) {
    return 'Rp. ' . number_format((float)$num, 0, ',', '.');
}

// ---------------------------------------------------------
// 5. LOGIN CHECKS
// ---------------------------------------------------------
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function current_role() {
    return $_SESSION['role'] ?? null;     // 'admin' or 'buyer'
}

// Put at the top of a page that needs login
function require_login() {
    if (!is_logged_in()) go('login.php');
}

// Put at the top of an admin page (admin pages are inside the admin/ folder)
function require_admin() {
    if (!is_logged_in()) go('../login.php');
    if (current_role() !== 'admin') go('../books.php');
}

// ---------------------------------------------------------
// 6. ORDER / STOCK HELPERS
// ---------------------------------------------------------

// Text + color for the PAYMENT status. Returns [text, css-class]
function payment_label($o) {
    if ($o['payment_status'] === 'lunas')               return ['Sudah dibayar', 'ok'];
    if ($o['payment_status'] === 'menunggu_verifikasi') return ['Menunggu verifikasi pembayaran', 'wait'];
    if ($o['payment_status'] === 'ditolak')             return ['Pembayaran ditolak', 'bad'];
    return [$o['payment_method'] === 'cod' ? 'Bayar saat diterima' : 'Belum dibayar', 'wait'];
}

// Text + color for the ORDER status. Returns [text, css-class]
function order_label($o) {
    if ($o['order_status'] === 'diproses')   return ['Diproses', 'info'];
    if ($o['order_status'] === 'selesai')    return ['Selesai', 'ok'];
    if ($o['order_status'] === 'dibatalkan') return ['Dibatalkan', 'bad'];
    return ['Menunggu konfirmasi', 'wait'];
}

function method_label($m) {
    return $m === 'transfer' ? 'Transfer Bank' : 'Bayar di Tempat';
}

// The books inside one order
function get_order_items($order_id) {
    return db_all("SELECT title, price, quantity FROM order_items WHERE order_id = ? ORDER BY id", 'i', [$order_id]);
}

// Give the stock back (used when an order is cancelled / payment rejected)
function restore_stock($order_id) {
    $items = db_all("SELECT book_id, quantity FROM order_items WHERE order_id = ? AND book_id IS NOT NULL", 'i', [$order_id]);
    foreach ($items as $it) {
        db_run("UPDATE books_data SET stock = stock + ? WHERE id = ?", 'ii', [$it['quantity'], $it['book_id']]);
    }
}

// Small colored label: "Stok: 10" / "Sisa 3" / "Stok habis"
function stock_badge($stock) {
    $stock = (int)$stock;
    if ($stock <= 0)               return '<span class="stock-badge stock-out">Stok habis</span>';
    if ($stock <= LOW_STOCK_LIMIT) return '<span class="stock-badge stock-low">Sisa ' . $stock . '</span>';
    return '<span class="stock-badge stock-ok">Stok: ' . $stock . '</span>';
}

// What goes inside a book cover box: the picture, or the title if there is no picture.
// $base is '' for normal pages and '../' for pages inside the admin folder.
function cover_inner($b, $base = '') {
    if (!empty($b['cover_image'])) {
        return '<img src="' . $base . 'assets/uploads/' . e($b['cover_image']) . '" alt="' . e($b['title']) . '">';
    }
    return '<span class="cover-text">' . e($b['title']) . '</span>';
}
