<?php
session_start();
$_SESSION['tutoriel_seen'] = true;
header('Location: index.php');
exit;
?>
