<?php
/**
 * Página principal - Dashboard de leitura
 */
require_once __DIR__ . '/config.php';

// Verifica se está logado
$logado = estaLogado();
$usuario = $logado ? getUsuarioLogado() : null;

// Carrega livros do banco (ou mockados se banco não existir)
if (dbExists()) {
    $livrosDB = carregarLivros();
    $leiturasUsuario = $logado && $usuario ? carregarLeituras($usuario['id']) : [];
    
    // Monta array de livros com contagem de lidos
    $livros = [];
    foreach ($livrosDB as $livro) {
        $livros[] = [
            'id' => $livro['id'],
            'nome' => $livro['nome'],
            'sigla' => $livro['sigla'],
            'capitulos' => $livro['capitulos'],
            'lidos' => $leiturasUsuario[$livro['id']] ?? 0
        ];
    }
} else {
    // Banco não existe - usa mockado zerado para visitante
    $livros = [
        ['id' => 1, 'nome' => 'Mateus', 'sigla' => 'Mt', 'capitulos' => 28, 'lidos' => 0],
        ['id' => 2, 'nome' => 'Marcos', 'sigla' => 'Mc', 'capitulos' => 16, 'lidos' => 0],
        ['id' => 3, 'nome' => 'Lucas', 'sigla' => 'Lc', 'capitulos' => 24, 'lidos' => 0],
        ['id' => 4, 'nome' => 'João', 'sigla' => 'Jo', 'capitulos' => 21, 'lidos' => 0],
        ['id' => 5, 'nome' => 'Atos', 'sigla' => 'At', 'capitulos' => 28, 'lidos' => 0],
        ['id' => 6, 'nome' => 'Romanos', 'sigla' => 'Rm', 'capitulos' => 16, 'lidos' => 0],
        ['id' => 7, 'nome' => '1 Coríntios', 'sigla' => '1Co', 'capitulos' => 16, 'lidos' => 0],
        ['id' => 8, 'nome' => '2 Coríntios', 'sigla' => '2Co', 'capitulos' => 13, 'lidos' => 0],
        ['id' => 9, 'nome' => 'Gálatas', 'sigla' => 'Gl', 'capitulos' => 6, 'lidos' => 0],
        ['id' => 10, 'nome' => 'Efésios', 'sigla' => 'Ef', 'capitulos' => 6, 'lidos' => 0],
        ['id' => 11, 'nome' => 'Filipenses', 'sigla' => 'Fp', 'capitulos' => 4, 'lidos' => 0],
        ['id' => 12, 'nome' => 'Colossenses', 'sigla' => 'Cl', 'capitulos' => 4, 'lidos' => 0],
        ['id' => 13, 'nome' => '1 Tessalonicenses', 'sigla' => '1Ts', 'capitulos' => 5, 'lidos' => 0],
        ['id' => 14, 'nome' => '2 Tessalonicenses', 'sigla' => '2Ts', 'capitulos' => 3, 'lidos' => 0],
        ['id' => 15, 'nome' => '1 Timóteo', 'sigla' => '1Tm', 'capitulos' => 6, 'lidos' => 0],
        ['id' => 16, 'nome' => '2 Timóteo', 'sigla' => '2Tm', 'capitulos' => 4, 'lidos' => 0],
        ['id' => 17, 'nome' => 'Tito', 'sigla' => 'Tt', 'capitulos' => 3, 'lidos' => 0],
        ['id' => 18, 'nome' => 'Filemom', 'sigla' => 'Fm', 'capitulos' => 1, 'lidos' => 0],
        ['id' => 19, 'nome' => 'Hebreus', 'sigla' => 'Hb', 'capitulos' => 13, 'lidos' => 0],
        ['id' => 20, 'nome' => 'Tiago', 'sigla' => 'Tg', 'capitulos' => 5, 'lidos' => 0],
        ['id' => 21, 'nome' => '1 Pedro', 'sigla' => '1Pe', 'capitulos' => 5, 'lidos' => 0],
        ['id' => 22, 'nome' => '2 Pedro', 'sigla' => '2Pe', 'capitulos' => 3, 'lidos' => 0],
        ['id' => 23, 'nome' => '1 João', 'sigla' => '1Jo', 'capitulos' => 5, 'lidos' => 0],
        ['id' => 24, 'nome' => '2 João', 'sigla' => '2Jo', 'capitulos' => 1, 'lidos' => 0],
        ['id' => 25, 'nome' => '3 João', 'sigla' => '3Jo', 'capitulos' => 1, 'lidos' => 0],
        ['id' => 26, 'nome' => 'Judas', 'sigla' => 'Jd', 'capitulos' => 1, 'lidos' => 0],
        ['id' => 27, 'nome' => 'Apocalipse', 'sigla' => 'Ap', 'capitulos' => 22, 'lidos' => 0],
    ];
}

