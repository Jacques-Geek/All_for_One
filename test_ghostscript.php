<?php
// Test 1 : Vérifier si Ghostscript est accessible
$output = shell_exec('gs -v 2>&1');
if ($output && strpos($output, 'Ghostscript') !== false) {
    echo "✅ Ghostscript est installé :<br><pre>$output</pre>";
} else {
    echo "❌ Ghostscript non trouvé. Chemin actuel : " . getenv('PATH');
}

// Test 2 : Essayer de compresser un PDF (si vous en avez un)
$testPdf = __DIR__ . '/test.pdf'; // Mettez un petit PDF ici pour tester
if (file_exists($testPdf)) {
    $outputFile = __DIR__ . '/test_compressed.pdf';
    $cmd = "gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/ebook -dNOPAUSE -dQUIET -dBATCH -sOutputFile=" . escapeshellarg($outputFile) . " " . escapeshellarg($testPdf);
    shell_exec($cmd);
    
    if (file_exists($outputFile)) {
        echo "<br>✅ Compression test réussie !";
        unlink($outputFile);
    } else {
        echo "<br>❌ Échec de la compression test";
    }
}
?>