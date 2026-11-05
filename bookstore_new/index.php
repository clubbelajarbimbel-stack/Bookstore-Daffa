<?php
// index.php - HOME PAGE
require_once 'includes/config.php';
$base = '';
$page_title = 'Home';
include 'includes/header.php';
?>

<!-- >>> CHANGE HERE: the welcome text, tagline and button on the home page -->
<div class="hero">
  <h1>Welcome to<br><em>Paper Moon</em><br>Bookstore!</h1>
  <p class="hero-tagline">Good books. Better you.</p>
  <a href="books.php" class="btn btn-primary">go check it!</a>
  <div class="book-icon">📚</div>
</div>

<?php include 'includes/footer.php'; ?>
