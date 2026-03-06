<?php
require_once '../config/core.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['update_zip'])) {

    $file = $_FILES['update_zip'];

    // Validate File Type
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (strtolower($ext) !== 'zip') {
        $error = "Apenas arquivos .zip são permitidos!";
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Erro no upload do arquivo. Código: " . $file['error'];
    } else {
        // Handle Extraction
        $zipPath = $file['tmp_name'];
        $extractPath = ABSPATH; // Extrair na raiz do projeto (cuidado, sobrescreve tudo)

        $zip = new ZipArchive;
        $res = $zip->open($zipPath);

        if ($res === TRUE) {
            // Check if we can write to root
            if (is_writable($extractPath)) {
                $zip->extractTo($extractPath);
                $zip->close();
                $msg = "Atualização aplicada com sucesso! Os arquivos foram sobrescritos.";
            } else {
                $zip->close();
                $error = "Erro de permissão: O servidor não permite gravar arquivos na pasta raiz (" . $extractPath . "). Verifique as permissões (CHMOD) na Hostinger.";
            }
        } else {
            $error = "Não foi possível abrir o arquivo ZIP. Pode estar corrompido.";
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .warning-box {
            background: #fffbeb;
            color: #b45309;
            padding: 1rem;
            border-left: 4px solid #f59e0b;
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .file-upload-box {
            border: 2px dashed var(--admin-border);
            padding: 3rem;
            text-align: center;
            background: #fafafa;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .file-upload-box:hover {
            border-color: var(--admin-accent);
            background: #eff6ff;
        }
    </style>
</head>

<body>

    <aside class="sidebar">
        <div class="sidebar-header">News Admin</div>
        <ul class="nav-menu">
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="articles.php">Artigos</a></li>
            <li><a href="categories.php">Categorias</a></li>
            <li><a href="settings.php">Configurações</a></li>
            <li><a href="n8n_guide.php">Workflow (n8n)</a></li>
            <li><a href="update.php" class="active">Atualizar Sistema</a></li>
        </ul>
        <div class="logout-btn"><a href="index.php?logout=1">Sair do Painel</a></div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <h2>Atualizar Sistema pelo Navegador</h2>
        </header>

        <div class="page-content" style="max-width: 800px;">
            <div class="form-card" style="max-width: 100%;">

                <?php if ($msg): ?>
                    <div class="success-msg">
                        <?= $msg ?>
                    </div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="error-msg">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <div class="warning-box">
                    <strong>⚠️ Cuidado!</strong> Subir um arquivo ZIP aqui fará com que o servidor sobrescreva os
                    arquivos atuais do código fonte. Use apenas para atualizações oficiais do tema ou correções. O seu
                    banco de dados (Notícias e Configurações) <strong>não será afetado</strong>.
                </div>

                <p style="margin-bottom: 2rem;">Se você possui uma nova versão do código fonte (arquivo
                    <code>.zip</code>), selecione-o abaixo para atualizar o site sem precisar de FTP ou GitHub.</p>

                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="file-upload-box">
                        <input type="file" name="update_zip" accept=".zip" required id="zip_file" style="display:none;"
                            onchange="document.getElementById('file-name').innerText = this.files[0].name">
                        <label for="zip_file"
                            style="cursor:pointer; display:flex; flex-direction:column; align-items:center;">
                            <span style="font-size: 3rem; margin-bottom:1rem;">📁</span>
                            <span class="btn-secondary">Selecionar Arquivo .ZIP</span>
                            <span id="file-name"
                                style="margin-top:1rem; font-weight:bold; color:var(--admin-primary);">Nenhum arquivo
                                selecionado...</span>
                        </label>
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem;"
                        onclick="return confirm('Deseja realmente aplicar esta atualização? Seu código atual será sobrescrito.');">Upload
                        e Atualizar Arquivos</button>
                </form>

            </div>
        </div>
    </main>

</body>

</html>