<?php
require_once '../config/core.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Stats
$total_articles = $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
$total_views = $pdo->query("SELECT SUM(views) FROM articles")->fetchColumn();
$total_comments = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();

// Recent AI Articles
$recent_ai = $pdo->query("SELECT title, created_at, views FROM articles WHERE ai_generated = 1 ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Administração</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            News Admin
        </div>
        <ul class="nav-menu">
            <li><a href="index.php" class="active">Dashboard</a></li>
            <li><a href="articles.php">Artigos</a></li>
            <li><a href="categories.php">Categorias</a></li>
            <li><a href="settings.php">Configurações</a></li>
            <li><a href="n8n_guide.php">Workflow (n8n)</a></li>
            <li><a href="update.php">Atualizar Sistema</a></li>
        </ul>
        <div class="logout-btn">
            <a href="?logout=1">Sair do Painel</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <h2>Dashboard</h2>
            <div>
                <a href="../" target="_blank" class="btn-secondary">Ver Site</a>
            </div>
        </header>

        <div class="page-content">

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total de Artigos</div>
                    <div class="stat-value">
                        <?= number_format($total_articles) ?>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Visualizações Totais</div>
                    <div class="stat-value">
                        <?= number_format((int) $total_views) ?>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Comentários</div>
                    <div class="stat-value">
                        <?= number_format($total_comments) ?>
                    </div>
                </div>
            </div>

            <div style="margin-top: 3rem;">
                <h3 style="margin-bottom: 1rem;">Últimos Artigos Gerados por IA</h3>
                <?php if ($recent_ai): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Data</th>
                                <th>Visualizações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_ai as $a): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($a['title']) ?>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y H:i', strtotime($a['created_at'])) ?>
                                    </td>
                                    <td>
                                        <?= number_format($a['views']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div
                        style="background: white; padding: 2rem; border-radius: 8px; border: 1px solid #e5e7eb; text-align:center; color: #6b7280;">
                        Nenhum artigo importado do n8n ainda.</div>
                <?php endif; ?>
            </div>

        </div>
    </main>

</body>

</html>