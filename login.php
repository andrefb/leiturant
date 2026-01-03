<?php
/**
 * Página de Login - Entrada de email
 */
require_once __DIR__ . '/config.php';

// Se já está logado, redireciona
if (estaLogado()) {
    header('Location: index.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Por favor, digite um email válido.';
    } else {
        // Verifica se usuário existe
        $usuario = buscarUsuarioPorEmail($email);
        
        if (!$usuario) {
            // Usuário novo - vai para registro
            header('Location: registro.php?email=' . urlencode($email));
            exit;
        } else {
            // Usuário existente - envia código
            $codigo = gerarCodigoVerificacao();
            salvarCodigoVerificacao($email, $codigo);
            
            // Tenta enviar email via SMTP
            $enviouEmail = enviarCodigoVerificacao($email, $codigo, $usuario['nome']);
            
            if ($enviouEmail) {
                // Redireciona para verificação
                $_SESSION['verificar_email'] = $email;
                header('Location: verificar.php');
                exit;
            } else {
                $erro = 'Não foi possível enviar o email. Tente novamente.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Entrar - LeituraNT</title>
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
</head>
<body class="dark bg-bg-dark font-sans text-white min-h-screen flex flex-col">
    
    <!-- Header simples -->
    <header class="p-5">
        <a href="index.php" class="flex items-center gap-2 text-gray-400 hover:text-white transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
            Voltar
        </a>
    </header>
    
    <main class="flex-1 flex items-center justify-center px-5 pb-10">
        <div class="w-full max-w-md">
            
            <!-- Ícone -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-accent-blue/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-4xl text-accent-blue">menu_book</span>
                </div>
                <h1 class="text-2xl font-serif font-bold">Bem-vindo de volta!</h1>
                <p class="text-gray-400 mt-2">Digite seu email para continuar</p>
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
                    <label class="block text-sm font-medium text-gray-300 mb-2">Seu email</label>
                    <input 
                        type="email" 
                        name="email" 
                        required
                        autofocus
                        placeholder="seu@email.com"
                        class="w-full px-4 py-4 bg-card-dark border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-accent-blue focus:ring-1 focus:ring-accent-blue text-lg"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    />
                </div>
                
                <button type="submit" class="w-full py-4 bg-accent-blue hover:bg-blue-600 text-white font-semibold rounded-xl transition-colors flex items-center justify-center gap-2 text-lg">
                    Continuar
                    <span class="material-symbols-outlined">arrow_forward</span>
                </button>
                
            </form>
            
            <p class="text-center text-gray-500 text-sm mt-6">
                Se você é novo, criaremos sua conta automaticamente.
            </p>
            
        </div>
    </main>
    
</body>
</html>
