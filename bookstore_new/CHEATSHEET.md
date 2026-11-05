# CHEAT SHEET - "the judge asks me to change X"

Tip: in VS Code press **Ctrl+Shift+F**, search `CHANGE HERE`. Every place made for editing has that comment.
After editing a file: save (Ctrl+S) and refresh the browser (Ctrl+F5).

---------------------------------------------------------------------------
## A. EASY CHANGES (text, colors, settings) - 1 minute

| Judge says...                         | File                         | What to do |
|---------------------------------------|------------------------------|------------|
| Change shop name / address / phone / WhatsApp / email | `includes/config.php`   | Section 2 "SHOP SETTINGS" - change the text in the quotes |
| Change bank name / account number     | `includes/config.php`        | `BANK_NAME`, `BANK_ACCOUNT`, `BANK_HOLDER` |
| Change shipping fee                   | `includes/config.php`        | `define('SHIPPING_FEE', 10000);` -> new number |
| Change "low stock" limit              | `includes/config.php`        | `LOW_STOCK_LIMIT` |
| Change theme color (whole website)    | `assets/css/style.css`       | Top of the file, `:root { ... }`. Change `--bg` (background), `--text` (dark buttons + text), `--accent` (blue) |
| Change font                           | `assets/css/style.css`       | `body { font-family: ... }` and `h1, h2, h3 { font-family: ... }` |
| Change button shape / color           | `assets/css/style.css`       | `.btn` (all buttons), `.btn-primary` (dark ones), `.btn-danger` (red ones). `border-radius: 999px` = round, `8px` = slightly round |
| Change book card size                 | `assets/css/style.css`       | `.book-grid` -> `minmax(190px, 1fr)` (smaller number = more books per row) |
| Change the home page text / button    | `index.php`                  | Edit the text inside `<div class="hero">` |
| Change About page text                | `about.php`                  | Edit the `<p>` paragraphs |
| Add / rename / remove a menu link     | `includes/header.php`        | Edit the `<a href="...">` lines inside `.nav-links` |
| Change footer text                    | `includes/footer.php`        | Edit the text |
| Change the browser tab title of a page| the page itself              | Top of the file: `$page_title = '...';` |
| Change the money format (Rp. 75.000)  | `includes/config.php`        | function `rupiah()` |
| Change where login sends you          | `login.php`                  | `go($user['role'] === 'admin' ? ... : ...)` |
| Change book sort order                | `books.php`                  | `ORDER BY created_at DESC` -> `ORDER BY price ASC` or `ORDER BY title ASC` |
| Change delivery estimate text         | `selesai.php`                | `Estimasi tiba: 2 - 3 hari kerja` |
| Change allowed upload file types      | `admin/book_form.php` (covers), `checkout.php` (transfer proof) | The list like `['jpg', 'jpeg', 'png', ...]` |

---------------------------------------------------------------------------
## B. MEDIUM CHANGES (add a field, a filter, a page)

### B1. Add a new field to books (example: `author`)

**Step 1 - database.** phpMyAdmin -> `bookstore_db` -> tab **SQL** -> run:
```sql
ALTER TABLE books_data ADD COLUMN author VARCHAR(100) DEFAULT '';
```

**Step 2 - `admin/book_form.php`** (4 small edits):
1. In the empty `$book = [...]` array at the top, add `'author' => ''`.
2. In "1. read the form", add:
   `$author = trim($_POST['author'] ?? '');`
3. Replace the two queries (note: ONE more `s` in the type letters, and `$author` in the list):
```php
db_run("UPDATE books_data SET title=?, description=?, price=?, stock=?, category_id=?, cover_color=?, cover_image=?, author=? WHERE id=?",
       'ssdiisssi', [$title, $desc, $price, $stock, $cat_id, $color, $cover_image, $author, $id]);

db_run("INSERT INTO books_data (title, description, price, stock, category_id, cover_color, cover_image, author) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
       'ssdiisss', [$title, $desc, $price, $stock, $cat_id, $color, $cover_image, $author]);
```
4. In the HTML form, add (for example under the title input):
```html
<input class="field" type="text" name="author" placeholder="Author" value="<?= e($book['author']) ?>">
```

**Step 3 - show it.**
- `book_detail.php`, under the `<h2>`: `<p>By <?= e($book['author']) ?></p>`
- `books.php`, inside the card: `<div class="book-price"><?= e($b['author']) ?></div>`

The type letters: `s` = text, `i` = whole number, `d` = decimal. One letter per `?`, same order.

### B2. Search also by author (or any other column)
`books.php`:
```php
$sql    = "SELECT * FROM books_data WHERE (title LIKE ? OR author LIKE ?)";
$types  = 'ss';
$params = ['%' . $search . '%', '%' . $search . '%'];
```

### B3. Add a filter (example: only books under a price)
`books.php`, after the category `if`:
```php
$max_price = (int)($_GET['max_price'] ?? 0);
if ($max_price > 0) {
    $sql     .= " AND price <= ?";
    $types   .= 'i';
    $params[] = $max_price;
}
```
and in the HTML search `<form>` add `<input type="number" name="max_price" placeholder="max price">`.

