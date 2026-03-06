<?php
require_once '../config/core.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$webhook_url = "http" . (isset($_SERVER['HTTPS']) ? "s" : "") . "://" . $_SERVER['HTTP_HOST'] . str_replace('/admin/n8n_guide.php', '/api/webhook_n8n.php', $_SERVER['REQUEST_URI']);

// Read workflow JSON content
$workflow_path = ABSPATH . '/n8n-workflows/automated_news_workflow.json';
$workflow_content = '{ "erro": "Arquivo não encontrado." }';
if (file_exists($workflow_path)) {
    $workflow_content = file_get_contents($workflow_path);
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Integração n8n - Admin</title>
    <!-- Premium Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .code-block {
            background: var(--admin-sidebar-bg);
            color: #a7f3d0;
            padding: 1.5rem;
            border-radius: var(--radius-md);
            font-family: 'Courier New', Courier, monospace;
            overflow-x: auto;
            font-size: 0.9rem;
            margin-bottom: 2rem;
            border: 1px solid var(--admin-sidebar-hover);
        }

        .step-list {
            list-style: decimal;
            padding-left: 1.5rem;
            margin-bottom: 2rem;
            color: var(--admin-text);
            line-height: 1.6;
        }

        .step-list li {
            margin-bottom: 0.75rem;
        }
        
        .step-title {
            color: var(--admin-primary);
            font-weight: 700;
            font-size: 1.1rem;
            margin-top: 2rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
    </style>
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
            <li><a href="n8n_guide.php" class="active"><i class='bx bx-bot'></i> Workflow (n8n)</a></li>
            <li><a href="update.php"><i class='bx bx-cloud-download'></i> Atualizar Sistema</a></li>
        </ul>
        <div class="logout-btn">
            <a href="index.php?logout=1"><i class='bx bx-log-out'></i> Sair do Painel</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <h2>Workflow Automation (n8n)</h2>
        </header>

        <div class="page-content" style="max-width: 900px;">
            <div class="form-card" style="max-width:100%;">

                <div class="flex-between" style="border-bottom: 1px solid var(--admin-border); padding-bottom: 1.5rem; margin-bottom: 2rem;">
                    <div>
                        <h3 style="color: var(--admin-primary); margin-bottom: 0.5rem; font-size: 1.5rem;"><i class='bx bxl-nodejs' style="color: #ea580c;"></i> Como automatizar pelo n8n</h3>
                        <p class="stat-label" style="text-transform: none; font-size: 0.95rem;">Configure seu sistema para receber matérias reescritas por IA 100% no automático.</p>
                    </div>
                </div>

                <div class="step-title"><i class='bx bx-import'></i> 1. Copie e Importe o Workflow para o seu n8n</div>
                <p style="margin-bottom: 1rem; color: var(--admin-text-light);">No seu painel do n8n, crie um novo workflow vazio, clique na tela, pressione <strong>Ctrl+V</strong> (ou Cmd+V) e cole o código abaixo. Ele montará toda a estrutura automaticamente.</p>
                
                <div style="position: relative;">
                    <button onclick="copyWorkflow()" id="copyBtn" class="btn-primary" style="position: absolute; top: 10px; right: 10px; padding: 0.4rem 0.8rem; font-size: 0.8rem; z-index: 2;">
                        <i class='bx bx-copy'></i> Copiar Workflow
                    </button>
                    <textarea id="workflowData" style="display:none;"><?= htmlspecialchars($workflow_content) ?></textarea>
                    <div class="code-block" style="max-height: 250px; overflow-y: auto;">
<?= htmlspecialchars(substr($workflow_content, 0, 500)) ?>...
/* (Código gigante oculto aqui. Clique no botão de copiar!) */
                    </div>
                </div>

                <div class="step-title"><i class='bx bx-link'></i> 2. Configure o Webhook do seu portal</div>
                <p style="margin-bottom: 1rem; color: var(--admin-text-light);">No último nó do workflow do n8n (o bloco <code>HTTP Request</code> final), você deve configurar a URL exata do seu portal:</p>
                <div class="code-block" style="padding: 1rem;">
                    <i class='bx bx-globe'></i> <?= htmlspecialchars($webhook_url) ?>
                </div>

                <p style="margin-bottom: 0.5rem;"><strong>Autenticação (Headers):</strong> Para segurança, o endpoint exige um Token.</p>
                <div class="code-block" style="padding: 1rem;">
                    <span style="color: #fbbf24;">Key:</span> Authorization<br>
                    <span style="color: #fbbf24;">Value:</span> Bearer Mv3@AutomatedNews!2026
                </div>
                <p style="font-size: 0.85rem; color: var(--admin-text-light); margin-top: -1rem; margin-bottom: 2rem;"><i class='bx bx-info-circle'></i> Você pode alterar esse token de segurança editando a linha 13 do arquivo <code>api/webhook_n8n.php</code>.</p>

                <div class="step-title"><i class='bx bx-data'></i> 3. Formato JSON Esperado</div>
                <p style="margin-bottom: 1rem; color: var(--admin-text-light);">O nó de IA no seu n8n deve gerar, e o HTTP Request enviar (via POST), um payload exatamente neste formato:</p>
                <div class="code-block">
{
  "title": "A Apple acaba de anunciar os novos chips M4...",
  "category_slug": "tecnologia",
  "summary": "Resumo de dois parágrafos da notícia para aparecer no feed.",
  "content": "&lt;p&gt;Texto completo detalhado com tags HTML...&lt;/p&gt;",
  "image_url": "https://url-da-imagem-da-noticia.jpg",
  "seo_title": "Lançamento do Apple M4",
  "seo_description": "Tudo sobre o novo chipset M4.",
  "seo_tags": "apple, m4, macbook, tecnologia"
}
                </div>

            </div>
        </div>
    </main>

    <script>
        function copyWorkflow() {
            var copyText = document.getElementById("workflowData").value;
            navigator.clipboard.writeText(copyText).then(function() {
                var copyBtn = document.getElementById("copyBtn");
                copyBtn.innerHTML = "<i class='bx bx-check'></i> Copiado!";
                copyBtn.style.background = "#10b981";
                setTimeout(function() {
                    copyBtn.innerHTML = "<i class='bx bx-copy'></i> Copiar Workflow";
                    copyBtn.style.background = "var(--admin-accent)";
                }, 3000);
            }, function(err) {
                alert('Falha ao copiar workflow: ' + err);
            });
        }
    </script>
</body>
</html>