<?php
include 'includes/header.php';
include 'includes/sidebar.php';

$uploadDir = "uploads/";
$outputDir = "converted/";

if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
if (!file_exists($outputDir)) mkdir($outputDir, 0777, true);

// Chemin DIRECT vers votre Ghostscript (spécifique à votre installation)
$gsPath = 'C:\Program Files\gs\gs10.06.0\bin\gswin64c.exe';

// Vérifier si Ghostscript existe à cet emplacement
$hasGhostscript = file_exists($gsPath);

// Vérifier si pdfunite est disponible (optionnel)
function isPdfUniteAvailable() {
    $output = shell_exec('where pdfunite 2>nul');
    return !empty($output);
}
$hasPdfUnite = isPdfUniteAvailable();
?>

<div class="content">
  <h1>📄 Gestion des PDF</h1>
  
  <!-- Status des programmes -->
  <div style="margin-bottom:20px; padding:15px; background:#f8f9fa; border-radius:8px;">
    <h3>🔧 État des outils :</h3>
    <?php if ($hasGhostscript): ?>
    <p style="color:green;">✅ Ghostscript trouvé à : <?php echo $gsPath; ?></p>
    <?php else: ?>
    <p style="color:red;">❌ Ghostscript non trouvé à : <?php echo $gsPath; ?></p>
    <p>Vérifiez que le fichier existe bien à cet emplacement.</p>
    <?php endif; ?>
    <p>🔗 pdfunite : <?php echo $hasPdfUnite ? '✅ Installé' : '⚠️ Non installé - Utilisation de Ghostscript pour la fusion'; ?></p>
  </div>

  <div class="form-box">
    <h2>🧩 Fusionner des PDF</h2>
    <form action="pdf.php" method="post" enctype="multipart/form-data">
      <label>Fichiers PDF à fusionner :</label>
      <input type="file" name="merge_files[]" multiple accept="application/pdf" required>
      <button type="submit" name="merge">Fusionner</button>
    </form>
  </div>

  <div class="form-box">
    <h2>🔽 Compresser un PDF</h2>
    <?php if (!$hasGhostscript): ?>
    <div style="background:#ffcdd2; color:#b71c1c; padding:10px; border-radius:5px; margin-bottom:15px;">
      ❌ Ghostscript n'est pas trouvé au chemin : <?php echo $gsPath; ?>
      <br>👉 Vérifiez que le fichier gswin64c.exe existe bien dans ce dossier.
    </div>
    <?php endif; ?>
    <form action="pdf.php" method="post" enctype="multipart/form-data">
      <label>PDF à compresser :</label>
      <input type="file" name="compress_file" accept="application/pdf" required>
      <button type="submit" name="compress" <?php echo !$hasGhostscript ? 'disabled' : ''; ?>>Compresser</button>
    </form>
  </div>

<?php
// Fonction pour exécuter Ghostscript avec le chemin spécifique
function runGhostscript($args) {
    global $gsPath;
    
    if (!file_exists($gsPath)) {
        return false;
    }
    
    // Construire la commande avec le chemin complet entre guillemets (important pour les chemins avec espaces)
    $cmd = '"' . $gsPath . '" ' . $args;
    
    // Exécuter la commande
    shell_exec($cmd);
    
    return true;
}

