# bookstore_new  (Paper Moon Bookstore - simple version)

Same website as before, same features, but with simple code and "CHANGE HERE" comments
so you can edit it yourself. PHP + MySQL (XAMPP).

## How to run
1. Copy the folder `bookstore_new` into `C:\xampp\htdocs\`
2. Start **Apache** and **MySQL** in the XAMPP Control Panel
3. Open `http://localhost/phpmyadmin` -> tab **Import** -> choose `database.sql` -> **Go**
4. Open `http://localhost/bookstore_new/`

IMPORTANT: this uses the SAME database name (`bookstore_db`) as your old `bookstore` folder.
- Same data, nothing to re-import if your old site already works.
- If you want a separate copy of the data, change `bookstore_db` to e.g. `bookstore_new_db`
  in `includes/config.php` AND in the first two lines of `database.sql`, then import.

## How to get an ADMIN account
1. Register a normal account (it starts as buyer) and log in.
2. Open `http://localhost/bookstore_new/role.php` and click **Admin**.
3. Log out and log in again - you now see the **Admin** menu.

## Features (all kept)
- Home, About, Contact, footer with shop info
- Register, Login, Logout, "Who are you" role screen (role.php)
- Books: search by title + filter by category, stock label (ok / low / out)
- Book detail + Add to cart (checks the stock)
- Cart: remove a book, total price, stock warning
- Checkout: pay on delivery (COD) or bank transfer + upload proof, address, shipping fee,
  stock goes down, cart is emptied, success page
- Buyer: "Pesanan" page with payment + order status
- Admin: dashboard with numbers, add / edit / delete book (with cover upload + color),
  add / rename category, stock page (+ / - / set number), users + orders table,
  incoming orders (confirm / reject payment, process, complete, cancel, view proof)

## Folder map
```
bookstore_new/
├── database.sql            import in phpMyAdmin
├── CHEATSHEET.md           "if the judge asks X, edit Y"  <-- read this first
├── index.php               home
├── about.php / contact.php
├── register.php / login.php / logout.php
├── role.php / role_transition.php
├── books.php               book list + search + category
├── book_detail.php         one book + add to cart
├── cart.php / checkout.php / selesai.php
├── orders.php              buyer's orders
├── includes/
│   ├── config.php          database, shop settings, helper functions
│   ├── header.php          top menu (every page)
│   └── footer.php          bottom info (every page)
├── admin/
│   ├── dashboard.php       admin home + book list
│   ├── book_form.php       add / edit / delete book
│   ├── categories.php / category_edit.php
│   ├── stock.php
│   ├── users.php           users + orders table
│   └── orders.php          manage incoming orders
└── assets/
    ├── css/style.css       ALL the styling (colors at the top)
    └── uploads/            book covers (+ bukti/ = payment proofs)
```

Tip: in VS Code press **Ctrl+Shift+F** and search `CHANGE HERE` to see every place made for editing.
