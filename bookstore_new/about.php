<?php
// about.php - "Who are we?" page (just text)
require_once 'includes/config.php';
$base = '';
$page_title = 'Tentang Kami';
include 'includes/header.php';
?>

<!-- >>> CHANGE HERE: the text of the About page -->
<h1>siapa kami?</h1>
<p class="detail-desc">
Paper Moon Bookstore adalah toko buku yang hadir untuk memenuhi kebutuhan membaca dan belajar masyarakat.
Kami percaya bahwa setiap buku memiliki kekuatan untuk membuka wawasan, mengembangkan
pengetahuan, dan menginspirasi pembacanya.
</p>
<p class="detail-desc">
Kami menyediakan berbagai koleksi buku, mulai dari novel, buku pelajaran, buku pengembangan diri,
buku anak, hingga buku referensi dari berbagai penerbit terpercaya. Dengan koleksi yang beragam,
harga yang kompetitif, dan pelayanan yang ramah, kami berkomitmen memberikan pengalaman
berbelanja buku yang mudah, nyaman, dan menyenangkan.
</p>
<p class="detail-desc">
Bagi kami, membaca bukan sekadar hobi, tetapi langkah awal untuk terus belajar dan berkembang.
</p>
<a class="btn" href="index.php">Back</a>

<?php include 'includes/footer.php'; ?>
