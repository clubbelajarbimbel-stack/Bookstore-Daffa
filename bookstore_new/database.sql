-- =========================================================
-- PAPER MOON BOOKSTORE database schema + seed data
-- Import this in phpMyAdmin, or run:
--   mysql -u root -p < database.sql
-- =========================================================

CREATE DATABASE IF NOT EXISTS bookstore_db;
USE bookstore_db;

-- ---------------------------------------------------------
-- Users (buyers & admins). Role is chosen once at register,
-- and can also be switched on the "Who are you?" screen.
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    address VARCHAR(255) DEFAULT '',
    role ENUM('admin','buyer') NOT NULL DEFAULT 'buyer',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------
-- Categories
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

-- ---------------------------------------------------------
-- Books
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS books_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(12,2) NOT NULL DEFAULT 0,
    stock INT NOT NULL DEFAULT 0,
    category_id INT,
    cover_color VARCHAR(7) DEFAULT '#4B4F58',
    cover_image VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- ---------------------------------------------------------
-- Cart (one row per book per user, with quantity)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books_data(id) ON DELETE CASCADE
);

-- ---------------------------------------------------------
-- Orders (created once checkout / payment is confirmed)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_title VARCHAR(150) NOT NULL,           -- ringkasan judul buku
    total DECIMAL(12,2) NOT NULL DEFAULT 0,
    order_date DATE NOT NULL,
    payment_method ENUM('cod','transfer') NOT NULL DEFAULT 'cod',
    payment_status ENUM('belum_dibayar','menunggu_verifikasi','lunas','ditolak') NOT NULL DEFAULT 'belum_dibayar',
    order_status ENUM('menunggu','diproses','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu',
    shipping_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
    shipping_address TEXT NULL,
    payment_proof VARCHAR(255) NULL,            -- nama file bukti transfer
    admin_note VARCHAR(255) NULL,
    confirmed_at DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Order items (rincian buku per pesanan)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    book_id INT NULL,
    title VARCHAR(150) NOT NULL,
    price DECIMAL(12,2) NOT NULL DEFAULT 0,
    quantity INT NOT NULL DEFAULT 1,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books_data(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Seed data
-- ---------------------------------------------------------
INSERT INTO categories (name) VALUES ('Romance'), ('Horror'), ('Aksi');

INSERT INTO books_data (title, description, price, stock, category_id, cover_color, cover_image) VALUES
('Gentayangan', 'Iblis Kekasih telah memberimu sepasang sepatu merah, hadiah sekaligus kutukan. Kau terkutuk untuk bertualang, atau lebih tepatnya, gentayangan. Bernaung, tapi tak berumah. Gentayangan: Pilih Sendiri Petualanganmu adalah novel interaktif karya Intan Paramaditha yang terbit pertama kali tahun 2017. Berkisah tentang perjalanan, ketercerabutan, dan batas-batas kemerdekaan, novel ini mendapatkan penghargaan Sastra Prosa Terbaik Pilihan Tempo.', 70000, 10, 1, '#c0392b', 'gentay.png'),
('Detektif Conan', 'Serial komik detektif legendaris tentang seorang detektif SMA yang tubuhnya menyusut menjadi anak-anak setelah keracunan obat rahasia, dan memecahkan kasus demi kasus sambil mencari cara mengembalikan tubuhnya.', 35000, 10, 3, '#c9a227', 'conan.png'),
('5cm', 'Novel karya Donny Dhirgantoro tentang lima sahabat yang menjalani persahabatan, mimpi, dan pendakian ke puncak Mahameru sebagai simbol perjuangan mewujudkan cita-cita.', 85000, 10, 3, '#8e3b5c', '5cm.png'),
('99++ Solusi Medis, Herbal & Holistik', 'Buku referensi kesehatan yang membahas solusi medis, herbal, dan holistik untuk mengatasi berbagai keluhan kesehatan sehari-hari.', 60000, 10, 3, '#2c3e50', '99.png'),
('Cica: 96 Puisi', 'Kumpulan 96 puisi karya Cyntha Hariadi yang menyentuh tema keseharian, perasaan, dan renungan hidup dengan gaya bahasa yang ringan namun dalam.', 55000, 10, 1, '#9b8ec4', 'cica.png'),
('Frozen II: Book of the Film', 'Kisah petualangan Anna dan Elsa dalam menjelajahi hutan terlarang untuk mengungkap asal-usul kekuatan sihir Elsa, dituturkan ulang dalam format buku cerita.', 45000, 10, 1, '#5b8fae', 'frozen.png'),
('Death Note', 'Kisah seorang siswa jenius yang menemukan buku catatan misterius yang dapat membunuh siapa pun yang namanya dituliskan di dalamnya, memicu pertarungan kejahatan dan keadilan.', 40000, 10, 2, '#111318', 'dednot.png'),
('The Little Backpacker in the Great Big Universe', 'Catatan perjalanan seorang backpacker yang membagikan pengalaman, pelajaran hidup, dan mimpi-mimpi besar dari perjalanannya menjelajah dunia.', 65000, 10, 3, '#b03a2e', 'b.png');















-- 
                                                     @@@@@@@@@@@@@                                  
                                                   #%%%@@@@@@@@@@%%%#                               
                                                  *##%%@@@@@@@@@@%%#*=                              
                                                  *#%@@@@@@@@@@@@@%#*=                              
                                                  +*#@@@@*:..:::-=%%#=                              
                                                ::::..=#*::::-=-:=*##+                              
                                               ::-*+:..+*:.-*%@@**-*#+                              
                                               :-+-@@-.%%-:#....@@:=*+                              
                                               :=...#@-@@#++:..-*#*%#+                              
                                               :--:=*==@@%@+:---+@@@%*                              
                                               #+::-+%*@@#%@@%@@@@@%*=:                             
                                               *@@@@#=#@@@%+=++++=--:::                             
                                               +---::-:::::=+:..:::::::                             
                                               =#-:.=+....-@@@*:..:::::                             
                                                 +#.-*:..---::=*=::::::                             
                                                  #=-::::*%*-=%#+-:::::                             
                                                  ##-::....-#%%*=-----:                             
                                                  =@-:-:-+%%#*=-===----                             
                                                   @+:::-------=++==--:                             
                                                   ##:...:::-+***+++*-:                             
                                                   *%::::-=++***++=+%%+                             
                                                  -=#::-=+*+**+++=-:*#*                             
                                                  =-*::-=++++++===-:+##                             
                                                  =-+::-=++++++===-::*#                             
                                                  =:+-:-===++++====:.=*                             
                                                  =:-=:--===++++===-:@#+                            
                                                  =::=---===++++===-+@*                             
                                                  ==:---====++++===-#%+                             
                                                  *-:-:--=========--%#                              
                                                 #--::---==========#%+                              
                                                 %%-::---========+##*-                              
                                                 .-=:----========--+-:                              
                                                  -+------======--+-:.                              
                                                  %+:::::---===--:::                                
                                                 %#-:::...:  .:--:.                                 
                                                %#=- --:...  :-++-:                                 
                                               %%=-   ==:..  =+*+-:                                 
                                             =#%=:    =+:..   **+-:                                 
                                            -#%+:     *#=:.   ##*=-                                 
                                           +#%+:      ::-:.   ----:                                 
                                          #%%+:       :::::   ::---                                 
                                         %%#=:        -:--:   :--=-                                 
                                       *#%*=:         :----   --==-                                 
                                      ###*=:           ---:    ----                                 
                                     *##*=-            =--:    ----                                 
                                   +**#*=:=            ---:    ----                                 
                                  ++**+=-=             ----    -=--                                 
                                 +++*+=:-              ==-:    ==--                                 
                                =+++=-:-              @#+=::   ++=-                                 
                               ==++=---            @@@%#+=-:: %%#+-:                                
                              ==+==-:-       ++%@@@@@#*+==-::=@%%#+-                                
                             :-==--::      #%%%%%@@%#*=::::.+@%%%%#*=                               
                             .::::::      =*##%%%#+==:..   #%%%%%%%#%                               
                                             -===:..     @@%+**-#-#=*+                              
                                                         ---:--:-.:--                               