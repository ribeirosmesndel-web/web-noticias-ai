<?php
require_once '../config/core.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$msg = '';
$error = '';
$git_output = '';

// Handle Git Pull Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'git_pull') {
    // Escapar para diretório raiz e rodar git pull
    $cmd = 'cd ' . escapeshellarg(ABSPATH) . ' && git pull origin main --rebase 2>&1';

    // shell_exec executa o comando e retorna a saída completa
    $output = shell_exec($cmd);

    if ($output === null) {
        $error = "Erro ao executar o comando Git. Função shell_exec pode estar desativada no servidor.";
    } else {
        $git_output = htmlspecialchars($output);
        if (strpos($output, 'Already up to date.') !== false) {
            $msg = "Seu site já está na versão mais recente.";
        } else if (strpos($output, 'error:') !== false || strpos($output, 'fatal:') !== false || strpos($output, 'CONFLICT') !== false) {
            $error = "Aviso retornado pelo Git (verifique o console).";
        } else {
            $msg = "Arquivos atualizados com sucesso via GitHub!";
        }
    }
}

// Handle Manual ZIP Upload (Legacy Tool)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['update_zip'])) {
    $file = $_FILES['update_zip'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (strtolower($ext) !== 'zip') {
        $error = "Apenas arquivos .zip são permitidos!";
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Erro no upload do arquivo. Código: " . $file['error'];
    } else {
        $zipPath = $file['tmp_name'];
        $extractPath = ABSPATH;
        $zip = new ZipArchive;
        $res = $zip->open($zipPath);
        if ($res === TRUE) {
            if (is_writable($extractPath)) {
                $zip->extractTo($extractPath);
                $zip->close();
                $msg = "Atualização manual (ZIP) aplicada com sucesso!";
            } else {
                $zip->close();
                $error = "Erro de permissão: Servidor não permite gravar na raiz. Chmod necessário.";
            }
        } else {
            $error = "Não foi possível abrir o ZIP.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar Sistema - Admin</title>
    <!-- Premium Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <i class='bx bx-news'></i> News Admin
        </div>
        <ul class="nav-menu">
            <li><a href="index.php"><i class='bx bx-grid-alt'></i> Dashboard</a></li>
            <li><a href="articles.php"><i class='bx bx-file'></i> Artigos</a></li>
            <li><a href="categories.php"><i class='bx bx-folder'></i> Categorias</a></li>
            <li><a href="settings.php"><i class='bx bx-cog'></i> Configurações</a></li>
            <li><a href="n8n_guide.php"><i class='bx bx-bot'></i> Workflow (n8n)</a></li>
            <li><a href="update.php" class="active"><i class='bx bx-cloud-download'></i> Atualizar Sistema</a></li>
        </ul>
        <div class="logout-btn">
            <a href="index.php?logout=1"><i class='bx bx-log-out'></i> Sair do Painel</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <h2>Atualizações e Sincronização</h2>
        </header>

        <div class="page-content" style="max-width: 900px;">
            <div class="form-card" style="max-width: 100%;">

                <?php if ($msg): ?>
                    <div class="success-msg"><i class='bx bx-check-circle'></i> <?= $msg ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="error-msg"><i class='bx bx-error-circle'></i> <?= $error ?></div>
                <?php endif; ?>

                <div class="flex-between"
                    style="border-bottom: 1px solid var(--admin-border); padding-bottom: 2rem; margin-bottom: 2rem;">
                    <div>
                        <h3
                            style="color: var(--admin-primary); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class='bx bxl-github' style="font-size: 1.5rem;"></i> Sincronização via GitHub</h3>
                        <p class="stat-label" style="text-transform: none; font-size: 0.95rem;">Clique no botão abaixo
                            para puxar as últimas alterações do repositório remoto para este servidor via
                            <code>git pull</code>.</p>
                    </div>

                    <form method="POST" action="">
                        <input type="hidden" name="action" value="git_pull">
                        <button type="submit" class="btn-primary"
                            onclick="return confirm('Iniciar sincronização pelo GitHub?');">
                            <i class='bx bx-sync'></i> Puxar do GitHub (Git Pull)
                        </button>
                    </form>
                </div>

                <?php if ($git_output): ?>
                    <div style="margin-bottom: 3rem;">
                        <h4 style="color: var(--admin-text-light); margin-bottom: 0.5rem;"><i class='bx bx-terminal'></i>
                            Terminal Output:</h4>
                        <div class="terminal-box"><?= $git_output ?></div>
                    </div>
                <?php endif; ?>

                <div style="margin-top: 3rem;">
                    <h3 style="color: var(--admin-primary); margin-bottom: 0.5rem;"><i class='bx bx-archive-in'></i>
                        Instalação Manual (Upload ZIP)</h3>
                    <p class="stat-label" style="text-transform: none; font-size: 0.95rem; margin-bottom: 1.5rem;">Use
                        apenas como método alternativo caso o GitHub esteja indisponível.</p>

                    <form method="POST" action="" enctype="multipart/form-data">
                        <div style="border: 2px dashed var(--admin-border); padding: 3rem; text-align: center; border-radius: var(--radius-md); background: #fafafa; margin-bottom: 1.5rem; transition: var(--transition);"
                            onmouseover="this.style.borderColor='var(--admin-accent)'; this.style.background='#eff6ff';"
                            onmouseout="this.style.borderColor='var(--admin-border)'; this.style.background='#fafafa';">
                            <input type="file" name="update_zip" accept=".zip" required id="zip_file"
                                style="display:none;"
                                onchange="document.getElementById('file-name').innerText = this.files[0].name">
                            <label for="zip_file"
                                style="cursor:pointer; display:flex; flex-direction:column; align-items:center;">
                                <i class='bx bx-cloud-upload'
                                    style="font-size: 4rem; color: var(--admin-accent); margin-bottom:1rem;"></i>
                                <span class="btn-secondary">Selecionar Arquivo .ZIP</span>
                                <span id="file-name"
                                    style="margin-top:1rem; font-weight:600; color:var(--admin-primary);">Nenhum arquivo
                                    selecionado...</span>
                            </label>
                        </div>
                        <button type="submit" class="btn-secondary"
                            style="width: 100%; justify-content: center; padding: 1rem;"
                            onclick="return confirm('Sobrescrever arquivos atuais?');"><i class='bx bx-upload'></i>
                            Enviar ZIP</button>
                    </form>
                </div>

            </div>
        </div>
    </main>

</body>

</html>