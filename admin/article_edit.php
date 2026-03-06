<?php
require_once '../config/core.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$article = [
    'title' => '',
    'slug' => '',
    'category_id' => '',
    'summary' => '',
    'content' => '',
    'image_url' => '',
    'seo_title' => '',
    'seo_description' => '',
    'seo_tags' => '',
    'status' => 'published'
];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($res)
        $article = $res;
}

// Fetch categories for dropdown
$cats = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $slug = !empty($_POST['slug']) ? generate_slug($_POST['slug']) : generate_slug($title);
    $category_id = (int) $_POST['category_id'];
    $summary = trim($_POST['summary']);
    $content = trim($_POST['content']);
    $image_url = trim($_POST['image_url']);
    $seo_title = trim($_POST['seo_title']);
    $seo_description = trim($_POST['seo_description']);
    $seo_tags = trim($_POST['seo_tags']);
    $status = $_POST['status'];

    if ($id) {
        $stmt = $pdo->prepare("UPDATE articles SET title=?, slug=?, category_id=?, summary=?, content=?, image_url=?, seo_title=?, seo_description=?, seo_tags=?, status=? WHERE id=?");
        $stmt->execute([$title, $slug, $category_id, $summary, $content, $image_url, $seo_title, $seo_description, $seo_tags, $status, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO articles (title, slug, category_id, summary, content, image_url, seo_title, seo_description, seo_tags, status, ai_generated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
        $stmt->execute([$title, $slug, $category_id, $summary, $content, $image_url, $seo_title, $seo_description, $seo_tags, $status]);
    }

    header("Location: articles.php?msg=saved");
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $id ? 'Editar' : 'Novo' ?> Artigo - Admin
    </title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>

    <aside class="sidebar">
        <div class="sidebar-header">News Admin</div>
        <ul class="nav-menu">
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="articles.php" class="active">Artigos</a></li>
            <li><a href="categories.php">Categorias</a></li>
            <li><a href="settings.php">Configurações</a></li>
            <li><a href="n8n_guide.php">Workflow (n8n)</a></li>
        </ul>
        <div class="logout-btn"><a href="index.php?logout=1">Sair do Painel</a></div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <h2>
                <?= $id ? 'Editar Artigo' : 'Criar Novo Artigo' ?>
            </h2>
            <a href="articles.php" class="btn-secondary">Voltar para Lista</a>
        </header>

        <div class="page-content">
            <form method="POST" action="" class="form-card" style="max-width: 1000px;">

                <div class="flex-between">
                    <div class="form-group" style="flex:2; margin-right:1rem;">
                        <label>Título do Artigo</label>
                        <input type="text" name="title" class="form-control"
                            value="<?= htmlspecialchars($article['title']) ?>" required>
                    </div>
                    <div class="form-group" style="flex:1; margin-right:1rem;">
                        <label>Categoria</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($cats as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $article['category_id'] == $c['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="published" <?= $article['status'] == 'published' ? 'selected' : '' ?>>Publicado
                            </option>
                            <option value="draft" <?= $article['status'] == 'draft' ? 'selected' : '' ?>>Rascunho</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>URL Amigável (Slug) <small style="font-weight:normal;color:#6b7280;">(Opcional: deixe em
                            branco para auto-gerar)</small></label>
                    <input type="text" name="slug" class="form-control"
                        value="<?= htmlspecialchars($article['slug']) ?>">
                </div>

                <div class="form-group">
                    <label>URL da Imagem de Capa</label>
                    <input type="url" name="image_url" class="form-control"
                        value="<?= htmlspecialchars($article['image_url']) ?>" placeholder="https://...">
                </div>

                <div class="form-group">
                    <label>Resumo (Lead)</label>
                    <textarea name="summary" class="form-control"
                        rows="3"><?= htmlspecialchars($article['summary']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Conteúdo Completo (Aceita HTML básico)</label>
                    <textarea name="content" class="form-control" rows="15"
                        required><?= htmlspecialchars($article['content']) ?></textarea>
                </div>

                <!-- SEO SECTION -->
                <h3 style="border-bottom: 1px solid var(--admin-border); padding-bottom: 0.5rem; margin-top:2rem;">
                    Otimização SEO</h3>

                <div class="form-group">
                    <label>SEO Title (Usado nas tags meta do cabeçalho)</label>
                    <input type="text" name="seo_title" class="form-control"
                        value="<?= htmlspecialchars($article['seo_title']) ?>">
                </div>

                <div class="form-group">
                    <label>SEO Meta Description</label>
                    <textarea name="seo_description" class="form-control"
                        rows="2"><?= htmlspecialchars($article['seo_description']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Keywords/Tags (Separadas por vírgula)</label>
                    <input type="text" name="seo_tags" class="form-control"
                        value="<?= htmlspecialchars($article['seo_tags']) ?>"
                        placeholder="Ex: tecnologia, brasil, economia">
                </div>

                <div style="margin-top: 2rem;">
                    <button type="submit" class="btn-primary" style="font-size: 1.1rem; padding: 1rem 2rem;">
                        <?= $id ? 'Salvar Alterações' : 'Publicar Artigo' ?>
                    </button>
                </div>

            </form>
        </div>
    </main>

</body>

</html>