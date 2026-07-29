<?php
include 'includes/header.php';
include 'includes/sidebar.php';

$feedbackDir = "feedbacks/";
if (!file_exists($feedbackDir)) mkdir($feedbackDir, 0777, true);

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES);
}

?>

<div class="content">
    <h1>💬 Feedback</h1>

    <div class="form-box">
        <form action="feedback.php" method="post" enctype="multipart/form-data">
            <label>Type de feedback :</label>
            <select name="type" required>
                <option value="">-- Sélectionner --</option>
                <option value="bug">Bug</option>
                <option value="suggestion">Suggestion</option>
                <option value="question">Question</option>
            </select>

            <label>Message :</label>
            <textarea name="message" rows="5" required placeholder="Votre message ici..."></textarea>

            <label>Joindre un fichier (optionnel) :</label>
            <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.pdf,.txt,.log">

            <button type="submit" name="send_feedback">Envoyer</button>
        </form>
    </div>

<?php
if (isset($_POST['send_feedback'])) {
    $type = sanitize($_POST['type']);
    $message = sanitize($_POST['message']);
    $date = date("Y-m-d H:i:s");

    $attachmentPath = null;

    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === 0) {
        $allowedExt = ['jpg', 'jpeg', 'png', 'pdf', 'txt', 'log'];
        $fileName = $_FILES['attachment']['name'];
        $fileTmp = $_FILES['attachment']['tmp_name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileExt, $allowedExt)) {
            $newFileName = uniqid('fb_attach_') . '.' . $fileExt;
            $destPath = $feedbackDir . $newFileName;
            if (move_uploaded_file($fileTmp, $destPath)) {
                $attachmentPath = $destPath;
            } else {
                echo "<p style='color:red;'>Erreur lors de l’upload du fichier.</p>";
            }
        } else {
            echo "<p style='color:red;'>Type de fichier non autorisé.</p>";
        }
    }

    // Enregistrement feedback au format JSON
    $feedbackData = [
        'type' => $type,
        'message' => $message,
        'date' => $date,
        'attachment' => $attachmentPath
    ];

    $jsonFile = $feedbackDir . uniqid('feedback_') . '.json';
    file_put_contents($jsonFile, json_encode($feedbackData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    echo "<p style='color:green;'>Merci pour votre feedback !</p>";
}
?>

    <!-- Les feedbacks ne sont plus affichés publiquement. Ils sont accessibles uniquement par l'administrateur via admin_feedback.php -->
    <div style="margin-top:24px;">
        <p>Merci pour votre retour. Vos feedbacks sont envoyés et seront traités par l'équipe.</p>
    </div>

</div>
</body>
</html>
