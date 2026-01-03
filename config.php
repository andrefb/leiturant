<?php
/**
 * Configuração global do app LeituraNT
 * - Carrega variáveis do .env
 * - Conexão PDO com SQLite
 * - Inicia sessão PHP (persistente - nunca expira)
 */

// Configurações de erro (desativar em produção)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Timezone São Paulo
date_default_timezone_set('America/Sao_Paulo');

// Configura sessão para durar muito tempo (10 anos)
$sessionLifetime = 60 * 60 * 24 * 365 * 10; // 10 anos em segundos
ini_set('session.gc_maxlifetime', $sessionLifetime);
ini_set('session.cookie_lifetime', $sessionLifetime);
session_set_cookie_params([
    'lifetime' => $sessionLifetime,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Inicia sessão
session_start();

// Carrega .env
function loadEnv(): void {
    $envFile = __DIR__ . '/.env';
    if (!file_exists($envFile)) return;
    
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '=')) continue;
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        
        if (!getenv($name)) {
            putenv("$name=$value");
            $_ENV[$name] = $value;
        }
    }
}

loadEnv();

// Configurações de email
define('MAIL_HOST', getenv('MAIL_HOST') ?: '');
define('MAIL_PORT', getenv('MAIL_PORT') ?: 587);
define('MAIL_USERNAME', getenv('MAIL_USERNAME') ?: '');
define('MAIL_PASSWORD', getenv('MAIL_PASSWORD') ?: '');
define('MAIL_FROM_ADDRESS', getenv('MAIL_FROM_ADDRESS') ?: '');
define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: 'LeituraNT');

// Conexão com SQLite
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $dbPath = __DIR__ . '/data/' . ($_ENV['DB_PATH'] ?? 'leitura.db');
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    return $pdo;
}

// Verifica se banco existe
function dbExists() {
    $dbPath = __DIR__ . '/data/' . ($_ENV['DB_PATH'] ?? 'leitura.db');
    return file_exists($dbPath);
}

