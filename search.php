<?php
require_once 'config/core.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$articles = [];
if (!empty($q)) {
    try {
        $stmt = $pdo->prepare("SELECT a.*, c.name as category_name, c.slug as category_slug 
                               FROM articles a 
                               LEFT JOIN categories c ON a.category_id = c.id 
                               WHERE (a.title LIKE ? OR a.content LIKE ?) AND a.status = 'published' 
                               ORDER BY a.created_at DESC LIMIT 50");
        $like = "%$q%";
        $stmt->execute([$like, $like]);
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $articles = [];
    }
}

$site_name = get_setting($pdo, 'site_name', 'Portal');
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Busca:
        <?= htmlspecialchars($q) ?> -
        <?= htmlspecialchars($site_name) ?>
    </title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <header class="site-header">
        <div class="container">
            <div class="header-top">
                <a href="index.php" class="logo">
                    <?= htmlspecialchars($site_name) ?>
                </a>
                <div class="nav-actions">
                    <button class="theme-toggle" id="themeToggle" aria-label="Alternar Tema">🌓</button>
                    <a href="index.php"
                        style="font-weight: bold; padding: 0.25rem 0.5rem; background: var(--border-color); border-radius: 4px;">Voltar</a>
                </div>
            </div>
        </div>
    </header>

    <main class="container" style="padding: 4rem 0; min-height: 60vh;">

        <h1 style="margin-bottom: 2rem;">Resultados para: "
            <?= htmlspecialchars($q) ?>"
        </h1>

        <?php if (empty($q)): ?>
            <p style="color: var(--text-muted);">Digite algo para buscar.</p>
        <?php elseif (empty($articles)): ?>
            <p style="color: var(--text-muted);">Nenhuma notícia encontrada para "
                <?= htmlspecialchars($q) ?>".
            </p>
        <?php else: ?>
            <div class="news-list">
                <?php foreach ($articles as $news): ?>
                    <article class="news-card">
                        <a href="article.php?slug=<?= urlencode($news['slug']) ?>" class="img-wrap">
                            <span class="category-tag" style="position:absolute; top:10px; left:10px; z-index:10;">
                                <?= htmlspecialchars($news['category_name']) ?>
                            </span>
                            <img src="<?= htmlspecialchars($news['image_url'] ?? 'https://via.placeholder.com/400x250?text=Sem+Imagem') ?>"
                                alt="<?= htmlspecialchars($news['title']) ?>" loading="lazy">
                        </a>
                        <div>
                            <div class="meta"><span>
                                    <?= date('d/m/Y', strtotime($news['created_at'])) ?>
                                </span></div>
                            <a href="article.php?slug=<?= urlencode($news['slug']) ?>">
                                <h3 class="title">
                                    <?= htmlspecialchars($news['title']) ?>
                                </h3>
                            </a>
                            <p class="summary">
                                <?= htmlspecialchars($news['summary']) ?>
                            </p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>

    <footer class="site-footer">
        <div class="container">
            <p>&copy;
                <?= date('Y') ?>
                <?= htmlspecialchars($site_name) ?>. Todos os direitos reservados.
            </p>
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