// Calcular totais
$totalCapitulos = 260;
$totalLidos = array_sum(array_column($livros, 'lidos'));
$percentual = $totalCapitulos > 0 ? round(($totalLidos / $totalCapitulos) * 100) : 0;
$faltam = $totalCapitulos - $totalLidos;

// Encontrar último livro em progresso
$livroAtual = null;
foreach ($livros as $livro) {
    if ($livro['lidos'] > 0 && $livro['lidos'] < $livro['capitulos']) {
        $livroAtual = $livro;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Leitura Bíblica - Novo Testamento</title>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        // Modo escuro
                        "bg-dark": "#0f1419",
                        "card-dark": "#1a2332",
                        "card-dark-alt": "#243044",
                        // Modo claro
                        "bg-light": "#f5f0e8",
                        "card-light": "#ffffff",
                        "card-light-alt": "#e8e0d0",
                        // Cores de destaque
                        "accent-blue": "#3b82f6",
                        "accent-green": "#22c55e",
                        "accent-gold": "#d4a574",
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
        body { min-height: 100dvh; }
        
        /* Transição suave de tema */
        * { transition: background-color 0.3s, color 0.3s, border-color 0.3s; }
        
        /* Progress bar customizada */
        .progress-bar {
            background: linear-gradient(90deg, #22c55e 0%, #16a34a 100%);
        }
        
        /* Card Hero gradiente */
        .hero-gradient-dark {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 50%, #1e3a5f 100%);
        }
        .hero-gradient-light {
            background: linear-gradient(135deg, #4a7c59 0%, #5d9a6a 50%, #4a7c59 100%);
        }
    </style>
</head>

<!-- Modo escuro por padrão -->
<body id="body" class="dark bg-bg-dark font-sans text-white min-h-screen pb-24">

    <!-- Header -->
    <header class="sticky top-0 z-50 backdrop-blur-md bg-bg-dark/90 dark:bg-bg-dark/90 bg-bg-light/90 border-b border-white/5 dark:border-white/5 border-black/5">
        <div class="max-w-lg mx-auto px-5 py-4 flex items-center justify-between">
            <div>
                <?php if ($logado && $usuario): ?>
                <p class="text-2xl font-serif font-bold dark:text-white text-gray-800">
                    Olá, <?= htmlspecialchars($usuario['nome']) ?>!
                </p>
                <p class="text-sm dark:text-gray-400 text-gray-600 mt-0.5">Vamos ler hoje?</p>
                <?php else: ?>
                <p class="text-2xl font-serif font-bold dark:text-white text-gray-800">
                    Olá, Visitante!
                </p>
                <p class="text-sm dark:text-orange-400 text-orange-600 mt-0.5 flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">info</span>
                    Você não está logado
                </p>
                <?php endif; ?>
            </div>
            <div class="flex items-center gap-2">
                <?php if ($logado): ?>
                <a href="logout.php" class="flex items-center gap-1 px-3 py-2 text-gray-400 hover:text-white text-sm transition-colors" title="Sair">
                    <span class="material-symbols-outlined text-lg">logout</span>
                </a>
                <?php else: ?>
                <a href="login.php" class="flex items-center gap-1.5 px-4 py-2 bg-accent-blue hover:bg-blue-600 text-white text-sm font-medium rounded-full transition-colors">
                    <span class="material-symbols-outlined text-lg">login</span>
                    Entrar
                </a>
                <?php endif; ?>
                <button onclick="toggleTheme()" class="w-10 h-10 rounded-full dark:bg-card-dark bg-card-light flex items-center justify-center dark:text-yellow-400 text-gray-700 hover:scale-105 transition-transform" title="Alternar tema">
                    <span class="material-symbols-outlined text-xl" id="theme-icon">light_mode</span>
                </button>
            </div>
        </div>
    </header>

    <main class="max-w-lg mx-auto px-5 py-6 space-y-6">

        <!-- Card Hero: Progresso Global -->
        <section class="hero-gradient-dark dark:hero-gradient-dark rounded-3xl p-6 text-white shadow-xl">
            <p class="text-xs uppercase tracking-widest opacity-80 mb-2">📖 Progresso Novo Testamento</p>
            
            <div class="flex items-end gap-4 mb-4">
                <span class="text-6xl font-bold font-serif"><?= $percentual ?>%</span>
                <span class="text-xl opacity-90 pb-2">Concluído</span>
            </div>
            
            <!-- Barra de progresso -->
            <div class="w-full h-3 bg-white/20 rounded-full overflow-hidden mb-4">
                <div class="h-full progress-bar rounded-full" style="width: <?= $percentual ?>%"></div>
            </div>
            
            <!-- Infos -->
            <div class="space-y-2 text-sm">
                <p class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">schedule</span>
                    Nesse ritmo, você terminará em <strong>4 meses</strong>
                </p>
                <p class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">menu_book</span>
                    <strong><?= $totalLidos ?></strong> de <strong><?= $totalCapitulos ?></strong> capítulos lidos
                </p>
            </div>
        </section>

        <!-- Continuar Leitura -->
        <?php if ($livroAtual): ?>
        <section>
            <p class="text-xs uppercase tracking-widest dark:text-gray-500 text-gray-500 mb-3 px-1">Sua Leitura Atual</p>
            
            <a href="livro.php?nome=<?= urlencode($livroAtual['nome']) ?>" 
               class="block dark:bg-accent-blue/20 bg-green-100 border-2 dark:border-accent-blue/50 border-green-400 rounded-2xl p-5 hover:scale-[1.02] transition-transform">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-xl font-serif font-bold dark:text-white text-gray-800"><?= $livroAtual['nome'] ?></p>
                        <p class="text-sm dark:text-gray-400 text-gray-600 mt-1">
                            Capítulo <?= $livroAtual['lidos'] + 1 ?> de <?= $livroAtual['capitulos'] ?>
                        </p>
                        <!-- Mini progresso -->
                        <div class="w-full h-2 dark:bg-white/10 bg-gray-200 rounded-full mt-3 overflow-hidden">
                            <div class="h-full progress-bar rounded-full" style="width: <?= round($livroAtual['lidos'] / $livroAtual['capitulos'] * 100) ?>%"></div>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-2 ml-4">
                        <span class="text-xl font-bold dark:text-accent-blue text-green-600">
                            <?= round($livroAtual['lidos'] / $livroAtual['capitulos'] * 100) ?>%
                        </span>
                        <span class="dark:bg-accent-blue bg-green-500 text-white text-xs font-bold px-3 py-1.5 rounded-full">
                            RETOMAR →
                        </span>
                    </div>
                </div>
            </a>
        </section>
        <?php endif; ?>

        <!-- Grid de Livros -->
        <section>
            <div class="flex items-center justify-between mb-4 px-1">
                <p class="text-sm font-semibold uppercase tracking-wide dark:text-gray-300 text-gray-600">Livros</p>
                <span class="text-xs dark:text-gray-500 text-gray-400 dark:bg-card-dark bg-gray-100 px-2 py-1 rounded-lg">
                   Capítulos: <?= $totalLidos ?> de <?= $totalCapitulos ?> lidos
                </span>
            </div>
            
            <div class="grid grid-cols-3 gap-2.5">
                <?php foreach ($livros as $index => $livro): 
                    $pct = $livro['capitulos'] > 0 ? round($livro['lidos'] / $livro['capitulos'] * 100) : 0;
                    $completo = $pct == 100;
                    $emProgresso = $pct > 0 && $pct < 100;
                    // Verifica se é o livro atual (primeiro em progresso)
                    $isLivroAtual = $livroAtual && $livro['nome'] === $livroAtual['nome'];
                ?>
                <a href="livro.php?nome=<?= urlencode($livro['nome']) ?>"
                   class="relative aspect-square rounded-xl flex flex-col items-center justify-center
                          transition-all hover:scale-[1.03] active:scale-95
                          <?php if ($completo): ?>
                              dark:bg-amber-900/40 bg-amber-50 border-2 border-amber-500
                          <?php elseif ($isLivroAtual): ?>
                              dark:bg-accent-blue/20 bg-blue-50 border-2 border-accent-blue dark:border-accent-blue
                          <?php elseif ($emProgresso): ?>
                              dark:bg-cyan-900/20 bg-cyan-50 border border-cyan-400 dark:border-cyan-600
                          <?php else: ?>
                              dark:bg-card-dark bg-gray-50 border border-gray-200 dark:border-white/10
                          <?php endif; ?>
                   ">
                    
                    <!-- Indicador no canto superior -->
                    <?php if ($completo): ?>
                    <span class="absolute top-1.5 left-1.5">
                        <span class="material-symbols-outlined text-amber-500 text-lg" style="font-variation-settings: 'FILL' 1">check_circle</span>
                    </span>
                    <?php elseif ($isLivroAtual): ?>
                    <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-accent-blue rounded-full animate-pulse"></span>
                    <?php elseif ($emProgresso): ?>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-cyan-500 rounded-full opacity-70"></span>
                    <?php endif; ?>
                    
                    <!-- Sigla GRANDE -->
                    <span class="text-3xl font-bold leading-none
                        <?php if ($completo): ?>
                            text-amber-600 dark:text-amber-400
                        <?php elseif ($isLivroAtual): ?>
                            text-accent-blue dark:text-accent-blue
                        <?php elseif ($emProgresso): ?>
                            text-cyan-600 dark:text-cyan-400
                        <?php else: ?>
                            dark:text-gray-300 text-gray-600
                        <?php endif; ?>
                    ">
                        <?= $livro['sigla'] ?>
                    </span>
                    
                    <!-- Nome do livro -->
                    <p class="text-[9px] font-medium uppercase tracking-wide mt-1.5
                        <?php if ($completo): ?>
                            text-amber-700 dark:text-amber-300
                        <?php elseif ($isLivroAtual): ?>
                            text-blue-700 dark:text-blue-300
                        <?php elseif ($emProgresso): ?>
                            text-cyan-700 dark:text-cyan-300
                        <?php else: ?>
                            dark:text-gray-400 text-gray-500
                        <?php endif; ?>
                    ">
                        <?= $livro['nome'] ?>
                    </p>
                    
                    <!-- Contador de capítulos e porcentagem -->
                    <div class="absolute bottom-1.5 left-0 right-0 flex justify-center">
                        <span class="text-[8px] font-semibold px-1.5 py-0.5 rounded
                            <?php if ($completo): ?>
                                bg-amber-500/20 text-amber-700 dark:text-amber-300
                            <?php elseif ($isLivroAtual): ?>
                                bg-accent-blue/20 text-blue-700 dark:text-blue-300
                            <?php elseif ($emProgresso): ?>
                                bg-cyan-500/20 text-cyan-700 dark:text-cyan-300
                            <?php else: ?>
                                dark:bg-white/5 bg-gray-100 dark:text-gray-500 text-gray-500
                            <?php endif; ?>
                        ">
                            <?= $livro['lidos'] ?>/<?= $livro['capitulos'] ?>
                            <?php if ($pct > 0): ?> · <?= $pct ?>%<?php endif; ?>
                        </span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </section>

    </main>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 dark:bg-card-dark/95 bg-white/95 backdrop-blur-lg border-t dark:border-white/5 border-gray-200 z-50">
        <div class="max-w-lg mx-auto flex items-center justify-around py-3">
            <a href="index.php" class="flex flex-col items-center gap-1 text-accent-blue">
                <span class="material-symbols-outlined text-2xl">home</span>
                <span class="text-[10px] font-medium">Início</span>
            </a>
            <a href="livro.php" class="flex flex-col items-center gap-1 dark:text-gray-500 text-gray-400">
                <span class="material-symbols-outlined text-2xl">auto_stories</span>
                <span class="text-[10px] font-medium">Minha Leitura</span>
            </a>
            <a href="ajustes.php" class="flex flex-col items-center gap-1 dark:text-gray-500 text-gray-400">
                <span class="material-symbols-outlined text-2xl">settings</span>
                <span class="text-[10px] font-medium">Ajustes</span>
            </a>
        </div>
    </nav>

    <script>
        // Sistema de tema
        function toggleTheme() {
            const body = document.getElementById('body');
            const icon = document.getElementById('theme-icon');
            
            if (body.classList.contains('dark')) {
                // Mudar para claro
                body.classList.remove('dark', 'bg-bg-dark', 'text-white');
                body.classList.add('bg-bg-light', 'text-gray-800');
                icon.textContent = 'dark_mode';
                localStorage.setItem('theme', 'light');
            } else {
                // Mudar para escuro
                body.classList.add('dark', 'bg-bg-dark', 'text-white');
                body.classList.remove('bg-bg-light', 'text-gray-800');
                icon.textContent = 'light_mode';
                localStorage.setItem('theme', 'dark');
            }
        }
        
        // Carregar tema salvo
        document.addEventListener('DOMContentLoaded', () => {
            const saved = localStorage.getItem('theme');
            if (saved === 'light') {
                toggleTheme();
            }
        });
    </script>
</body>
</html>
