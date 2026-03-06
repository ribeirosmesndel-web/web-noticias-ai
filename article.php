<?php
require_once 'config/core.php';

if (!file_exists('config/db.php')) {
    header("Location: install/index.php");
    exit;
}

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
if (empty($slug)) {
    header("Location: index.php");
    exit;
}

try {
    // Increment View
    $stmt_view = $pdo->prepare("UPDATE articles SET views = views + 1 WHERE slug = ?");
    $stmt_view->execute([$slug]);

    // Fetch Article
    $stmt = $pdo->prepare("SELECT a.*, c.name as category_name, c.slug as category_slug 
                           FROM articles a 
                           LEFT JOIN categories c ON a.category_id = c.id 
                           WHERE a.slug = ? AND a.status = 'published' LIMIT 1");
    $stmt->execute([$slug]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $article = false;
}

if (!$article) {
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 - Artigo não encontrado</h1><a href='index.php'>Voltar para Home</a>";
    exit;
}

// Fetch Comments
try {
    $stmt_comments = $pdo->prepare("SELECT * FROM comments WHERE article_id = ? AND status = 'approved' ORDER BY created_at DESC");
    $stmt_comments->execute([$article['id']]);
    $comments = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $comments = [];
}

// Settings
$site_name = get_setting($pdo, 'site_name', 'THE VANGUARD');
$adsense_header = get_setting($pdo, 'adsense_header', '');

// SEO
$seo_title = !empty($article['seo_title']) ? $article['seo_title'] : $article['title'];
$seo_desc = !empty($article['seo_description']) ? $article['seo_description'] : $article['summary'];
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($seo_title) ?> - <?= htmlspecialchars($site_name) ?></title>
    <meta name="description" content="<?= htmlspecialchars($seo_desc) ?>">

    <link rel="stylesheet" href="assets/css/style.css">

    <?= $adsense_header ?>
</head>

<body>

    <header class="site-header">
        <div class="container">
            <div class="header-top">
                <a href="index.php" class="logo"><?= htmlspecialchars($site_name) ?></a>
                <div class="nav-actions">
                    <button class="theme-toggle" id="themeToggle" aria-label="Alternar Tema">🌓</button>
                    <a href="index.php" style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase;">Back to
                        home</a>
                </div>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="article-layout">

            <article class="main-content">
                <header class="article-header">
                    <span class="category-tag">
                        <?= htmlspecialchars($article['category_name']) ?>
                    </span>
                    <h1 class="article-title">
                        <?= htmlspecialchars($article['title']) ?>
                    </h1>
                    <div class="article-meta">
                        <span>Updated <?= date('M d, Y', strtotime($article['updated_at'])) ?></span>
                        <span><?= (int) $article['views'] ?> views</span>
                        <?php if ($article['ai_generated']): ?>
                            <span style="color: var(--accent);">✨ Intelligence Analysis</span>
                        <?php endif; ?>
                    </div>
                </header>

                <div class="article-image">
                    <img src="<?= htmlspecialchars($article['image_url'] ?? 'https://via.placeholder.com/1200x800?text=Premium+News') ?>"
                        alt="<?= htmlspecialchars($article['title']) ?>">
                </div>

                <div class="article-content premium-serif">
                    <?= nl2br($article['content']) ?>
                </div>

                <!-- Simple Comments Footer -->
                <section style="margin-top: 6rem; padding-top: 4rem; border-top: 1px solid var(--border);">
                    <h2 class="premium-serif" style="font-size: 2rem; margin-bottom: 2rem;">Feedback</h2>
                    <div style="color: var(--muted); font-style: italic;">
                        This conversation is monitored by our editorial intelligence.
                    </div>
                </section>
            </article>

        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($site_name) ?>. All Rights Reserved.</p>
        </div>
    </footer>

    <script>
        const btn = document.getElementById('themeToggle');
        const html = document.documentElement;
        html.setAttribute('data-theme', localStorage.getItem('theme') || 'light');
        btn.addEventListener('click', () => {
            const current = html.getAttribute('data-theme');
            const target = current === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', target);
            localStorage.setItem('theme', target);
        });
    </script>
</body>

</html>