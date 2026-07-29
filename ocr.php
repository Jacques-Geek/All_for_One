<?php
include 'includes/header.php';
include 'includes/sidebar.php';

$uploadDir = "uploads/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Chemin absolu vers Tesseract (votre installation)
$tesseractPath = 'C:\Program Files\Tesseract-OCR\tesseract.exe';

// Vérifier si Tesseract existe
$hasTesseract = file_exists($tesseractPath);

// Fonction pour exécuter Tesseract
function runTesseract($input, $output, $lang = 'fra') {
    global $tesseractPath;
    
    if (!file_exists($tesseractPath)) {
        return ['success' => false, 'error' => 'Tesseract non trouvé'];
    }
    
    // Construire la commande
    $cmd = '"' . $tesseractPath . '" ' . escapeshellarg($input) . ' ' . escapeshellarg($output) . ' -l ' . $lang;
    
    // Exécuter
    $output_lines = [];
    $return_var = 0;
    exec($cmd . ' 2>&1', $output_lines, $return_var);
    
    // Vérifier le résultat
    $txtFile = $output . '.txt';
    if (file_exists($txtFile) && filesize($txtFile) > 0) {
        $text = file_get_contents($txtFile);
        unlink($txtFile); // Nettoyer
        return ['success' => true, 'text' => $text];
    } else {
        return [
            'success' => false, 
            'error' => 'Échec de Tesseract',
            'return_code' => $return_var,
            'output' => implode("\n", $output_lines)
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OCR - Reconnaissance de texte</title>
    <style>
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        
        .warning-message {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #ffeeba;
        }
        
        .result-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .result-text {
            background: white;
            border: 1px solid #ced4da;
            border-radius: 5px;
            padding: 15px;
            max-height: 400px;
            overflow-y: auto;
            font-family: monospace;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-info {
            background: #17a2b8;
            color: white;
        }
        
        .btn-info:hover {
            background: #138496;
        }
        
        .debug-info {
            background: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="content">
    <h1>🧠 OCR - Reconnaissance de texte</h1>
    
    <!-- Statut de Tesseract -->
    <?php if ($hasTesseract): ?>
        <div class="success-message">
            <strong>✅ Tesseract est installé</strong><br>
            Chemin : <?php echo $tesseractPath; ?>
        </div>
    <?php else: ?>
        <div class="error-message">
            <strong>❌ Tesseract n'est pas installé ou n'a pas été trouvé</strong><br>
            Chemin recherché : <?php echo $tesseractPath; ?><br>
            <br>
            <strong>Solution :</strong>
            <ol style="margin-top:10px; margin-left:20px;">
                <li>Téléchargez Tesseract depuis : <a href="https://github.com/UB-Mannheim/tesseract/wiki" target="_blank">https://github.com/UB-Mannheim/tesseract/wiki</a></li>
                <li>Choisissez "tesseract-ocr-w64-setup-5.3.3.20231005.exe" (64 bits)</li>
                <li>Pendant l'installation, cochez "French" dans la liste des langues</li>
                <li>Installez dans : C:\Program Files\Tesseract-OCR</li>
            </ol>
        </div>
    <?php endif; ?>
    
    <!-- Formulaire d'upload -->
    <div class="form-box">
        <h2>📤 Sélectionner un fichier</h2>
        <form action="ocr.php" method="post" enctype="multipart/form-data">
            <div style="margin-bottom:15px;">
                <label for="file_ocr" style="display:block; margin-bottom:5px; font-weight:bold;">Fichier (image ou PDF) :</label>
                <input type="file" 
                       name="file_ocr" 
                       id="file_ocr" 
                       accept="image/*,application/pdf" 
                       required
                       style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;">
            </div>
            
            <div style="margin-bottom:15px;">
                <label for="ocr_language" style="display:block; margin-bottom:5px; font-weight:bold;">Langue :</label>
                <select name="ocr_language" id="ocr_language" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;">
                    <option value="fra">Français</option>
                    <option value="eng">Anglais</option>
                    <option value="deu">Allemand</option>
                    <option value="spa">Espagnol</option>
                    <option value="ita">Italien</option>
                    <option value="fra+eng">Français + Anglais</option>
                </select>
                <small style="color:#666;">Maintenez Ctrl pour sélectionner plusieurs langues</small>
            </div>
            
            <button type="submit" 
                    name="do_ocr" 
                    class="btn btn-primary" 
                    <?php echo !$hasTesseract ? 'disabled' : ''; ?>
                    style="width:100%;">
                🔍 Lancer l'OCR
            </button>
        </form>
    </div>
    
    <?php
    // Traitement OCR
    if (isset($_POST['do_ocr']) && $hasTesseract) {
        $file = $_FILES['file_ocr'];
        $lang = $_POST['ocr_language'] ?? 'fra';
        
        echo '<div class="result-box">';
        echo '<h3>📊 Résultat du traitement</h3>';
        
        // Vérification des erreurs d'upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => 'Le fichier dépasse la taille maximale autorisée par PHP',
                UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la taille maximale autorisée par le formulaire',
                UPLOAD_ERR_PARTIAL => 'Le fichier n\'a été que partiellement téléchargé',
                UPLOAD_ERR_NO_FILE => 'Aucun fichier n\'a été téléchargé',
                UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
                UPLOAD_ERR_CANT_WRITE => 'Échec de l\'écriture du fichier sur le disque',
                UPLOAD_ERR_EXTENSION => 'Une extension PHP a arrêté le téléchargement'
            ];
            $errorMsg = $uploadErrors[$file['error']] ?? 'Erreur inconnue';
            echo '<div class="error-message">❌ Erreur upload : ' . $errorMsg . '</div>';
        } 
        // Vérification du type de fichier
        else {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/tiff', 'application/pdf'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowed)) {
                echo '<div class="error-message">❌ Type de fichier non supporté : ' . $mimeType . '<br>';
                echo 'Types acceptés : JPEG, PNG, GIF, BMP, TIFF, PDF</div>';
            } else {
                // Sauvegarder le fichier
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filePath = $uploadDir . uniqid("ocr_") . '.' . $extension;
                
                if (move_uploaded_file($file['tmp_name'], $filePath)) {
                    echo '<div class="success-message">✅ Fichier uploadé avec succès : ' . basename($file['name']) . '</div>';
                    
                    $imageForOCR = $filePath;
                    
                    // Traitement PDF
                    if ($mimeType === 'application/pdf') {
                        echo '<p>🔄 Conversion du PDF en cours...</p>';
                        
                        if (class_exists('Imagick')) {
                            try {
                                $imagick = new Imagick();
                                $imagick->readImage($filePath . '[0]'); // Première page seulement
                                $imagick->setImageResolution(300, 300);
                                $imagick->setImageFormat('png');
                                
                                $imageForOCR = $uploadDir . uniqid("ocr_img_") . '.png';
                                $imagick->writeImage($imageForOCR);
                                $imagick->clear();
                                $imagick->destroy();
                                
                                echo '<div class="success-message">✅ Conversion PDF terminée</div>';
                            } catch (Exception $e) {
                                echo '<div class="error-message">❌ Erreur conversion PDF : ' . $e->getMessage() . '</div>';
                                $imageForOCR = null;
                            }
                        } else {
                            echo '<div class="warning-message">⚠️ Imagick non installé - Traitement du PDF impossible</div>';
                            $imageForOCR = null;
                        }
                    }
                    
                    // Lancer l'OCR
                    if ($imageForOCR && file_exists($imageForOCR)) {
                        echo '<p>🔍 Analyse OCR en cours...</p>';
                        
                        $outputBase = $uploadDir . uniqid("ocr_result_");
                        $result = runTesseract($imageForOCR, $outputBase, $lang);
                        
                        if ($result['success']) {
                            echo '<div class="success-message">✅ OCR terminé avec succès !</div>';
                            
                            // Afficher le texte
                            echo '<h4>📝 Texte extrait :</h4>';
                            echo '<div class="result-text">' . htmlspecialchars($result['text']) . '</div>';
                            
                            // Boutons d'action
                            echo '<div class="btn-group">';
                            echo '<button class="btn btn-success" onclick="copyText()">📋 Copier</button>';
                            echo '<button class="btn btn-info" onclick="downloadText()">💾 Télécharger</button>';
                            echo '</div>';
                            
                            // Script pour copier/télécharger
                            echo '<script>
                                function copyText() {
                                    const text = ' . json_encode($result['text']) . ';
                                    navigator.clipboard.writeText(text).then(
                                        function() { alert("Texte copié dans le presse-papiers !"); },
                                        function() { alert("Erreur lors de la copie"); }
                                    );
                                }
                                
                                function downloadText() {
                                    const text = ' . json_encode($result['text']) . ';
                                    const blob = new Blob([text], { type: "text/plain;charset=utf-8" });
                                    const url = URL.createObjectURL(blob);
                                    const a = document.createElement("a");
                                    a.href = url;
                                    a.download = "ocr_result_' . date('Y-m-d_H-i-s') . '.txt";
                                    document.body.appendChild(a);
                                    a.click();
                                    document.body.removeChild(a);
                                    URL.revokeObjectURL(url);
                                }
                            </script>';
                        } else {
                            echo '<div class="error-message">❌ Échec de l\'OCR</div>';
                            
                            // Afficher les informations de débogage
                            echo '<div class="debug-info">';
                            echo '<strong>Informations de débogage :</strong><br>';
                            echo 'Code retour : ' . ($result['return_code'] ?? 'N/A') . '<br>';
                            if (!empty($result['error'])) {
                                echo 'Erreur : ' . htmlspecialchars($result['error']) . '<br>';
                            }
                            if (!empty($result['output'])) {
                                echo 'Sortie Tesseract :<br><pre>' . htmlspecialchars($result['output']) . '</pre>';
                            }
                            echo '</div>';
                            
                            echo '<p><strong>Vérifiez que :</strong></p>';
                            echo '<ul>';
                            echo '<li>L\'image contient bien du texte lisible</li>';
                            echo '<li>La langue sélectionnée est correcte</li>';
                            echo '<li>Les données d\'entraînement pour la langue "' . $lang . '" sont installées</li>';
                            echo '</ul>';
                        }
                        
                        // Nettoyer l'image temporaire (si différente du fichier original)
                        if ($imageForOCR !== $filePath && file_exists($imageForOCR)) {
                            unlink($imageForOCR);
                        }
                    }
                    
                    // Nettoyer le fichier original
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    
                } else {
                    echo '<div class="error-message">❌ Erreur lors de la sauvegarde du fichier</div>';
                }
            }
        }
        
        echo '</div>'; // Fermeture result-box
    }
    ?>
    
    <!-- Section d'aide -->
    <div class="form-box" style="margin-top:20px;">
        <h3>ℹ️ Aide</h3>
        <p><strong>Formats supportés :</strong></p>
        <ul>
            <li>Images : JPEG, PNG, GIF, BMP, TIFF</li>
            <li>Documents : PDF (première page seulement)</li>
        </ul>
        
        <p><strong>Langues disponibles :</strong></p>
        <ul>
            <li>Français (fra) - Installé par défaut</li>
            <li>Anglais (eng) - Installé par défaut</li>
            <li>Allemand (deu), Espagnol (spa), Italien (ita) - À installer via le language pack</li>
        </ul>
        
        <p><strong>Conseils :</strong></p>
        <ul>
            <li>Utilisez des images de bonne qualité (300 DPI minimum)</li>
            <li>Évitez les images trop sombres ou floues</li>
            <li>Pour les PDF, seule la première page est traitée</li>
        </ul>
    </div>
</div>

<!-- Style supplémentaire -->
<style>
    .form-box {
        background: white;
        border-radius: 8px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .form-box h2, .form-box h3 {
        margin-top: 0;
        color: #333;
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
    }
    
    label {
        font-weight: 600;
        color: #555;
    }
    
    input[type="file"] {
        background: #f8f9fa;
        cursor: pointer;
    }
    
    input[type="file"]:hover {
        background: #e9ecef;
    }
    
    select {
        background: white;
        cursor: pointer;
    }
    
    select:hover {
        border-color: #007bff;
    }
    
    button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    button:not(:disabled):hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    .debug-info {
        background: #1e1e1e;
        color: #0f0;
        padding: 15px;
        border-radius: 5px;
        font-size: 13px;
        margin-top: 10px;
    }
</style>

</body>
</html>