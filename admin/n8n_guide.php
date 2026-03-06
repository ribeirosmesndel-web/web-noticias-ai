<?php
require_once '../config/core.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$webhook_url = "http" . (isset($_SERVER['HTTPS']) ? "s" : "") . "://" . $_SERVER['HTTP_HOST'] . str_replace('/admin/n8n_guide.php', '/api/webhook_n8n.php', $_SERVER['REQUEST_URI']);

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Integração n8n - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .code-block {
            background: #111827;
            color: #a7f3d0;
            padding: 1.5rem;
            border-radius: 0.5rem;
            font-family: monospace;
            overflow-x: auto;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }

        .step-list {
            list-style: decimal;
            padding-left: 1.5rem;
            margin-bottom: 2rem;
        }

        .step-list li {
            margin-bottom: 1rem;
            color: #4b5563;
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
            <li><a href="n8n_guide.php" class="active">Workflow (n8n)</a></li>
        </ul>
        <div class="logout-btn"><a href="index.php?logout=1">Sair do Painel</a></div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <h2>Guia de Integração com Inteligência Artificial (n8n)</h2>
        </header>

        <div class="page-content" style="max-width: 900px;">
            <div class="form-card" style="max-width:100%;">

                <h3 style="margin-top:0;">Como automatizar notícias usando n8n</h3>
                <p>Seu sistema está perfeitamente preparado para receber notícias reescritas por Inteligência Artificial
                    (ChatGPT ou Gemini) de forma totalmente automatizada através de um Webhook.</p>

                <h4>1. Importe o Workflow para o seu n8n</h4>
                <p>Na raiz do seu projeto, existe uma pasta chamada <code>/n8n-workflows/</code> com um arquivo
                    <code>automated_news_workflow.json</code>. Importe esse arquivo no seu n8n.</p>

                <h4>2. Configure o Webhook do seu portal no HTTP Request</h4>
                <p>No último nó (node) do workflow do n8n (HTTP Request), você deve configurar a seguinte URL:</p>
                <div class="code-block">
                    <?= htmlspecialchars($webhook_url) ?>
                </div>

                <p><strong>Autenticação (Headers):</strong></p>
                <div class="code-block">
                    Key: Authorization<br>
                    Value: Bearer Mv3@AutomatedNews!2026
                </div>
                <small style="color:red; display:block; margin-top:-1rem; margin-bottom:2rem;">(Você pode alterar esse
                    token editando a linha 13 do arquivo api/webhook_n8n.php)</small>

                <h4>3. Formato JSON Esperado pelo Site</h4>
                <p>O nó de IA no seu n8n deve gerar, e o HTTP Request enviar (via POST), um payload (Body) exatamente
                    neste formato JSON:</p>

                <div class="code-block">
                    {
                    "title": "A Apple acaba de anunciar os novos chips M4...",
                    "category_slug": "tecnologia",
                    "summary": "Resumo de dois parágrafos da notícia para aparecer no feed inicial.",
                    "content": "&lt;p&gt;Texto completo detalhado com tags HTML...&lt;/p&gt;",
                    "image_url": "https://url-da-imagem-da-noticia.jpg",
                    "seo_title": "Lançamento do Apple M4: Preços e Especificações",
                    "seo_description": "Tudo sobre o novo chipset Apple Silicon M4.",
                    "seo_tags": "apple, m4, macbook, tecnologia"
                    }
                </div>

                <h4>Fluxo de funcionamento</h4>
                <ol class="step-list">
                    <li>O n8n desperta sozinho a cada 30 minutos (Cron node).</li>
                    <li>Ele consome feeds RSS das principais mídias de tecnologia/mundo.</li>
                    <li>Ele solicita ao GPT/Gemini que leia essas notícias, as reescreva em linguagem jornalística
                        própria, extraia imagens e crie um título chamativo.</li>
                    <li>A IA devolve os dados estruturados no JSON acima.</li>
                    <li>O n8n envia os dados para este portal.</li>
                    <li>Assim que recebemos, a notícia entra no ar automaticamente via script seguro.</li>
                </ol>

            </div>
        </div>
    </main>

</body>

</html>