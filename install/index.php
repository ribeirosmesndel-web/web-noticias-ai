<?php
session_start();
$step = isset($_GET['step']) ? (int) $_GET['step'] : 1;
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - Automated News Portal</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f3f4f6;
            color: #1f2937;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .container {
            background: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }

        h1 {
            margin-top: 0;
            font-size: 1.5rem;
            text-align: center;
            color: #111827;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.875rem;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            background: #3b82f6;
            color: white;
            padding: 0.75rem;
            border: none;
            border-radius: 0.375rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }

        button:hover {
            background: #2563eb;
        }

        .alert {
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }

        .alert-success {
            background: #dcfce3;
            color: #166534;
        }

        .progress {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            justify-content: center;
        }

        .step {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e5e7eb;
            color: #6b7280;
            font-weight: bold;
        }

        .step.active {
            background: #3b82f6;
            color: white;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Instalação do Portal</h1>

        <div class="progress">
            <div class="step <?= $step === 1 ? 'active' : '' ?>">1</div>
            <div class="step <?= $step === 2 ? 'active' : '' ?>">2</div>
            <div class="step <?= $step === 3 ? 'active' : '' ?>">3</div>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= $_SESSION['error'];
            unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
            <form action="process.php?action=db" method="POST">
                <h3>Configuração de Banco de Dados</h3>
                <p style="font-size: 0.875rem; color:#4b5563;">Insira os dados de conexão do banco MySQL da Hostinger.</p>
                <div class="form-group">
                    <label>Host</label>
                    <input type="text" name="db_host" value="localhost" required>
                </div>
                <div class="form-group">
                    <label>Nome do Banco de Dados</label>
                    <input type="text" name="db_name" required placeholder="Ex: u123456_news">
                </div>
                <div class="form-group">
                    <label>Usuário</label>
                    <input type="text" name="db_user" required placeholder="Ex: u123456_admin">
                </div>
                <div class="form-group">
                    <label>Senha</label>
                    <input type="password" name="db_pass" required>
                </div>
                <button type="submit">Testar Conexão e Continuar</button>
            </form>

        <?php elseif ($step === 2): ?>
            <form action="process.php?action=admin" method="POST">
                <h3>Usuário Administrador</h3>
                <div class="form-group">
                    <label>Nome do Site</label>
                    <input type="text" name="site_name" required placeholder="Ex: Portal de Notícias">
                </div>
                <div class="form-group">
                    <label>Email do Admin</label>
                    <input type="email" name="admin_email" required>
                </div>
                <div class="form-group">
                    <label>Usuário Admin</label>
                    <input type="text" name="admin_user" required>
                </div>
                <div class="form-group">
                    <label>Senha de Admin</label>
                    <input type="password" name="admin_pass" required minlength="6">
                </div>
                <button type="submit">Criar Usuário e Finalizar</button>
            </form>

        <?php elseif ($step === 3): ?>
            <h3 style="text-align: center; color: #166534;">🎉 Instalação Concluída!</h3>
            <p style="text-align: center;">O seu portal já está pronto para uso.</p>
            <p style="text-align: center; color: red; font-size: 0.875rem;">IMPORTANTE: Exclua a pasta <code>/install</code>
                por segurança.</p>
            <a href="../admin/" style="display: block; text-align: center; text-decoration: none;"><button
                    type="button">Acessar Painel de Administração</button></a>
            <a href="../"
                style="display: block; text-align: center; margin-top: 1rem; color: #3b82f6; text-decoration: none;">Ver
                Homepage</a>
        <?php endif; ?>
    </div>
</body>

</html>