// FUSION
if (isset($_POST['merge'])) {
    $files = $_FILES['merge_files'];
    if (count($files['tmp_name']) < 2) {
        echo "<p style='color:red;'>❌ Veuillez sélectionner au moins deux fichiers PDF.</p>";
    } else {
        $outputPDF = $outputDir . uniqid("merged_") . ".pdf";
        $tempFiles = [];
        
        // Sauvegarder tous les fichiers uploadés
        $fileCount = 0;
        foreach ($files['tmp_name'] as $key => $tmp) {
            if (!empty($tmp) && $files['error'][$key] === 0) {
                $newPath = $uploadDir . uniqid("pdf_") . ".pdf";
                if (move_uploaded_file($tmp, $newPath)) {
                    $tempFiles[] = $newPath;
                    $fileCount++;
                }
            }
        }
        
        if ($fileCount < 2) {
            echo "<p style='color:red;'>❌ Erreur lors de l'upload des fichiers.</p>";
        } else {
            $success = false;
            
            // Essayer pdfunite d'abord si disponible
            if ($hasPdfUnite) {
                $cmd = "pdfunite ";
                foreach ($tempFiles as $f) $cmd .= escapeshellarg($f) . " ";
                $cmd .= escapeshellarg($outputPDF);
                shell_exec($cmd);
                $success = file_exists($outputPDF) && filesize($outputPDF) > 0;
            }
            
            // Fallback avec Ghostscript
            if (!$success && $hasGhostscript) {
                $args = "-dNOPAUSE -sDEVICE=pdfwrite -sOUTPUTFILE=" . escapeshellarg($outputPDF) . " -dBATCH ";
                foreach ($tempFiles as $f) $args .= escapeshellarg($f) . " ";
                runGhostscript($args);
                $success = file_exists($outputPDF) && filesize($outputPDF) > 0;
            }
            
            if ($success) {
                echo "<p style='color:green;'>✅ Fusion réussie !</p>";
                echo "<a href='" . htmlspecialchars($outputPDF) . "' download class='download-link'>⬇️ Télécharger le PDF fusionné</a>";
            } else {
                echo "<p style='color:red;'>❌ Échec de la fusion PDF.</p>";
            }
            
            // Nettoyage
            foreach ($tempFiles as $f) {
                if (file_exists($f)) unlink($f);
            }
        }
    }
}

// COMPRESSION
if (isset($_POST['compress']) && $hasGhostscript) {
    $file = $_FILES['compress_file'];
    
    if ($file['error'] !== 0) {
        echo "<p style='color:red;'>❌ Erreur lors de l'upload. Code erreur : " . $file['error'] . "</p>";
    } elseif ($file['size'] > 100 * 1024 * 1024) {
        echo "<p style='color:red;'>❌ Le fichier dépasse 100 Mo.</p>";
    } elseif ($file['size'] == 0) {
        echo "<p style='color:red;'>❌ Fichier vide.</p>";
    } else {
        $input = $uploadDir . uniqid("pdf_") . ".pdf";
        $output = $outputDir . uniqid("compressed_") . ".pdf";
        
        if (move_uploaded_file($file['tmp_name'], $input)) {
            echo "<p>📊 Taille originale : " . round(filesize($input)/1024/1024, 2) . " Mo</p>";
            
            // Commande Ghostscript pour compression
            $args = "-sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/ebook -dNOPAUSE -dQUIET -dBATCH -sOutputFile=" . escapeshellarg($output) . " " . escapeshellarg($input);
            
            // Exécuter
            runGhostscript($args);
            
            // Attendre un peu que le fichier soit écrit
            sleep(1);
            
            if (file_exists($output) && filesize($output) > 0) {
                $compressedSize = filesize($output);
                $reduction = round((1 - $compressedSize/filesize($input)) * 100, 2);
                
                echo "<p style='color:green;'>✅ Compression réussie !</p>";
                echo "<p>📦 Taille compressée : " . round($compressedSize/1024/1024, 2) . " Mo (réduction de {$reduction}%)</p>";
                echo "<a href='" . htmlspecialchars($output) . "' download class='download-link'>⬇️ Télécharger le PDF compressé</a>";
                
                // Nettoyer le fichier original
                unlink($input);
            } else {
                echo "<p style='color:red;'>❌ Échec de la compression.</p>";
                // Afficher plus de détails pour le debug
                echo "<p>Debug : Le fichier de sortie n'a pas été créé.</p>";
            }
        } else {
            echo "<p style='color:red;'>❌ Erreur lors de la sauvegarde du fichier.</p>";
        }
    }
}
?>
</div>
</body>
</html>