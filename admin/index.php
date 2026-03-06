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
    <title>Painel Admin - Dashboard</title>
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
            <li><a href="index.php" class="active"><i class='bx bx-grid-alt'></i> Dashboard</a></li>
            <li><a href="articles.php"><i class='bx bx-file'></i> Artigos</a></li>
            <li><a href="categories.php"><i class='bx bx-folder'></i> Categorias</a></li>
            <li><a href="settings.php"><i class='bx bx-cog'></i> Configurações</a></li>
            <li><a href="n8n_guide.php"><i class='bx bx-bot'></i> Workflow (n8n)</a></li>
            <li><a href="update.php"><i class='bx bx-cloud-download'></i> Atualizar Sistema</a></li>
        </ul>
        <div class="logout-btn">
            <a href="?logout=1"><i class='bx bx-log-out'></i> Sair do Painel</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <h2>Dashboard Overview</h2>
            <div>
                <a href="../" target="_blank" class="btn-primary"><i class='bx bx-link-external'></i> Ver Site Ao
                    Vivo</a>
            </div>
        </header>

        <div class="page-content">

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <span class="stat-label">Total de Artigos</span>
                        <span class="stat-value"><?= number_format($total_articles) ?></span>
                    </div>
                    <div class="stat-icon"><i class='bx bx-file-blank'></i></div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <span class="stat-label">Visualizações Totais</span>
                        <span class="stat-value"><?= number_format((int) $total_views) ?></span>
                    </div>
                    <div class="stat-icon" style="color: #10b981; background: rgba(16, 185, 129, 0.1);"><i
                            class='bx bx-bar-chart'></i></div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <span class="stat-label">Comentários</span>
                        <span class="stat-value"><?= number_format($total_comments) ?></span>
                    </div>
                    <div class="stat-icon" style="color: #f59e0b; background: rgba(245, 158, 11, 0.1);"><i
                            class='bx bx-message-rounded-dots'></i></div>
                </div>
            </div>

            <div style="margin-top: 3rem;">
                <div class="flex-between" style="margin-bottom: 1.5rem;">
                    <h3><i class='bx bx-bot'
                            style="color: var(--admin-accent); vertical-align: middle; margin-right: 0.5rem; font-size: 1.5rem;"></i>
                        Últimos Artigos Gerados por IA</h3>
                </div>

                <?php if ($recent_ai): ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Data de Geração</th>
                                    <th>Status de Views</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_ai as $a): ?>
                                    <tr>
                                        <td style="font-weight: 500; color: var(--admin-primary);">
                                            <?= htmlspecialchars($a['title']) ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-ai"><i class='bx bx-time'></i>
                                                <?= date('d M Y, H:i', strtotime($a['created_at'])) ?></span>
                                        </td>
                                        <td>
                                            <i class='bx bx-show' style="color: var(--admin-text-light);"></i>
                                            <?= number_format($a['views']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div
                        style="background: white; padding: 4rem 2rem; border-radius: var(--radius-lg); border: 1px dashed var(--admin-border); text-align:center;">
                        <i class='bx bx-sleepy'
                            style="font-size: 3rem; color: var(--admin-text-light); margin-bottom: 1rem;"></i>
                        <h4 style="color: var(--admin-text-light); font-weight: 500;">Nenhum artigo importado do n8n ainda.
                        </h4>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>

</body>

</html>