### B4. Show the number of books found
`books.php`, above the book grid: `<p>Total: <?= count($books) ?> buku</p>`

### B5. Add a field to register (example: phone number)
1. SQL: `ALTER TABLE users ADD COLUMN phone VARCHAR(20) DEFAULT '';`
2. `register.php`: add `<input class="field" type="text" name="phone" placeholder="no. telepon">` in the form,
   read it with `$phone = trim($_POST['phone'] ?? '');`, and change the INSERT to:
```php
db_run("INSERT INTO users (name, email, password, address, phone, role) VALUES (?, ?, ?, ?, ?, 'buyer')",
       'sssss', [$name, $email, $hash, $address, $phone]);
```

### B6. Add a column to the admin users table
`admin/users.php`: add the column to the `SELECT` (e.g. `u.email`), add `<th>email</th>` in the header row,
and `<td><?= e($r['email']) ?></td>` in the loop.

### B7. Add a new simple page (example: FAQ)
1. Copy `about.php` -> `faq.php`, change `$page_title` and the text.
2. Add a link in `includes/header.php`: `<a href="<?= $base ?>faq.php">FAQ</a>`

### B8. Add a delete button for categories
The delete code already exists in `admin/categories.php` (it reacts to `delete_id`). Only the button is missing.
In `admin/category_edit.php`, under the `</form>`, add:
```html
<form method="post" action="categories.php" style="text-align:center; margin-top:16px;">
  <button class="btn btn-danger" name="delete_id" value="<?= $cat['id'] ?>" onclick="return confirm('Hapus kategori ini?')">delete category</button>
</form>
```

### B9. Add a new payment method (example: e-wallet)
1. SQL: `ALTER TABLE orders MODIFY payment_method ENUM('cod','transfer','ewallet') NOT NULL DEFAULT 'cod';`
2. `checkout.php`: copy one `<label class="pay-option">` block, change `value="ewallet"` and the text.
   In "3a. check the form" change the check to `if ($method !== 'cod' && $method !== 'transfer' && $method !== 'ewallet')`.
3. `includes/config.php`, function `method_label()`: add a line for `ewallet`.

---------------------------------------------------------------------------
## C. HOW TO READ THE CODE (so you can explain it)

**Every page has the same shape:**
```
require_once 'includes/config.php';   // 1. connect to database + load helpers
require_login();                      // 2. (only if the page needs login)
...read the URL / form (the $_GET / $_POST) ...   // 3. do the logic
include 'includes/header.php';        // 4. top of the page (menu)
...HTML with <?= ... ?> to print values...        // 5. the page content
include 'includes/footer.php';        // 6. bottom of the page
```

**The 3 database helpers (in `includes/config.php`):**
```php
$books = db_all("SELECT * FROM books_data WHERE price > ?", 'i', [50000]);  // many rows
$book  = db_one("SELECT * FROM books_data WHERE id = ?", 'i', [$id]);       // one row (or null)
db_run("UPDATE books_data SET stock = ? WHERE id = ?", 'ii', [5, $id]);     // insert / update / delete
```
The `?` are placeholders (this is what protects against SQL injection - the "prepared statement").

**Small helpers:** `e($text)` = safe text for HTML, `go('page.php')` = redirect, `rupiah(75000)` = "Rp. 75.000".

**GET vs POST:** `$_GET` = values in the URL (`books.php?q=conan`), `$_POST` = values from a form with `method="post"`.

**Login:** `login.php` checks the password with `password_verify()` and saves the user in `$_SESSION`.
`require_login()` / `require_admin()` at the top of a page block people who are not allowed.

**Order life (admin/orders.php):**
- transfer: `menunggu_verifikasi` -> admin confirms -> `lunas` + `diproses` -> complete -> `selesai`
- cod: `menunggu` -> admin confirms -> `diproses` -> complete -> `selesai` (and `lunas`)
- reject / cancel -> `dibatalkan` and the stock goes back
- Stock goes DOWN in `checkout.php`, and UP again in `restore_stock()` (config.php)

---------------------------------------------------------------------------
## D. IF SOMETHING BREAKS

| Problem | Fix |
|---|---|
| White / blank page | There is a typo in the PHP. Open `C:\xampp\apache\logs\error.log` - the last line says the file + line number. Usual causes: missing `;`, missing `)`, missing `?>` |
| "Database connection failed" | MySQL not started in XAMPP, or wrong name/password in `includes/config.php` |
| "Unknown column ..." | You used a column that is not in the database yet - run the `ALTER TABLE` (see B1) |
| "Column count doesn't match" / "bind_param" error | The number of `?`, the type letters and the values list `[...]` must all have the SAME count, in the same order |
| Upload does not work | Folder `assets/uploads/` (and `assets/uploads/bukti/`) must exist and be writable |
| Style not updating | Press Ctrl+F5 |
| Menu / page shows without CSS inside `admin/` | the page needs `$base = '../';` BEFORE `include '../includes/header.php';` |
