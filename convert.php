
<?php
include 'includes/header.php';
include 'includes/sidebar.php';

$uploadDir = "uploads/";
$outputDir = "converted/";

if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
if (!file_exists($outputDir)) mkdir($outputDir, 0777, true);
// Permettre de forcer le chemin vers ffmpeg si nécessaire (ex: 'C:\\ffmpeg\\bin\\ffmpeg.exe')
$FFMPEG_CMD = 'ffmpeg';

// Fonction utilitaire pour repérer ffmpeg sur le système
function find_ffmpeg($candidate)
{
  // Si l'utilisateur fournit déjà un chemin, teste-le
  if ($candidate && is_executable($candidate)) return $candidate;

  $checkCommands = [
    'where ffmpeg', // Windows
    'which ffmpeg', // Unix
    'command -v ffmpeg'
  ];

  foreach ($checkCommands as $cmd) {
    $out = @shell_exec($cmd . ' 2>&1');
    if ($out) {
      // Prendre la première ligne qui ressemble à un chemin
      $lines = preg_split('/\r?\n/', trim($out));
      foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;
        // Sous Windows, `where` retourne le chemin complet
        if (is_executable($line)) return $line;
      }
    }
  }

  // Si rien trouvé, retourne null
  return null;
}

// Charger configuration si présente (lecture sûre pour éviter d'exécuter un fichier corrompu)
// Prefer a fixed config file if present (safe fallback) to avoid parse errors
$fixed = __DIR__ . '/includes/config_fixed.php';
$configPath = file_exists($fixed) ? $fixed : (__DIR__ . '/includes/config.php');
if (file_exists($configPath) && is_readable($configPath)) {
  $raw = @file_get_contents($configPath);
  if ($raw !== false) {
    // Rechercher une affectation simple 'ffmpeg_cmd' => '...'
    if (preg_match("/['\"]ffmpeg_cmd['\"]\s*=>\s*['\"]([^'\"]*)['\"]/i", $raw, $m)) {
      $val = trim($m[1]);
      if ($val !== '') $FFMPEG_CMD = $val;
    }
  }
}

// Recherche de ffmpeg si non forcé
if (empty($FFMPEG_CMD)) {
  $detected = find_ffmpeg('ffmpeg');
  if ($detected) $FFMPEG_CMD = $detected;
  else $FFMPEG_CMD = null;
}
?>








































