<?php
/**
 * Página de Registro - Pedir nome do novo usuário
 */
require_once __DIR__ . '/config.php';

// Se já está logado, redireciona
if (estaLogado()) {
    header('Location: index.php');
    exit;
}

$email = trim($_GET['email'] ?? '');
$erro = '';

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: login.php');
    exit;
}

// Verifica se email já existe (não deveria chegar aqui nesse caso)
if (buscarUsuarioPorEmail($email)) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    
    if (empty($nome) || strlen($nome) < 2) {
        $erro = 'Por favor, digite seu nome (mínimo 2 caracteres).';
    } else {
        // Cria usuário
        $usuarioId = criarUsuario($email, $nome);
        
        // Loga automaticamente
        logarUsuario($usuarioId);
        
        // Redireciona para index
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Criar conta - LeituraNT</title>
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
                        "accent-green": "#22c55e",
                    },
                    fontFamily: {
                        "serif": ["Merriweather", "Georgia", "serif"],
                        "sans": ["Inter", "sans-serif"]
                    },
                },
            },
        }
    </script>
</head>
<body class="dark bg-bg-dark font-sans text-white min-h-screen flex flex-col">
    
    <!-- Header simples -->
    <header class="p-5">
        <a href="login.php" class="flex items-center gap-2 text-gray-400 hover:text-white transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
            Voltar
        </a>
    </header>
    
    <main class="flex-1 flex items-center justify-center px-5 pb-10">
        <div class="w-full max-w-md">
            
            <!-- Ícone -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-accent-green/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-4xl text-accent-green">person_add</span>
                </div>
                <h1 class="text-2xl font-serif font-bold">Quase lá!</h1>
                <p class="text-gray-400 mt-2">Como podemos te chamar?</p>
            </div>
            
            <!-- Info do email -->
            <div class="bg-card-dark rounded-xl p-4 mb-6 flex items-center gap-3">
                <span class="material-symbols-outlined text-gray-500">email</span>
                <span class="text-gray-300"><?= htmlspecialchars($email) ?></span>
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
                    <label class="block text-sm font-medium text-gray-300 mb-2">Seu nome</label>
                    <input 
                        type="text" 
                        name="nome" 
                        required
                        autofocus
                        placeholder="Ex: Maria, João, Gabriel..."
                        class="w-full px-4 py-4 bg-card-dark border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-accent-green focus:ring-1 focus:ring-accent-green text-lg"
                        value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>"
                    />
                </div>
                
                <button type="submit" class="w-full py-4 bg-accent-green hover:bg-green-600 text-white font-semibold rounded-xl transition-colors flex items-center justify-center gap-2 text-lg">
                    <span class="material-symbols-outlined">check</span>
                    Criar minha conta
                </button>
                
            </form>
            
            <p class="text-center text-gray-500 text-sm mt-6">
                Seu progresso será salvo automaticamente.
            </p>
            
        </div>
    </main>
    
</body>
</html>
