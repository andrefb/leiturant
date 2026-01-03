-- Schema do banco de dados LeituraNT
-- SQLite

-- Usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT UNIQUE NOT NULL,
    nome TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Códigos de verificação (para login de usuários existentes)
CREATE TABLE IF NOT EXISTS codigos_verificacao (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL,
    codigo TEXT NOT NULL,
    expira_em DATETIME NOT NULL,
    usado INTEGER DEFAULT 0
);

-- Livros do Novo Testamento
CREATE TABLE IF NOT EXISTS livros (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    sigla TEXT NOT NULL,
    capitulos INTEGER NOT NULL,
    ordem INTEGER NOT NULL
);

-- Leituras (capítulos lidos por usuário)
CREATE TABLE IF NOT EXISTS leituras (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER NOT NULL,
    livro_id INTEGER NOT NULL,
    capitulo INTEGER NOT NULL,
    lido_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (livro_id) REFERENCES livros(id) ON DELETE CASCADE,
    UNIQUE(usuario_id, livro_id, capitulo)
);

-- Índices para performance
CREATE INDEX IF NOT EXISTS idx_leituras_usuario ON leituras(usuario_id);
CREATE INDEX IF NOT EXISTS idx_leituras_livro ON leituras(livro_id);
CREATE INDEX IF NOT EXISTS idx_codigos_email ON codigos_verificacao(email);