<div class="content">
  <h1>🔁 Convertir un fichier</h1>
  <p>Sélectionnez un fichier, le type de conversion et le format de sortie.</p>

  <form action="convert.php" method="post" enctype="multipart/form-data" class="form-box">
    <label for="file">Fichier à convertir :</label>
    <input type="file" name="file" id="file" required>

    <label for="type">Type de conversion :</label>
    <select name="type" id="type" required>
      <option value="audio">Audio</option>
      <option value="video">Vidéo</option>
      <option value="image">Image</option>
    </select>

    <label for="format">Format de sortie :</label>
    <select name="format" id="format" required>
      <option value="mp3">MP3</option>
      <option value="wav">WAV</option>
      <option value="mp4">MP4</option>
      <option value="avi">AVI</option>
      <option value="jpg">JPG</option>
      <option value="png">PNG</option>
    </select>

    <button type="submit" name="convertir">Convertir</button>
  </form>

  <?php
  if (isset($_POST['convertir'])) {
    $uploadDir = "uploads/";
    $outputDir = "converted/";

    $fileTmp = $_FILES['file']['tmp_name'];
    $fileName = basename($_FILES['file']['name']);
    $targetFile = $uploadDir . uniqid() . "_" . $fileName;

    // Créer les dossiers si non existants
    if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
    if (!file_exists($outputDir)) mkdir($outputDir, 0777, true);

    // Basic upload error handling
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
      echo "<p style='color:red'>❌ Échec de l'envoi du fichier. Code erreur: " . ($_FILES['file']['error'] ?? 'n/a') . "</p>";
      exit;
    }

    if (!move_uploaded_file($fileTmp, $targetFile)) {
      echo "<p style='color:red'>❌ Échec de l'envoi du fichier.</p>";
      exit;
    }

    // Détecter le type de fichier source
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $targetFile);
    finfo_close($finfo);

    $sourceCategory = '';
    if (strpos($mime, 'video/') === 0) $sourceCategory = 'video';
    elseif (strpos($mime, 'audio/') === 0) $sourceCategory = 'audio';
    elseif (strpos($mime, 'image/') === 0) $sourceCategory = 'image';
    else $sourceCategory = 'other';

    $format = strtolower($_POST['format']);
    // map output formats to categories
    $formatCategory = in_array($format, ['mp3','wav']) ? 'audio' : (in_array($format, ['mp4','avi']) ? 'video' : (in_array($format, ['jpg','jpeg','png']) ? 'image' : 'other'));

    // Validation des conversions autorisées
    $allowed = false;
    // Règles :
    // - video -> audio, video
    // - audio -> audio
    // - image -> image
    if ($sourceCategory === 'video' && in_array($formatCategory, ['audio','video'])) $allowed = true;
    if ($sourceCategory === 'audio' && $formatCategory === 'audio') $allowed = true;
    if ($sourceCategory === 'image' && $formatCategory === 'image') $allowed = true;

    if (!$allowed) {
      echo "<p style='color:red'>❌ Conversion non autorisée : impossible de convertir <strong>" . htmlspecialchars($sourceCategory) . "</strong> en <strong>" . htmlspecialchars($formatCategory) . "</strong>.</p>";
      // supprimer fichier uploadé pour propreté
      if (file_exists($targetFile)) @unlink($targetFile);
      exit;
    }

    $outputFile = $outputDir . uniqid('converted_') . '.' . $format;

    // Si conversion image -> image, on utilise GD si possible (PHP), évite d'appeler ffmpeg
    if ($sourceCategory === 'image' && $formatCategory === 'image') {
      $ext = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
      $img = null;
      switch ($ext) {
        case 'png': $img = @imagecreatefrompng($targetFile); break;
        case 'jpg': case 'jpeg': $img = @imagecreatefromjpeg($targetFile); break;
      }
      if (!$img) {
        echo "<p style='color:red'>❌ Impossible de charger l'image source pour la conversion.</p>";
        @unlink($targetFile);
        exit;
      }
      // write target
      if ($format === 'png') {
        imagepng($img, $outputFile);
      } else {
        imagejpeg($img, $outputFile, 90);
      }
      imagedestroy($img);

      if (file_exists($outputFile)) {
        echo "<p style='margin-top: 20px; color: green;'>✅ Conversion image réussie !</p>";
        echo "<a href='$outputFile' download class='download-link'>⬇️ Télécharger</a>";
      } else {
        echo "<p style='color:red'>❌ Échec de la conversion image.</p>";
      }
      // cleanup source
      @unlink($targetFile);
      exit;
    }

    // Pour les conversions audio/video, nécessite ffmpeg
    if (empty($FFMPEG_CMD)) {
      echo "<p style='color:red'>❌ FFmpeg introuvable sur le serveur. Configurez le chemin dans includes/config.php ou installez FFmpeg.</p>";
      @unlink($targetFile);
      exit;
    }

    // Construire la commande selon le type de sortie
    if ($formatCategory === 'audio') {
      // extraire ou convertir l'audio
      // ex: ffmpeg -i input -q:a 0 -map a output.mp3
      $cmd = escapeshellarg($FFMPEG_CMD) . ' -i ' . escapeshellarg($targetFile) . ' -q:a 0 -map a ' . escapeshellarg($outputFile) . ' -y 2>&1';
    } else { // video -> video
      $cmd = escapeshellarg($FFMPEG_CMD) . ' -i ' . escapeshellarg($targetFile) . ' ' . escapeshellarg($outputFile) . ' -y 2>&1';
    }

    $output = shell_exec($cmd);

    if (file_exists($outputFile)) {
      echo "<p style='margin-top: 20px; color: green;'>✅ Conversion réussie !</p>";
      echo "<a href='$outputFile' download class='download-link'>⬇️ Télécharger le fichier converti</a>";
    } else {
      echo "<p style='color:red'>❌ La conversion a échoué. Vérifie le format ou le type de fichier.</p>";
      echo "<pre>" . htmlspecialchars($output) . "</pre>";
    }

    // Nettoyage optionnel
    @unlink($targetFile);
  }
  ?>
</div>

</body>
</html>
