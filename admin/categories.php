<?php
require_once '../config/core.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Handle Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = !empty($_POST['id']) ? (int) $_POST['id'] : null;
    $name = trim($_POST['name']);
    $slug = !empty($_POST['slug']) ? generate_slug($_POST['slug']) : generate_slug($name);

    if ($id) {
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?");
        $stmt->execute([$name, $slug, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
        $stmt->execute([$name, $slug]);
    }
    header("Location: categories.php?msg=saved");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
    header("Location: categories.php?msg=deleted");
    exit;
}

$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Categorias - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>

    <aside class="sidebar">
        <div class="sidebar-header">News Admin</div>
        <ul class="nav-menu">
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="articles.php">Artigos</a></li>
            <li><a href="categories.php" class="active">Categorias</a></li>
            <li><a href="settings.php">Configurações</a></li>
            <li><a href="n8n_guide.php">Workflow (n8n)</a></li>
        </ul>
        <div class="logout-btn"><a href="index.php?logout=1">Sair do Painel</a></div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <h2>Gerenciar Categorias</h2>
        </header>

        <div class="page-content" style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">

            <div class="form-card" style="align-self: start;">
                <h3>Adicionar Nova</h3>
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Nome da Categoria</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Slug (opcional)</label>
                        <input type="text" name="slug" class="form-control"
                            placeholder="Deixe em branco para auto-gerar">
                    </div>
                    <button type="submit" class="btn-primary">Salvar Categoria</button>
                </form>
            </div>

            <div>
                <?php if (isset($_GET['msg'])): ?>
                    <div class="success-msg">Ação realizada com sucesso!</div>
                <?php endif; ?>

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Slug</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $c): ?>
                            <tr>
                                <td>
                                    <?= $c['id'] ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($c['name']) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($c['slug']) ?>
                                </td>
                                <td>
                                    <a href="?delete=<?= $c['id'] ?>" class="btn-danger"
                                        onclick="return confirm('Tem certeza? Isso pode afetar artigos.');">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>

</body>

</html>