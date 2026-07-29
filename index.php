<?php
session_start();
include 'includes/header.php';
include 'includes/sidebar.php';

$showTutoriel = !isset($_SESSION['tutoriel_seen']);
?>
<div class="content">
  <h1>Bienvenue sur All_For_One JP</h1>
  <p>Sélectionne une fonctionnalité dans le menu de gauche.</p>

  <?php if ($showTutoriel): ?>
  <div class="tutoriel-banner">
    <p>👋 Bienvenue ! Découvrez nos fonctionnalités</p>
    <a href="tutoriel_seen.php" class="btn-close">×</a>
  </div>
  <?php endif; ?>

  <div class="features-grid">
    <a href="convert.php" class="feature">🔁 Conversion</a>
    <a href="pdf.php" class="feature">📄 PDF</a>
    <a href="extract.php" class="feature">🎧 Extraction audio</a>
    <a href="ocr.php" class="feature">🧠 OCR</a>
    <a href="speech.php" class="feature">🗣️ SPEECH</a>
    <a href="qr.php" class="feature">📱 QR</a>
    <a href="feedback.php" class="feature">💬 Feedback</a>
  </div>
</div>
</body>
</html>