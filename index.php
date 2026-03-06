<?php
require_once 'config/core.php';

// If DB isn't setup, redirect to install
if (!file_exists('config/db.php')) {
    header("Location: install/index.php");
    exit;
}

// Fetch Settings
$site_name = get_setting($pdo, 'site_name', 'THE VANGUARD');
$site_desc = get_setting($pdo, 'site_description', 'Últimas notícias');
$adsense_header = get_setting($pdo, 'adsense_header', '');
$adsense_mid = get_setting($pdo, 'adsense_article_mid', '');

// Fetch Featured Articles (Last 3)
try {
    $stmt = $pdo->query("SELECT a.*, c.name as category_name, c.slug as category_slug 
                         FROM articles a 
                         LEFT JOIN categories c ON a.category_id = c.id 
                         WHERE a.status = 'published' 
                         ORDER BY a.created_at DESC LIMIT 3");
    $featured = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $featured = [];
}

// Fetch Latest News
try {
    $stmt_latest = $pdo->query("SELECT a.*, c.name as category_name, c.slug as category_slug 
                                FROM articles a 
                                LEFT JOIN categories c ON a.category_id = c.id 
                                WHERE a.status = 'published' 
                                ORDER BY a.created_at DESC LIMIT 3, 12");
    $latest = $stmt_latest->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $latest = [];
}

// Fetch Categories for Menu
try {
    $stmt_cats = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $stmt_cats->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}

?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($site_name) ?> - <?= htmlspecialchars($site_desc) ?></title>
    <meta name="description" content="<?= htmlspecialchars($site_desc) ?>">

    <link rel="stylesheet" href="assets/css/style.css">

    <!-- AdSense Header -->
    <?= $adsense_header ?>
</head>

<body>

    <header class="site-header">
        <div class="container">
            <div class="header-top">
                <a href="index.php" class="logo"><?= htmlspecialchars($site_name) ?></a>
                <div class="nav-actions">
                    <form action="search.php" method="GET" class="header-search">
                        <input type="text" name="q" placeholder="Search..." required>
                        <button type="submit">🔍</button>
                    </form>
                    <button class="theme-toggle" id="themeToggle" aria-label="Alternar Tema">🌓</button>
                </div>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php" class="active">Home</a></li>
                    <?php foreach ($categories as $cat): ?>
                        <li><a href="category.php?slug=<?= htmlspecialchars($cat['slug']) ?>">
                                <?= htmlspecialchars($cat['name']) ?>
                            </a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <!-- Featured Section -->
        <?php if (!empty($featured)): ?>
            <section class="hero-section">
                <div class="grid-featured">

                    <!-- Main Feature -->
                    <?php $main = $featured[0]; ?>
                    <article class="card-main">
                        <a href="article.php?slug=<?= urlencode($main['slug']) ?>">
                            <div class="img-container">
                                <img src="<?= htmlspecialchars($main['image_url'] ?? 'https://via.placeholder.com/1200x800?text=Premium+News') ?>"
                                    alt="<?= htmlspecialchars($main['title']) ?>" loading="lazy">
                            </div>
                            <div class="content">
                                <span class="category-tag">
                                    <?= htmlspecialchars($main['category_name'] ?? 'Notícia') ?>
                                </span>
                                <h1 class="title">
                                    <?= htmlspecialchars($main['title']) ?>
                                </h1>
                                <p class="premium-serif" style="font-size: 1.2rem; color: var(--muted); opacity: 0.8;">
                                    <?= htmlspecialchars($main['summary']) ?>
                                </p>
                            </div>
                        </a>
                    </article>

                    <!-- 2 Small Features -->
                    <div class="card-secondary-container">
                        <?php for ($i = 1; $i <= 2; $i++):
                            if (isset($featured[$i])):
                                $sm = $featured[$i]; ?>
                                <article class="card-sm">
                                    <a href="article.php?slug=<?= urlencode($sm['slug']) ?>">
                                        <div class="img-wrap">
                                            <img src="<?= htmlspecialchars($sm['image_url'] ?? 'https://via.placeholder.com/600x400?text=Premium+News') ?>"
                                                alt="<?= htmlspecialchars($sm['title']) ?>" loading="lazy">
                                        </div>
                                        <div class="content-sm">
                                            <span class="category-tag">
                                                <?= htmlspecialchars($sm['category_name'] ?? 'Notícia') ?>
                                            </span>
                                            <h3 class="title">
                                                <?= htmlspecialchars($sm['title']) ?>
                                            </h3>
                                        </div>
                                    </a>
                                </article>
                            <?php endif; endfor; ?>
                    </div>

                </div>
            </section>
        <?php else: ?>
            <div style="padding: 10rem 0; text-align:center;">
                <h1 class="premium-serif">Welcome to <?= htmlspecialchars($site_name) ?></h1>
                <p style="color: var(--muted);">Establishing frequencies...</p>
            </div>
        <?php endif; ?>

        <!-- Latest News -->
        <?php if (!empty($latest)): ?>
            <section class="latest-section">
                <h2 class="section-title">Latest Updates</h2>
                <div class="news-list">
                    <?php foreach ($latest as $news): ?>
                        <article class="news-card">
                            <a href="article.php?slug=<?= urlencode($news['slug']) ?>">
                                <div class="img-wrap">
                                    <img src="<?= htmlspecialchars($news['image_url'] ?? 'https://via.placeholder.com/600x400?text=Premium+News') ?>"
                                        alt="<?= htmlspecialchars($news['title']) ?>" loading="lazy">
                                </div>
                                <span class="category-tag">
                                    <?= htmlspecialchars($news['category_name'] ?? 'Notícia') ?>
                                </span>
                                <h3 class="title">
                                    <?= htmlspecialchars($news['title']) ?>
                                </h3>
                                <p class="summary">
                                    <?= htmlspecialchars($news['summary']) ?>
                                </p>
                                <div class="meta">
                                    <?= date('M d, Y', strtotime($news['created_at'])) ?>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

    </main>

    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($site_name) ?>. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Theme Script -->
    <script>
        const btn = document.getElementById('themeToggle');
        const html = document.documentElement;

        const savedTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-theme', savedTheme);

        btn.addEventListener('click', () => {
            const current = html.getAttribute('data-theme');
            const target = current === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', target);
            localStorage.setItem('theme', target);
        });
    </script>
</body>

</html>