// Obtém usuário logado da sessão
function getUsuarioLogado() {
    if (!isset($_SESSION['usuario_id'])) return null;
    try {
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM usuarios WHERE id = ?');
        $stmt->execute([$_SESSION['usuario_id']]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
}

// Verifica se está logado
function estaLogado() {
    return isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] > 0;
}

// Loga usuário
function logarUsuario($usuarioId) {
    $_SESSION['usuario_id'] = $usuarioId;
}

// Desloga
function deslogarUsuario() {
    unset($_SESSION['usuario_id']);
    session_destroy();
}

// Gera código de verificação (4 caracteres: letras + números)
function gerarCodigoVerificacao() {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Sem I, O, 0, 1 para evitar confusão
    $codigo = '';
    for ($i = 0; $i < 4; $i++) {
        $codigo .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $codigo;
}

// Busca usuário por email
function buscarUsuarioPorEmail($email) {
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM usuarios WHERE email = ?');
    $stmt->execute([strtolower(trim($email))]);
    return $stmt->fetch();
}

// Cria novo usuário
function criarUsuario($email, $nome) {
    $db = getDB();
    $stmt = $db->prepare('INSERT INTO usuarios (email, nome) VALUES (?, ?)');
    $stmt->execute([strtolower(trim($email)), trim($nome)]);
    return $db->lastInsertId();
}

// Salva código de verificação
function salvarCodigoVerificacao($email, $codigo) {
    $db = getDB();
    // Remove códigos antigos deste email
    $db->prepare('DELETE FROM codigos_verificacao WHERE email = ?')->execute([strtolower($email)]);
    // Insere novo código (expira em 10 minutos)
    $expira = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    $stmt = $db->prepare('INSERT INTO codigos_verificacao (email, codigo, expira_em) VALUES (?, ?, ?)');
    $stmt->execute([strtolower($email), $codigo, $expira]);
}

// Verifica código
function verificarCodigo($email, $codigo) {
    $db = getDB();
    $agora = date('Y-m-d H:i:s'); // Usa timezone PHP (São Paulo)
    $stmt = $db->prepare('SELECT * FROM codigos_verificacao WHERE email = ? AND codigo = ? AND expira_em > ? AND usado = 0');
    $stmt->execute([strtolower($email), strtoupper($codigo), $agora]);
    $row = $stmt->fetch();
    if ($row) {
        // Marca como usado
        $db->prepare('UPDATE codigos_verificacao SET usado = 1 WHERE id = ?')->execute([$row['id']]);
        return true;
    }
    return false;
}

// Carrega livros do banco
function carregarLivros() {
    try {
        $db = getDB();
        return $db->query('SELECT * FROM livros ORDER BY ordem')->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

// Carrega leituras de um usuário (retorna array [livro_id => quantidade_lida])
function carregarLeituras($usuarioId) {
    $db = getDB();
    $stmt = $db->prepare('SELECT livro_id, COUNT(*) as lidos FROM leituras WHERE usuario_id = ? GROUP BY livro_id');
    $stmt->execute([$usuarioId]);
    $result = [];
    foreach ($stmt->fetchAll() as $row) {
        $result[$row['livro_id']] = $row['lidos'];
    }
    return $result;
}

/**
 * Enviar email via SMTP
 */
function enviarEmailSMTP(string $para, string $nome, string $assunto, string $textoPlano, string $html): bool {
    try {
        $porta = (int)MAIL_PORT;
        $usaSSL = ($porta === 465);
        
        // Conectar com ou sem SSL
        if ($usaSSL) {
            $socket = @stream_socket_client(
                "ssl://" . MAIL_HOST . ":" . MAIL_PORT,
                $errno, $errstr, 30,
                STREAM_CLIENT_CONNECT,
                stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]])
            );
        } else {
            $socket = @fsockopen(MAIL_HOST, MAIL_PORT, $errno, $errstr, 30);
        }
        
        if (!$socket) {
            error_log("Erro SMTP: Não foi possível conectar - $errstr ($errno)");
            return false;
        }

        // Boundary para multipart
        $boundary = md5(time());
        
        // Headers
        $headers = [
            "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM_ADDRESS . ">",
            "Reply-To: " . MAIL_FROM_ADDRESS,
            "To: {$nome} <{$para}>",
            "Subject: =?UTF-8?B?" . base64_encode($assunto) . "?=",
            "MIME-Version: 1.0",
            "Content-Type: multipart/alternative; boundary=\"{$boundary}\"",
            "X-Mailer: PHP/" . phpversion()
        ];

        // Corpo
        $corpo = "--{$boundary}\r\n";
        $corpo .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $corpo .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $corpo .= chunk_split(base64_encode($textoPlano)) . "\r\n";
        $corpo .= "--{$boundary}\r\n";
        $corpo .= "Content-Type: text/html; charset=UTF-8\r\n";
        $corpo .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $corpo .= chunk_split(base64_encode($html)) . "\r\n";
        $corpo .= "--{$boundary}--";

        // Comandos SMTP - ler banner multiline
        $resposta = fgets($socket, 515);
        while (substr($resposta, 3, 1) == '-') $resposta = fgets($socket, 515);
        
        fputs($socket, "EHLO leitura.vivos.site\r\n");
        $resposta = fgets($socket, 515);
        while (substr($resposta, 3, 1) == '-') $resposta = fgets($socket, 515);
        
        // STARTTLS apenas se não for SSL direto (porta 587)
        if (!$usaSSL) {
            fputs($socket, "STARTTLS\r\n");
            $resposta = fgets($socket, 515);
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            
            fputs($socket, "EHLO leitura.vivos.site\r\n");
            $resposta = fgets($socket, 515);
            while (substr($resposta, 3, 1) == '-') $resposta = fgets($socket, 515);
        }
        
        fputs($socket, "AUTH LOGIN\r\n");
        $resposta = fgets($socket, 515);
        
        fputs($socket, base64_encode(MAIL_USERNAME) . "\r\n");
        $resposta = fgets($socket, 515);
        
        fputs($socket, base64_encode(MAIL_PASSWORD) . "\r\n");
        $resposta = fgets($socket, 515);
        
        fputs($socket, "MAIL FROM:<" . MAIL_FROM_ADDRESS . ">\r\n");
        $resposta = fgets($socket, 515);
        
        fputs($socket, "RCPT TO:<{$para}>\r\n");
        $resposta = fgets($socket, 515);
        
        fputs($socket, "DATA\r\n");
        $resposta = fgets($socket, 515);
        
        fputs($socket, implode("\r\n", $headers) . "\r\n\r\n" . $corpo . "\r\n.\r\n");
        $resposta = fgets($socket, 515);
        
        fputs($socket, "QUIT\r\n");
        fclose($socket);
        
        return str_starts_with($resposta, '250');
        
    } catch (Exception $e) {
        error_log("Erro ao enviar email: " . $e->getMessage());
        return false;
    }
}

/**
 * Envia email de verificação com código
 */
function enviarCodigoVerificacao(string $email, string $codigo, string $nome): bool {
    $assunto = "Seu código de acesso: $codigo";
    
    $textoPlano = "Olá, $nome!\n\nSeu código de acesso é: $codigo\n\nEste código expira em 10 minutos.\n\nSe você não solicitou este código, ignore este email.";
    
    $html = "
    <html>
    <body style='font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5;'>
        <div style='max-width: 400px; margin: 0 auto; background: white; border-radius: 12px; padding: 30px;'>
            <h2 style='color: #333; margin-bottom: 10px;'>Olá, $nome!</h2>
            <p style='color: #666;'>Seu código de acesso é:</p>
            <div style='background: #3b82f6; color: white; padding: 20px; text-align: center; font-size: 32px; letter-spacing: 8px; font-weight: bold; margin: 20px 0; border-radius: 8px;'>
                $codigo
            </div>
            <p style='color: #999; font-size: 14px;'>Este código expira em 10 minutos.</p>
            <p style='color: #999; font-size: 12px;'>Se você não solicitou este código, ignore este email.</p>
        </div>
    </body>
    </html>
    ";
    
    return enviarEmailSMTP($email, $nome, $assunto, $textoPlano, $html);
}
