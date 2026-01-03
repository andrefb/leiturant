<?php
/**
 * Logout - Destrói sessão e redireciona
 */
require_once __DIR__ . '/config.php';

deslogarUsuario();

header('Location: index.php');
exit;
