<?php
/**
 * Página de Verificação - Digitar código enviado por email
 */
require_once __DIR__ . '/config.php';

// Se já está logado, redireciona
if (estaLogado()) {
    header('Location: index.php');
    exit;
}

$email = $_SESSION['verificar_email'] ?? '';
$erro = '';

if (empty($email)) {
    header('Location: login.php');
    exit;
}

$usuario = buscarUsuarioPorEmail($email);
if (!$usuario) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = strtoupper(trim($_POST['codigo'] ?? ''));
    
    if (strlen($codigo) !== 4) {
        $erro = 'O código deve ter 4 caracteres.';
    } else if (verificarCodigo($email, $codigo)) {
        // Código correto - loga
        logarUsuario($usuario['id']);
        unset($_SESSION['verificar_email']);
        header('Location: index.php');
        exit;
    } else {
        $erro = 'Código inválido ou expirado. Tente novamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Verificar código - LeituraNT</title>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "bg-dark": "#0f1419",
                        "card-dark": "#1a2332",
                        "accent-blue": "#3b82f6",
                    },
                    fontFamily: {
                        "serif": ["Merriweather", "Georgia", "serif"],
                        "sans": ["Inter", "sans-serif"]
                    },
                },
            },
        }
    </script>
    <style>
        .code-input {
            letter-spacing: 0.5em;
            text-align: center;
            font-family: monospace;
        }
    </style>
</head>
<body class="dark bg-bg-dark font-sans text-white min-h-screen flex flex-col">
    
    <!-- Header simples -->
    <header class="p-5">
        <a href="login.php" class="flex items-center gap-2 text-gray-400 hover:text-white transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
            Usar outro email
        </a>
    </header>
    
    <main class="flex-1 flex items-center justify-center px-5 pb-10">
        <div class="w-full max-w-md">
            
            <!-- Ícone -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-accent-blue/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-4xl text-accent-blue">mark_email_read</span>
                </div>
                <h1 class="text-2xl font-serif font-bold">Verifique seu email</h1>
                <p class="text-gray-400 mt-2">
                    Enviamos um código de 4 caracteres para<br>
                    <strong class="text-white"><?= htmlspecialchars($email) ?></strong>
                </p>
            </div>
            
            <!-- Formulário -->
            <form method="POST" class="space-y-4">
                
                <?php if ($erro): ?>
                <div class="bg-red-500/20 border border-red-500/50 rounded-xl p-4 text-red-300 text-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-xl">error</span>
                    <?= htmlspecialchars($erro) ?>
                </div>
                <?php endif; ?>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2 text-center">Digite o código</label>
                    <input 
                        type="text" 
                        name="codigo" 
                        required
                        autofocus
                        maxlength="4"
                        placeholder="XXXX"
                        autocomplete="off"
                        class="code-input w-full px-4 py-5 bg-card-dark border border-white/10 rounded-xl text-white placeholder-gray-600 focus:outline-none focus:border-accent-blue focus:ring-1 focus:ring-accent-blue text-3xl uppercase"
                    />
                </div>
                
                <button type="submit" class="w-full py-4 bg-accent-blue hover:bg-blue-600 text-white font-semibold rounded-xl transition-colors flex items-center justify-center gap-2 text-lg">
                    <span class="material-symbols-outlined">login</span>
                    Verificar e entrar
                </button>
                
            </form>
            
            <p class="text-center text-gray-500 text-sm mt-6">
                O código expira em 10 minutos.<br>
                Verifique também a pasta de spam.
            </p>
            
            <!-- Reenviar -->
            <div class="text-center mt-4">
                <a href="login.php" class="text-accent-blue hover:underline text-sm">
                    Não recebeu? Tentar novamente
                </a>
            </div>
            
        </div>
    </main>
    
</body>
</html>
