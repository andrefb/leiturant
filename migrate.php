<?php
/**
 * Script de migração - Cria/atualiza o banco de dados SQLite
 */

require_once __DIR__ . '/config.php';

$dbPath = __DIR__ . '/data/' . ($_ENV['DB_PATH'] ?? 'leitura.db');

echo "<h1>🗄️ Migração do Banco de Dados</h1>";
echo "<pre>";

try {
    // Cria conexão
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexão com SQLite estabelecida\n";
    echo "📁 Arquivo: $dbPath\n\n";
    
    // Executa schema
    echo "📋 Executando schema.sql...\n";
    $schema = file_get_contents(__DIR__ . '/database/schema.sql');
    $pdo->exec($schema);
    echo "✅ Tabelas criadas/atualizadas\n\n";
    
    // Executa seed
    echo "🌱 Executando seed.sql...\n";
    $seed = file_get_contents(__DIR__ . '/database/seed.sql');
    $pdo->exec($seed);
    echo "✅ Dados inseridos\n\n";
    
    // Verifica
    $livros = $pdo->query('SELECT COUNT(*) FROM livros')->fetchColumn();
    echo "📚 Total de livros no banco: $livros\n";
    
    echo "\n✅ MIGRAÇÃO CONCLUÍDA COM SUCESSO!\n";
    echo "\n<a href='index.php'>← Voltar para o app</a>";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}

echo "</pre>";
