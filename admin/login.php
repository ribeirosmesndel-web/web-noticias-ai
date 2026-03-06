<?php
require_once '../config/core.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT id, password, role FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user_id'] = $user['id'];
            $_SESSION['admin_role'] = $user['role'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Usuário ou senha inválidos.";
        }
    } catch (PDOException $e) {
        $error = "Erro no banco de dados. " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Web Notícias</title>
    <!-- Premium Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body class="login-body">
    <div class="login-box">
        <i class='bx bx-news' style="font-size: 3rem; color: var(--admin-accent); margin-bottom: 0.5rem;"></i>
        <h1>Admin Portal</h1>
        <p>Acesse para gerenciar o seu portal de notícias.</p>

        <?php if (isset($error)): ?>
            <div class="error-msg"><i class='bx bx-error-circle'></i> <?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div style="position: relative;">
                <i class='bx bx-user'
                    style="position: absolute; left: 1rem; top: 1.1rem; color: var(--admin-text-light);"></i>
                <input type="text" name="username" placeholder="Usuário" required style="padding-left: 2.5rem;">
            </div>
            <div style="position: relative;">
                <i class='bx bx-lock-alt'
                    style="position: absolute; left: 1rem; top: 1.1rem; color: var(--admin-text-light);"></i>
                <input type="password" name="password" placeholder="Senha" required style="padding-left: 2.5rem;">
            </div>
            <button type="submit"><i class='bx bx-log-in' style="vertical-align: middle; margin-right: 0.5rem;"></i>
                Entrar no Painel</button>
        </form>
    </div>
</body>

</html>