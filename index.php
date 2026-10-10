<?php
session_start();
include 'includes/header.php';
include 'includes/sidebar.php';

$showTutoriel = !isset($_SESSION['tutoriel_seen']);
?>
<div class="content">
  <div class="welcome-hero">
    <div>
      <span class="eyebrow">Portail multimédia</span>
      <h1>Bienvenue sur All_For_One JP</h1>
      <p>Transforme, extrait, convertis et optimise tes fichiers directement depuis une seule interface claire et moderne.</p>
      <div class="hero-actions">
        <a href="#features" class="primary-btn">Découvrir les outils</a>
      </div>
    </div>
    <div class="hero-badge">
      <strong>7</strong>
      <span>outils</span>
    </div>
  </div>

  <?php if ($showTutoriel): ?>
  <div class="tutoriel-banner">
    <p>👋 Bienvenue ! Découvrez nos fonctionnalités</p>
    <a href="tutoriel_seen.php" class="btn-close" aria-label="Fermer la bannière">×</a>
  </div>
  <?php endif; ?>

  <div class="features-grid" id="features">
    <a href="convert.php" class="feature"><span>🔁</span> Conversion</a>
    <a href="pdf.php" class="feature"><span>📄</span> PDF</a>
    <a href="extract.php" class="feature"><span>🎧</span> Extraction audio</a>
    <a href="ocr.php" class="feature"><span>🧠</span> OCR</a>
    <a href="speech.php" class="feature"><span>🗣️</span> SPEECH</a>
    <a href="qr.php" class="feature"><span>📱</span> QR</a>
    <a href="feedback.php" class="feature"><span>💬</span> Feedback</a>
  </div>
</div>
</body>
</html>
