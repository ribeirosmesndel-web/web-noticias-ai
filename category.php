<?php
require_once 'config/core.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
if (empty($slug)) {
    header("Location: index.php");
    exit;
}

// Fetch Category
try {
    $stmt_cat = $pdo->prepare("SELECT * FROM categories WHERE slug = ? LIMIT 1");
    $stmt_cat->execute([$slug]);
    $category = $stmt_cat->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $category = false;
}

if (!$category) {
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 - Categoria não encontrada</h1><a href='index.php'>Voltar</a>";
    exit;
}

// Pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

try {
    // Total count
    $stmt_c = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE category_id = ? AND status = 'published'");
    $stmt_c->execute([$category['id']]);
    $total_articles = $stmt_c->fetchColumn();
    $total_pages = ceil($total_articles / $per_page);

    // Articles
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE category_id = ? AND status = 'published' ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $category['id'], PDO::PARAM_INT);
    $stmt->bindValue(2, $per_page, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $articles = [];
    $total_pages = 0;
}

$site_name = get_setting($pdo, 'site_name', 'Portal');
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categoria:
        <?= htmlspecialchars($category['name']) ?> -
        <?= htmlspecialchars($site_name) ?>
    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .category-header {
            background: var(--header-bg);
            padding: 4rem 0;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 3rem;
        }

        .category-title {
            font-size: 3rem;
            font-weight: 800;
            color: var(--text-main);
        }

        .category-subtitle {
            color: var(--text-muted);
            font-size: 1.2rem;
            margin-top: 0.5rem;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 4rem;
        }

        .pagination a,
        .pagination span {
            padding: 0.5rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            color: var(--text-main);
            font-weight: 600;
        }

        .pagination .active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .pagination a:hover {
            background: var(--border-color);
        }
    </style>
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
                        style="font-weight: bold; padding: 0.25rem 0.5rem; background: var(--border-color); border-radius: 4px;">Voltar
                        Home</a>
                </div>
            </div>
        </div>
    </header>

    <div class="category-header">
        <div class="container">
            <h1 class="category-title">
                <?= htmlspecialchars($category['name']) ?>
            </h1>
            <p class="category-subtitle">Notícias e novidades sobre
                <?= htmlspecialchars($category['name']) ?>
            </p>
        </div>
    </div>

    <main class="container">

        <?php if (empty($articles)): ?>
            <p style="text-align:center; color: var(--text-muted); padding: 2rem 0;">Nenhum artigo encontrado nesta
                categoria ainda.</p>
        <?php else: ?>
            <div class="news-list">
                <?php foreach ($articles as $news): ?>
                    <article class="news-card">
                        <a href="article.php?slug=<?= urlencode($news['slug']) ?>" class="img-wrap">
                            <span class="category-tag" style="position:absolute; top:10px; left:10px; z-index:10;">
                                <?= htmlspecialchars($category['name']) ?>
                            </span>
                            <img src="<?= htmlspecialchars($news['image_url'] ?? 'https://via.placeholder.com/400x250?text=Sem+Imagem') ?>"
                                alt="<?= htmlspecialchars($news['title']) ?>" loading="lazy">
                        </a>
                        <div>
                            <div class="meta">
                                <span>
                                    <?= date('d/m/Y', strtotime($news['created_at'])) ?>
                                </span>
                            </div>
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

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="active">
                                <?= $i ?>
                            </span>
                        <?php else: ?>
                            <a href="?slug=<?= urlencode($slug) ?>&page=<?= $i ?>">
                                <?= $i ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>

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