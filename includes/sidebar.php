<div class="sidebar">
  <div class="sidebar-header">
    <h2>📁 All_For_One JP</h2>
    <button class="menu-toggle" onclick="toggleMenu()">☰ Menu</button>
  </div>

  <p class="sidebar-subtitle">Navigation</p>

  <ul class="sidebar-menu">
    <li><a href="index.php" class="active">🏠 Accueil</a></li>
    <li><a href="convert.php">🔁 Convertir</a></li>
    <li><a href="pdf.php">📄 PDF</a></li>
    <li><a href="extract.php">🎧 Extraction audio</a></li>
    <li><a href="ocr.php">🧠 OCR</a></li>
    <li><a href="speech.php">🗣️ Speech</a></li>
    <li><a href="qr.php">📱 QR Code</a></li>
    <li><a href="feedback.php">💬 Feedback</a></li>
  </ul>
</div>

<script>
function toggleMenu() {
  document.querySelector('.sidebar-menu').classList.toggle('show');
}
</script>
