<?php
session_start();

$errors = '';

// Si déjà connecté, rediriger vers la page admin
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_feedback.php');
    exit;
}

if (isset($_POST['login'])) {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    // Identifiants admin à modifier selon ton choix
    $adminUser = 'admin';
    $adminPass = 'password123'; // change ça pour plus de sécurité !

    if ($user === $adminUser && $pass === $adminPass) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin_feedback.php');
        exit;
    } else {
        $errors = "Identifiants incorrects.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Connexion Admin</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .login-container {
            width: 300px; margin: 100px auto; padding: 20px;
            background: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input { width: 100%; padding: 8px; margin: 8px 0; }
        button { width: 100%; padding: 10px; background: #007bff; border: none; color: white; cursor: pointer; }
        .error { color: red; }
    </style>
</head>
<body>
<div class="login-container">
    <h2>Connexion Admin</h2>
    <?php if ($errors): ?>
        <p class="error"><?= htmlspecialchars($errors) ?></p>
    <?php endif; ?>
    <form method="post">
        <input type="text" name="username" placeholder="Nom d'utilisateur" required />
        <input type="password" name="password" placeholder="Mot de passe" required />
        <button type="submit" name="login">Se connecter</button>
    </form>
</div>
</body>
</html>
