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
$fixed = __DIR__ . '/includes/config_fixed.php';
$configPath = file_exists($fixed) ? $fixed : (__DIR__ . '/includes/config.php');
if (file_exists($configPath) && is_readable($configPath)) {
  $raw = @file_get_contents($configPath);
  if ($raw !== false) {
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
  <h1>🎧 Extraction audio depuis vidéo</h1>
  <div class="form-box">
    <form action="extract.php" method="post" enctype="multipart/form-data">
      <label>Choisir une vidéo :</label>
      <input type="file" name="video_file" accept="video/*" required>
      <button type="submit" name="extract">Extraire l'audio</button>
    </form>
  </div>

  <?php
  // Détecter une requête POST qui dépasse post_max_size/upload_max_filesize.
  // Quand POST dépasse post_max_size, $_POST et $_FILES sont vides.
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES)) {
    $postMax = ini_get('post_max_size');
    $uploadMax = ini_get('upload_max_filesize');
    echo "<p style='color:red;'>Erreur : la taille totale du formulaire dépasse la limite autorisée par le serveur.<br>Paramètres actuels : post_max_size = <strong>$postMax</strong>, upload_max_filesize = <strong>$uploadMax</strong>.<br>Augmentez ces valeurs dans le fichier php.ini (XAMPP) ou uploadez un fichier plus petit.</p>";
  }
  ?>

<?php
if (isset($_POST['extract'])) {
  $file = $_FILES['video_file'];

  // Gestion des erreurs PHP d'upload
  $err = $file['error'];
  if ($err !== UPLOAD_ERR_OK) {
    $msg = '';
    switch ($err) {
      case UPLOAD_ERR_INI_SIZE:
      case UPLOAD_ERR_FORM_SIZE:
        $msg = 'Le fichier est trop volumineux.';
        break;
      case UPLOAD_ERR_PARTIAL:
        $msg = 'Le fichier a été partiellement téléchargé.';
        break;
      case UPLOAD_ERR_NO_FILE:
        $msg = 'Aucun fichier envoyé.';
        break;
      case UPLOAD_ERR_NO_TMP_DIR:
        $msg = 'Répertoire temporaire manquant sur le serveur.';
        break;
      case UPLOAD_ERR_CANT_WRITE:
        $msg = "Impossible d'écrire le fichier sur le disque.";
        break;
      case UPLOAD_ERR_EXTENSION:
        $msg = 'Upload stoppé par une extension PHP.';
        break;
      default:
        $msg = 'Erreur lors de l\'upload.';
    }
    $postMax = ini_get('post_max_size');
    $uploadMax = ini_get('upload_max_filesize');
    echo "<p style='color:red;'>Erreur d'upload : $msg <br>Paramètres actuels : post_max_size = <strong>$postMax</strong>, upload_max_filesize = <strong>$uploadMax</strong>.</p>";
    exit;
  }

  if ($file['size'] > 100 * 1024 * 1024) {
    echo "<p style='color:red;'>Le fichier dépasse 100 Mo.</p>";
    exit;
  }

  $tmpPath = $uploadDir . uniqid("video_") . "_" . basename($file['name']);
  move_uploaded_file($file['tmp_name'], $tmpPath);

  $outputPath = $outputDir . uniqid("audio_") . ".mp3";

  // Vérifier que ffmpeg est disponible
  if (empty($FFMPEG_CMD)) {
    echo "<p style='color:red;'>❌ Extraction échouée : 'ffmpeg' introuvable sur le serveur.</p>";
    echo "<p>Pour corriger :<br>- Installez FFmpeg (https://ffmpeg.org/download.html).<br>- Sous Windows, téléchargez une build statique, placez le dossier (ex: <code>C:\\ffmpeg\\bin</code>) et ajoutez ce chemin au PATH système, puis redémarrez Apache.</p>";
    echo "<p>Si vous avez installé ffmpeg dans un chemin personnalisé, définissez la variable <code>\$FFMPEG_CMD</code> au début de <code>extract.php</code> avec le chemin complet vers <code>ffmpeg.exe</code>.</p>";
    exit;
  }

  // Commande FFmpeg pour extraire audio mp3 (utilise le chemin détecté)
  $cmd = escapeshellarg($FFMPEG_CMD) . ' -i ' . escapeshellarg($tmpPath) . ' -q:a 0 -map a ' . escapeshellarg($outputPath) . ' -y 2>&1';

  exec($cmd, $output, $return_var);

  if ($return_var === 0 && file_exists($outputPath)) {
    echo "<p style='color:green;'>✅ Extraction réussie !</p>";
    $safeOut = htmlspecialchars($outputPath);
    echo "<a href='$safeOut' download class='download-link'>⬇️ Télécharger le MP3</a>";
    echo "<div style='margin-top:12px;'><audio controls src='$safeOut'>Votre navigateur ne supporte pas l'élément audio.</audio></div>";
  } else {
    echo "<p style='color:red;'>❌ Extraction échouée.</p>";
    echo "<pre>" . implode("\n", $output) . "</pre>";
  }

  // Optionnel : supprime la vidéo uploadée pour économiser de l'espace
  // Si vous souhaitez supprimer le fichier vidéo uploadé après extraction, dé-commentez la ligne suivante :
  // unlink($tmpPath);
}
?>

</div>
</body>
</html>
