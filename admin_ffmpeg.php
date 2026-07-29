<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// Small admin tool to detect ffmpeg and set path in includes/config.php
include 'includes/header.php';
include 'includes/sidebar.php';

function run_cmd($cmd) {
    $out = [];
    $ret = null;
    @exec($cmd . ' 2>&1', $out, $ret);
    return ['out' => implode("\n", $out), 'ret' => $ret];
}

// Load existing config if present
$fixed = __DIR__ . '/includes/config_fixed.php';
$configPath = file_exists($fixed) ? $fixed : (__DIR__ . '/includes/config.php');
$current = '';
if (file_exists($configPath) && is_readable($configPath)) {
    $raw = @file_get_contents($configPath);
    if ($raw !== false) {
        if (preg_match("/['\"]ffmpeg_cmd['\"]\s*=>\s*['\"]([^'\"]*)['\"]/i", $raw, $m)) {
            $current = trim($m[1]);
        }
    }
}

$message = '';

// CSRF token
if (empty($_SESSION['csrf_ffmpeg'])) $_SESSION['csrf_ffmpeg'] = bin2hex(random_bytes(16));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf_ffmpeg']) {
        $message = 'Jeton CSRF invalide.';
    } else if (isset($_POST['set_path'])) {
        $path = trim($_POST['ffmpeg_path']);
        // Save to includes/config.php
        $php = "<?php\n// Updated by admin_ffmpeg.php\n\$CONFIG = [\n    'ffmpeg_cmd' => '" . str_replace("'","\\'", $path) . "'\n];\n";
        if (@file_put_contents($configPath, $php) !== false) {
            $message = 'Chemin enregistré dans includes/config.php';
            $current = $path;
        } else {
            $message = 'Impossible d\'écrire le fichier de configuration. Vérifiez les permissions.';
        }
    } else if (isset($_POST['download'])) {
        // Attempt to download a windows build (non-interactive). Be careful with server permissions.
        $zipUrl = 'https://www.gyan.dev/ffmpeg/builds/ffmpeg-release-essentials.zip';
        $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ffmpeg_dl.zip';
        set_time_limit(300);
        $ctx = stream_context_create(['http' => ['timeout' => 120]]);
        $data = @file_get_contents($zipUrl, false, $ctx);
        if (!$data) {
            $message = 'Téléchargement échoué. Vérifiez la connectivité ou téléchargez manuellement.';
        } else {
            if (file_put_contents($tmp, $data) === false) {
                $message = 'Impossible d\'écrire le fichier temporaire.';
            } else {
                $extractTo = __DIR__ . DIRECTORY_SEPARATOR . 'ffmpeg';
                if (!is_dir($extractTo)) @mkdir($extractTo, 0777, true);
                $zip = new ZipArchive();
                if ($zip->open($tmp) === true) {
                    $zip->extractTo($extractTo);
                    $zip->close();
                    // find ffmpeg.exe inside extracted dir
                    $ff = null;
                    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($extractTo));
                    foreach ($it as $f) {
                        if (strtolower($f->getFilename()) === 'ffmpeg.exe') { $ff = $f->getPathname(); break; }
                    }
                    if ($ff) {
                        // Save path to config
                        $relative = $ff; // absolute path
                        $php = "<?php\n// Updated by admin_ffmpeg.php (download)\n\$CONFIG = [\n    'ffmpeg_cmd' => '" . str_replace("'","\\'", $relative) . "'\n];\n";
                        if (@file_put_contents($configPath, $php) !== false) {
                            $message = 'FFmpeg téléchargé et configuré : ' . htmlspecialchars($relative);
                            $current = $relative;
                        } else {
                            $message = 'Téléchargé mais impossible d\'écrire includes/config.php. Copiez manuellement le chemin : ' . htmlspecialchars($relative);
                        }
                    } else {
                        $message = 'Extraction réussie mais ffmpeg.exe introuvable dans l\'archive.';
                    }
                } else {
                    $message = 'Impossible d\'ouvrir l\'archive ZIP. Vérifiez que l\'extension Zip est activée.';
                }
                @unlink($tmp);
            }
        }
    }
}

// Detection helpers
$detected = null;
$version = null;
// Try configured path first
if (!empty($current) && file_exists($current)) {
    $detected = $current;
    $r = run_cmd(escapeshellarg($current) . ' -version');
    $version = $r['out'];
} else {
    // Try system commands
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $r = run_cmd('where ffmpeg');
        if ($r['ret'] === 0 && trim($r['out']) !== '') {
            $lines = preg_split('/\r?\n/', trim($r['out']));
            $candidate = trim($lines[0]);
            if (file_exists($candidate)) { $detected = $candidate; $version = run_cmd(escapeshellarg($candidate) . ' -version')['out']; }
        }
    } else {
        $r = run_cmd('which ffmpeg');
        if ($r['ret'] === 0 && trim($r['out']) !== '') {
            $candidate = trim($r['out']);
            if (file_exists($candidate)) { $detected = $candidate; $version = run_cmd(escapeshellarg($candidate) . ' -version')['out']; }
        }
    }
}

?>
<div class="content">
  <h1>🛠️ Outils FFmpeg (Admin)</h1>

  <?php if ($message): ?>
    <div style="padding:10px; background:#fff3cd; border-radius:6px; margin-bottom:12px;"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>

  <div class="form-box">
    <h2>Détection</h2>
    <p>Chemin configuré : <strong><?php echo htmlspecialchars($current ?: 'non défini'); ?></strong></p>
    <p>Chemin détecté : <strong><?php echo htmlspecialchars($detected ?: 'non détecté'); ?></strong></p>
    <?php if ($version): ?>
      <pre style="background:#f3f4f6; padding:8px; border-radius:6px; max-height:200px; overflow:auto"><?php echo htmlspecialchars($version); ?></pre>
    <?php endif; ?>

    <form method="post">
      <label>Définir un chemin personnalisé vers ffmpeg (ex: C:\\ffmpeg\\bin\\ffmpeg.exe) :</label>
      <input type="text" name="ffmpeg_path" value="<?php echo htmlspecialchars($current); ?>" style="width:100%; padding:8px; margin-top:6px;" />
      <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_ffmpeg']; ?>" />
      <div style="margin-top:10px; display:flex; gap:8px;">
        <button type="submit" name="set_path">Enregistrer</button>
        <button type="submit" name="download">Télécharger et extraire FFmpeg dans le dossier du projet</button>
      </div>
    </form>
  </div>

  <p style="margin-top:12px;">Instructions rapides : si ffmpeg est installé mais introuvable, mettez le chemin absolu ci-dessus. Sinon, cliquez sur <strong>Télécharger</strong> pour tenter une installation locale (nécessite que PHP ait les droits d'écriture et l'extension Zip activée).</p>

  <p><a href="admin_feedback.php">Retour admin</a></p>
</div>

</body>
</html>
