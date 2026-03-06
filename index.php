<?php
require_once 'config/core.php';

if (!file_exists('config/db.php')) {
    header("Location: install/index.php");
    exit;
}

$site_name = get_setting($pdo, 'site_name', 'NoticiasNew');
$site_desc = get_setting($pdo, 'site_description', 'Últimas notícias');
$adsense_header = get_setting($pdo, 'adsense_header', '');

try {
    $stmt = $pdo->query("SELECT a.*, c.name as category_name, c.slug as category_slug FROM articles a LEFT JOIN categories c ON a.category_id = c.id WHERE a.status = 'published' ORDER BY a.created_at DESC LIMIT 3");
    $featured = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $featured = [];
}

try {
    $stmt_latest = $pdo->query("SELECT a.*, c.name as category_name, c.slug as category_slug FROM articles a LEFT JOIN categories c ON a.category_id = c.id WHERE a.status = 'published' ORDER BY a.created_at DESC LIMIT 3, 12");
    $latest = $stmt_latest->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $latest = [];
}

try {
    $stmt_cats = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $stmt_cats->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}

function get_image($url, $title)
{
    if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL))
        return htmlspecialchars($url);
    $initials = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $title), 0, 2));
    if (empty($initials))
        $initials = "NW";
    return "https://placehold.co/800x600/1e293b/ffffff?text=" . urlencode($initials) . "&font=Playfair+Display";
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($site_name) ?> - <?= htmlspecialchars($site_desc) ?></title>
    <meta name="description" content="<?= htmlspecialchars($site_desc) ?>">
    <!-- Boxicons for elegant icons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <?= $adsense_header ?>
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
                    <li><a href="index.php" class="active">Página Inicial</a></li>
                    <?php if (!empty($categories)):
                        foreach ($categories as $cat): ?>
                            <li><a href="category.php?slug=<?= htmlspecialchars($cat['slug']) ?>">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </a></li>
                        <?php endforeach; else: ?>
                        <li><a href="#">Brasil</a></li>
                        <li><a href="#">Mundo</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <!-- Featured Section -->
        <?php if (!empty($featured)): ?>
            <section class="hero-section">
                <div class="grid-featured">
                    <?php $main = $featured[0]; ?>
                    <article class="card card-main">
                        <a href="article.php?slug=<?= urlencode($main['slug']) ?>"
                            style="display:flex; flex-direction:column; height: 100%;">
                            <div class="img-container">
                                <img src="<?= get_image($main['image_url'], $main['title']) ?>"
                                    onerror="this.src='https://placehold.co/800x600/1e293b/ffffff?text=X'"
                                    alt="<?= htmlspecialchars($main['title']) ?>">
                            </div>
                            <div class="content">
                                <span class="category-tag">
                                    <i class='bx bxs-bolt-circle'></i>
                                    <?= htmlspecialchars($main['category_name'] ?? 'Destaque') ?>
                                </span>
                                <h1 class="title"><?= htmlspecialchars($main['title']) ?></h1>
                                <p class="summary">
                                    <?= htmlspecialchars($main['summary'] ?? 'Leia o artigo completo para mais detalhes sobre esta importante notícia.') ?>
                                </p>
                                <div class="card-meta">
                                    <div class="meta-author">
                                        <div class="meta-avatar"><i class='bx bx-user'></i></div>
                                        <span>Redação</span>
                                    </div>
                                    <span>&bull;</span>
                                    <span><i class='bx bx-calendar-alt'></i>
                                        <?= date('d M', strtotime($main['created_at'])) ?></span>
                                </div>
                            </div>
                        </a>
                    </article>

                    <div class="card-secondary-container">
                        <?php for ($i = 1; $i <= 2; $i++):
                            if (isset($featured[$i])):
                                $sm = $featured[$i]; ?>
                                <article class="card card-sm">
                                    <a href="article.php?slug=<?= urlencode($sm['slug']) ?>"
                                        style="display:flex; flex-direction:column; height: 100%;">
                                        <div class="img-container">
                                            <img src="<?= get_image($sm['image_url'], $sm['title']) ?>"
                                                onerror="this.src='https://placehold.co/600x400/1e293b/ffffff?text=X'"
                                                alt="<?= htmlspecialchars($sm['title']) ?>">
                                        </div>
                                        <div class="content">
                                            <span
                                                class="category-tag"><?= htmlspecialchars($sm['category_name'] ?? 'Notícia') ?></span>
                                            <h3 class="title"><?= htmlspecialchars($sm['title']) ?></h3>
                                            <div class="card-meta">
                                                <span><i class='bx bx-calendar'></i>
                                                    <?= date('d M, Y', strtotime($sm['created_at'])) ?></span>
                                            </div>
                                        </div>
                                    </a>
                                </article>
                            <?php endif; endfor; ?>
                    </div>
                </div>
            </section>
        <?php else: ?>
            <section class="hero-section">
                <div
                    style="text-align: center; padding: 4rem 2rem; background: var(--surface); border-radius: var(--radius-md); border: 1px dashed var(--border);">
                    <i class='bx bx-news' style="font-size: 4rem; color: var(--muted); margin-bottom: 1rem;"></i>
                    <h2 class="premium-serif">Nenhum artigo publicado ainda</h2>
                    <p style="color: var(--muted); margin-top: 0.5rem;">Crie conteúdo pelo painel administrativo para
                        preencher o site.</p>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($latest)): ?>
            <section class="latest-section">
                <div class="section-header">
                    <h2 class="section-title">Últimas Atualizações</h2>
                    <a href="#" style="color: var(--accent); font-weight: 600; font-size: 0.9rem;">Ver todas <i
                            class='bx bx-right-arrow-alt'></i></a>
                </div>
                <div class="news-list">
                    <?php foreach ($latest as $news): ?>
                        <article class="card">
                            <a href="article.php?slug=<?= urlencode($news['slug']) ?>">
                                <div class="img-container">
                                    <img src="<?= get_image($news['image_url'], $news['title']) ?>"
                                        onerror="this.src='https://placehold.co/600x400/1e293b/ffffff?text=X'"
                                        alt="<?= htmlspecialchars($news['title']) ?>">
                                </div>
                                <div class="content">
                                    <span class="category-tag"
                                        style="font-size: 0.65rem; padding: 0.25rem 0.6rem; opacity: 0.8; margin-bottom: 0.75rem;">
                                        <?= htmlspecialchars($news['category_name'] ?? 'Notícia') ?>
                                    </span>
                                    <h3 class="title"><?= htmlspecialchars($news['title']) ?></h3>
                                    <p class="summary"><?= htmlspecialchars($news['summary']) ?></p>
                                    <div class="card-meta" style="margin-top: 1rem; border-top: none; padding-top: 0;">
                                        <span><?= date('d M', strtotime($news['created_at'])) ?> &bull; 3 min de leitura</span>
                                    </div>
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
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="logo">
                        <i class='bx bx-news'></i>
                        <?= htmlspecialchars(explode(' ', $site_name)[0]) ?><span><?= isset(explode(' ', $site_name)[1]) ? htmlspecialchars(explode(' ', $site_name)[1]) : '' ?></span>
                    </div>
                    <p class="footer-desc">
                        <?= htmlspecialchars($site_desc ?? 'O portal líder em notícias curadas por Inteligência Artificial. Rapidez, precisão e design de classe mundial.') ?>
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
                        <?php if (!empty($categories)):
                            foreach (array_slice($categories, 0, 5) as $cat): ?>
                                <li><a href="category.php?slug=<?= htmlspecialchars($cat['slug']) ?>">
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </a></li>
                            <?php endforeach; else: ?>
                            <li><a href="#">Tecnologia</a></li>
                            <li><a href="#">Inovação</a></li>
                            <li><a href="#">Mercado</a></li>
                        <?php endif; ?>
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
        const icon = btn.querySelector('i');
        const html = document.documentElement;

        let savedTheme = localStorage.getItem('theme');
        if (!savedTheme) {
            savedTheme = 'dark';
            localStorage.setItem('theme', 'dark');
        }

        html.setAttribute('data-theme', savedTheme);
        updateIcon(savedTheme);

        btn.addEventListener('click', () => {
            const current = html.getAttribute('data-theme');
            const target = current === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', target);
            localStorage.setItem('theme', target);
            updateIcon(target);
        });

        function updateIcon(theme) {
            if (theme === 'dark') {
                icon.className = 'bx bx-sun';
            } else {
                icon.className = 'bx bx-moon';
            }
        }
    </script>
</body>

</html>