<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header('Location: admin_login.php');
  exit;
}
include 'includes/header.php';
include 'includes/sidebar.php';

$feedbackDir = "feedbacks/";
$files = glob($feedbackDir . "*.json");
  
?>

<div class="content">
  <h1>📝 Gestion des Feedbacks (Admin)</h1>

  <?php if (empty($files)): ?>
    <p>Aucun feedback reçu pour le moment.</p>
  <?php else: ?>
    <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse: collapse;">
      <thead>
        <tr>
          <th>Date</th>
          <th>Type</th>
          <th>Message</th>
          <th>Pièce jointe</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($files as $file) {
          $data = json_decode(file_get_contents($file), true);
          if (!$data) continue;
          ?>
          <tr>
            <td><?= htmlspecialchars($data['date']) ?></td>
            <td><?= htmlspecialchars($data['type']) ?></td>
            <td style="white-space: pre-wrap;"><?= htmlspecialchars($data['message']) ?></td>
            <td>
              <?php
              if (!empty($data['attachment']) && file_exists($data['attachment'])) {
                $fileUrl = $data['attachment'];
                $fileName = basename($fileUrl);
                echo "<a href='$fileUrl' target='_blank'>$fileName</a>";
              } else {
                echo "-";
              }
              ?>
            </td>
            <td>
              <form method="post" onsubmit="return confirm('Supprimer ce feedback ?');" style="margin:0;">
                <input type="hidden" name="delete_file" value="<?= htmlspecialchars($file) ?>">
                <button type="submit" style="background:red; color:white; border:none; padding:4px 8px; cursor:pointer;">Supprimer</button>
              </form>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  <?php endif; ?>

<?php
// Suppression
if (isset($_POST['delete_file'])) {
  $delFile = $_POST['delete_file'];
  if (file_exists($delFile)) {
    unlink($delFile);
    echo "<p style='color:green;'>Feedback supprimé avec succès.</p>";
    // Rafraîchir la page pour mettre à jour la liste
    echo "<script>window.location.href=window.location.href;</script>";
    exit;
  } else {
    echo "<p style='color:red;'>Fichier non trouvé.</p>";
  }
}
?>
<br><br>
<center><button><a href="index.php">Deconnexion</a></button></center>
</div>
</body>
</html>
