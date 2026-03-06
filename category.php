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

    <!-- Boxicons for elegant icons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
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
                    <i class='bx bx-news'></i>
                    <?= htmlspecialchars(explode(' ', $site_name)[0]) ?><span><?= isset(explode(' ', $site_name)[1]) ? htmlspecialchars(explode(' ', $site_name)[1]) : '' ?></span>
                </a>
                <div class="nav-actions">
                    <form action="search.php" method="GET" class="header-search">
                        <input type="text" name="q" placeholder="Buscar notícias..." required>
                        <button type="submit" aria-label="Buscar"><i class='bx bx-search'></i></button>
                    </form>
                    <button class="theme-toggle" id="themeToggle" aria-label="Alternar Tema">
                        <i class='bx bx-moon'></i>
                    </button>
                </div>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php">Página Inicial</a></li>
                    <?php
                    try {
                        $stmt_cats_nav = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
                        $categories_nav = $stmt_cats_nav->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($categories_nav as $cat): ?>
                            <li><a href="category.php?slug=<?= htmlspecialchars($cat['slug']) ?>"
                                    <?= $cat['slug'] == $category['slug'] ? 'class="active"' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </a></li>
                        <?php endforeach;
                    } catch (Exception $e) {
                    } ?>
                </ul>
            </nav>
        </div>
    </header>

    <section class="category-hero-section">
        <div class="container">
            <h1 class="category-hero-title">
                <?= htmlspecialchars($category['name']) ?>
            </h1>
            <p class="category-hero-subtitle">
                Acompanhe as últimas publicações, tendências e análises profundas sobre
                <?= htmlspecialchars($category['name']) ?> curadas pela nossa Inteligência Artificial editora.
            </p>
            <div class="category-hero-meta">
                <span><i class='bx bx-file'></i> <?= $total_articles ?> Artigos</span>
                <span><i class='bx bx-time'></i> Atualizado Diariamente</span>
            </div>
        </div>
    </section>

    <main class="container">

        <?php if (empty($articles)): ?>
            <div
                style="text-align: center; padding: 4rem 2rem; background: var(--surface); border-radius: var(--radius-md); border: 1px dashed var(--border);">
                <i class='bx bx-file-blank' style="font-size: 4rem; color: var(--muted); margin-bottom: 1rem;"></i>
                <h2 class="premium-serif">Nenhum artigo encontrado aqui</h2>
                <p style="color: var(--muted); margin-top: 0.5rem;">Ainda não formatamos as notícias sobre
                    "<?= htmlspecialchars($category['name']) ?>". Volte mais tarde.</p>
            </div>
        <?php else: ?>
            <div class="news-list">
                <?php foreach ($articles as $news): ?>
                    <article class="card">
                        <a href="article.php?slug=<?= urlencode($news['slug']) ?>"
                            style="display:flex; flex-direction:column; height: 100%;">
                            <div class="img-container">
                                <span class="category-tag"
                                    style="position:absolute; top:15px; left:15px; z-index:10; background: var(--surface); color: var(--accent); box-shadow: var(--shadow-sm);">
                                    <?= htmlspecialchars($category['name']) ?>
                                </span>
                                <img src="<?= htmlspecialchars($news['image_url'] ?? 'https://placehold.co/600x400/1e293b/ffffff?text=X') ?>"
                                    onerror="this.src='https://placehold.co/600x400/1e293b/ffffff?text=X'"
                                    alt="<?= htmlspecialchars($news['title']) ?>" loading="lazy">
                            </div>
                            <div class="content">
                                <h3 class="title">
                                    <?= htmlspecialchars($news['title']) ?>
                                </h3>
                                <p class="summary">
                                    <?= htmlspecialchars($news['summary']) ?>
                                </p>
                                <div class="card-meta" style="margin-top: auto;">
                                    <span><i class='bx bx-calendar'></i>
                                        <?= date('d M, Y', strtotime($news['created_at'])) ?>
                                    </span>
                                    <span style="margin-left: auto; color: var(--accent); font-weight: 700;">Ler <i
                                            class='bx bx-right-arrow-alt'></i></span>
                                </div>
                            </div>
                        </a>
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
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="logo">
                        <i class='bx bx-news'></i>
                        <?php
                        $site_name_parts = explode(' ', $site_name ?? '');
                        echo htmlspecialchars($site_name_parts[0] ?? '');
                        if (isset($site_name_parts[1])) {
                            echo '<span>' . htmlspecialchars($site_name_parts[1]) . '</span>';
                        }
                        ?>
                    </div>
                    <p class="footer-desc">
                        O portal líder em notícias curadas por Inteligência Artificial. Rapidez, precisão e design de
                        classe mundial.
                    </p>
                    <div class="footer-socials">
                        <a href="#" class="social-icon" aria-label="Twitter"><i class='bx bxl-twitter'></i></a>
                        <a href="#" class="social-icon" aria-label="LinkedIn"><i class='bx bxl-linkedin'></i></a>
                        <a href="#" class="social-icon" aria-label="Instagram"><i class='bx bxl-instagram'></i></a>
                    </div>
                </div>

                <div>
                    <h4 class="footer-title">Navegação</h4>
                    <ul class="footer-links">
                        <li><a href="index.php">Página Inicial</a></li>
                        <li><a href="search.php">Pesquisa Avançada</a></li>
                        <li><a href="#">Sobre Nós</a></li>
                        <li><a href="#">Contato Editorial</a></li>
                        <li><a href="#">Privacidade & Termos</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="footer-title">Explore</h4>
                    <ul class="footer-links">
                        <li><a href="#">Tecnologia</a></li>
                        <li><a href="#">Inovação</a></li>
                        <li><a href="#">Mercado</a></li>
                        <li><a href="#">Startups</a></li>
                        <li><a href="#">Opinião AI</a></li>
                    </ul>
                </div>

                <div class="footer-subscribe">
                    <h4 class="footer-title">Newsletter Premium</h4>
                    <p>Receba as análises mais profundas diretamente na sua caixa de entrada, todas as manhãs.</p>
                    <form class="subscribe-form" action="#" method="POST"
                        onsubmit="event.preventDefault(); alert('Assinatura confirmada com sucesso!');">
                        <input type="email" placeholder="Seu melhor e-mail..." required>
                        <button type="submit"><i class='bx bx-send'></i></button>
                    </form>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($site_name) ?>. Todos os direitos reservados.</p>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <span style="display: flex; align-items: center; gap: 0.5rem;"><i class='bx bx-check-shield'
                            style="color: var(--accent);"></i> Criptografia SSL</span>
                    <span>Design by Stitch</span>
                </div>
            </div>
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