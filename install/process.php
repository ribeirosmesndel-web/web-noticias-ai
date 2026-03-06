<?php
session_start();

$action = $_GET['action'] ?? '';

if ($action === 'db') {
    $host = trim($_POST['db_host']);
    $db = trim($_POST['db_name']);
    $user = trim($_POST['db_user']);
    $pass = trim($_POST['db_pass']);

    try {
        $pdo = new PDO("mysql:host=$host", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if DB exists, if not, create it
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$db`");

        // Load SQL File
        $sql = file_get_contents('../database/schema.sql');

        // Execute Schema
        $pdo->exec($sql);

        // Write Config File
        $config_content = "<?php\n"
            . "\$db_host = '$host';\n"
            . "\$db_name = '$db';\n"
            . "\$db_user = '$user';\n"
            . "\$db_pass = '$pass';\n\n"
            . "try {\n"
            . "    \$pdo = new PDO(\"mysql:host=\$db_host;dbname=\$db_name;charset=utf8mb4\", \$db_user, \$db_pass);\n"
            . "    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);\n"
            . "} catch(PDOException \$e) {\n"
            . "    die(\"Erro de conexão: \" . \$e->getMessage());\n"
            . "}\n";

        if (!file_put_contents('../config/db.php', $config_content)) {
            throw new Exception("Não foi possível escrever o arquivo config/db.php. Verifique as permissões (CHMOD 755).");
        }

        // Save for step 2
        $_SESSION['db_setup'] = true;
        header("Location: index.php?step=2");
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = "Erro no banco de dados: " . $e->getMessage();
        header("Location: index.php?step=1");
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: index.php?step=1");
        exit;
    }
}

if ($action === 'admin') {
    if (!isset($_SESSION['db_setup'])) {
        header("Location: index.php?step=1");
        exit;
    }

    require_once '../config/db.php';

    $site_name = trim($_POST['site_name']);
    $email = trim($_POST['admin_email']);
    $username = trim($_POST['admin_user']);
    $password = password_hash($_POST['admin_pass'], PASSWORD_DEFAULT);

    try {
        // Update Site Name
        $stmt_site = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('site_name', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt_site->execute([$site_name, $site_name]);

        // Create Admin User
        $stmt_admin = $pdo->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'admin')");
        $stmt_admin->execute([$username, $password, $email]);

        // Setup complete
        unset($_SESSION['db_setup']);
        header("Location: index.php?step=3");
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = "Erro ao criar admin: " . $e->getMessage();
        header("Location: index.php?step=2");
        exit;
    }
}

header("Location: index.php");
exit;
