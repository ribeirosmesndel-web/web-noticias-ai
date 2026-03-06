<?php
require_once '../config/core.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'site_name' => trim($_POST['site_name']),
        'site_description' => trim($_POST['site_description']),
        'analytics_id' => trim($_POST['analytics_id']),
        'adsense_header' => trim($_POST['adsense_header']),
        'adsense_article_mid' => trim($_POST['adsense_article_mid']),
        'adsense_sidebar' => trim($_POST['adsense_sidebar'])
    ];

    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");

    foreach ($settings as $key => $value) {
        $stmt->execute([$key, $value, $value]);
    }

    header("Location: settings.php?msg=saved");
    exit;
}

// Fetch current
$stmt = $pdo->query("SELECT * FROM settings");
$current = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $current[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - Admin</title>
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
            <li><a href="settings.php" class="active"><i class='bx bx-cog'></i> Configurações</a></li>
            <li><a href="n8n_guide.php"><i class='bx bx-bot'></i> Workflow (n8n)</a></li>
            <li><a href="update.php"><i class='bx bx-cloud-download'></i> Atualizar Sistema</a></li>
        </ul>
        <div class="logout-btn">
            <a href="index.php?logout=1"><i class='bx bx-log-out'></i> Sair do Painel</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <h2>Configurações Globais</h2>
        </header>

        <div class="page-content" style="max-width: 900px;">

            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'saved'): ?>
                <div class="success-msg"><i class='bx bx-check-circle'></i> Configurações salvas com sucesso!</div>
            <?php endif; ?>

            <form method="POST" action="" class="form-card" style="max-width: 100%;">

                <h3
                    style="margin-top: 0; margin-bottom: 2rem; color: var(--admin-primary); display: flex; align-items: center; gap: 0.5rem;">
                    <i class='bx bx-info-circle'></i> Informações Básicas</h3>

                <div class="form-group">
                    <label>Nome do Site</label>
                    <input type="text" name="site_name" class="form-control"
                        value="<?= htmlspecialchars($current['site_name'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label>Descrição do Site (SEO Meta)</label>
                    <textarea name="site_description" class="form-control"
                        rows="3"><?= htmlspecialchars($current['site_description'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label>Google Analytics ID (Ex: G-XXXXXXXXXX)</label>
                    <input type="text" name="analytics_id" class="form-control"
                        value="<?= htmlspecialchars($current['analytics_id'] ?? '') ?>">
                </div>

                <h3
                    style="margin-top: 3rem; margin-bottom: 2rem; color: var(--admin-primary); display: flex; align-items: center; gap: 0.5rem;">
                    <i class='bx bx-dollar-circle'></i> Monetização (Google AdSense)</h3>

                <div class="form-group">
                    <label>AdSense Header Script (Inserido no &lt;head&gt;)</label>
                    <textarea name="adsense_header" class="form-control" rows="4"
                        placeholder="<script data-ad-client=..."></textarea>
                    <?php if (!empty($current['adsense_header'])): ?>
                        <small style="color: var(--admin-text-light); margin-top: 0.5rem; display: block;">
                            <i class='bx bx-check'></i> Atual:
                            <?= htmlspecialchars(substr($current['adsense_header'], 0, 50)) ?>...
                        </small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>AdSlot: Meio do Artigo / Home</label>
                    <textarea name="adsense_article_mid" class="form-control" rows="4"
                        placeholder="<ins class='adsbygoogle'..."></textarea>
                </div>

                <div class="form-group">
                    <label>AdSlot: Sidebar</label>
                    <textarea name="adsense_sidebar" class="form-control" rows="4"
                        placeholder="<ins class='adsbygoogle'..."></textarea>
                </div>

                <div style="margin-top: 3rem; text-align: right;">
                    <button type="submit" class="btn-primary" style="padding: 1rem 2rem;"><i class='bx bx-save'></i>
                        Salvar Configurações</button>
                </div>
            </form>

        </div>
    </main>

</body>

</html>