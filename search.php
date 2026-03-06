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
                    <i class='bx bx-news'></i>
                    <?= htmlspecialchars(explode(' ', $site_name)[0]) ?><span><?= isset(explode(' ', $site_name)[1]) ? htmlspecialchars(explode(' ', $site_name)[1]) : '' ?></span>
                </a>
                <div class="nav-actions">
                    <button class="theme-toggle" id="themeToggle" aria-label="Alternar Tema">
                        <i class='bx bx-moon'></i>
                    </button>
                    <a href="index.php"
                        style="font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--accent);">
                        Voltar Home <i class='bx bx-right-arrow-alt'></i>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <section class="category-hero-section">
        <div class="container">
            <h1 class="category-hero-title">
                <?= empty($q) ? 'Pesquisa Avançada' : 'Resultados para: "' . htmlspecialchars($q) . '"' ?>
            </h1>
            <p class="category-hero-subtitle">
                Explore nosso arquivo de conteúdo e análises detalhadas guiadas por Inteligência Artificial.
            </p>

            <form action="search.php" method="GET" style="max-width: 600px; margin: 3rem auto 0; position: relative;">
                <input type="text" name="q" value="<?= htmlspecialchars($q) ?>"
                    placeholder="O que você está procurando?"
                    style="width: 100%; padding: 1.25rem 2rem; border-radius: 3rem; border: 1px solid var(--border); background: var(--surface); color: var(--fg); font-family: var(--font-sans); font-size: 1.1rem; box-shadow: var(--shadow-sm); transition: var(--transition);"
                    required>
                <button type="submit"
                    style="position: absolute; right: 8px; top: 8px; bottom: 8px; background: var(--accent); color: white; border: none; border-radius: 3rem; width: 50px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; transition: var(--transition);">
                    <i class='bx bx-search'></i>
                </button>
            </form>
        </div>
    </section>

    <main class="container" style="padding: 2rem 0 4rem; min-height: 40vh;">

        <?php if (empty($q)): ?>
            <div
                style="text-align: center; padding: 4rem 2rem; background: var(--surface); border-radius: var(--radius-md); border: 1px dashed var(--border);">
                <i class='bx bx-search-alt' style="font-size: 4rem; color: var(--muted); margin-bottom: 1rem;"></i>
                <h2 class="premium-serif">O que você deseja ler hoje?</h2>
                <p style="color: var(--muted); margin-top: 0.5rem;">Utilize a barra acima para buscar por palavras-chave
                    relevantes.</p>
            </div>
        <?php elseif (empty($articles)): ?>
            <div
                style="text-align: center; padding: 4rem 2rem; background: var(--surface); border-radius: var(--radius-md); border: 1px dashed var(--border);">
                <i class='bx bx-file-blank' style="font-size: 4rem; color: var(--muted); margin-bottom: 1rem;"></i>
                <h2 class="premium-serif">Nenhum resultado encontrado</h2>
                <p style="color: var(--muted); margin-top: 0.5rem;">Sua busca para "<?= htmlspecialchars($q) ?>" não
                    retornou artigos. Tente termos menos específicos ou navegue pelas categorias no rodapé.</p>
            </div>
        <?php else: ?>
            <p style="margin-bottom: 2rem; color: var(--muted); font-weight: 600;"><i class='bx bx-check-circle'
                    style="color: var(--accent);"></i> Encontramos <?= count($articles) ?> resultados.</p>
            <div class="news-list">
                <?php foreach ($articles as $news): ?>
                    <article class="card">
                        <a href="article.php?slug=<?= urlencode($news['slug']) ?>"
                            style="display:flex; flex-direction:column; height: 100%;">
                            <div class="img-container">
                                <span class="category-tag"
                                    style="position:absolute; top:15px; left:15px; z-index:10; background: var(--surface); color: var(--accent); box-shadow: var(--shadow-sm);">
                                    <?= htmlspecialchars($news['category_name']) ?>
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