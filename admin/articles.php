<?php
require_once '../config/core.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $pdo->prepare("DELETE FROM articles WHERE id = ?")->execute([$id]);
    header("Location: articles.php?msg=deleted");
    exit;
}

// Pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$total = $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
$total_pages = ceil($total / $per_page);

$stmt = $pdo->prepare("SELECT a.id, a.title, a.status, a.created_at, c.name as cat_name FROM articles a LEFT JOIN categories c ON a.category_id = c.id ORDER BY a.created_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $per_page, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Artigos - Admin</title>
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
            <h2>Gerenciar Artigos</h2>
            <a href="article_edit.php" class="btn-primary">+ Novo Artigo</a>
        </header>

        <div class="page-content">

            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                <div class="success-msg">Artigo excluído com sucesso.</div>
            <?php endif; ?>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Categoria</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $a): ?>
                        <tr>
                            <td>
                                <?= $a['id'] ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($a['title']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($a['cat_name'] ?? 'Nenhuma') ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= $a['status'] === 'published' ? 'published' : 'draft' ?>">
                                    <?= $a['status'] == 'published' ? 'Publicado' : 'Rascunho' ?>
                                </span>
                            </td>
                            <td>
                                <?= date('d/m/Y', strtotime($a['created_at'])) ?>
                            </td>
                            <td>
                                <a href="../article.php?slug=<?= generate_slug($a['title']) ?>" target="_blank"
                                    class="btn-secondary" style="margin-right:0.5rem;">Ver</a>
                                <a href="article_edit.php?id=<?= $a['id'] ?>" class="btn-secondary"
                                    style="margin-right:0.5rem;">Editar</a>
                                <a href="?delete=<?= $a['id'] ?>" class="btn-danger"
                                    onclick="return confirm('Tem certeza?');">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="margin-top: 2rem; display:flex; gap:0.5rem;">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="btn-secondary" <?= $i == $page ? 'style="background:var(--admin-accent); color:white;"' : '' ?>>
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>

        </div>
    </main>

</body>

</html>