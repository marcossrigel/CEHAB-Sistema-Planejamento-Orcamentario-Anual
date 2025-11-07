<?php
// config.php — conexão MySQL
$host = '127.0.0.1';
$user = 'root';
$pass = ''; // geralmente vazio no Laragon
$dbname = 'planejamento_orcamentario';

$conexao = new mysqli($host, $user, $pass, $dbname);

if ($conexao->connect_error) {
    die("Erro na conexão: " . $conexao->connect_error);
}

$conexao->set_charset("utf8mb4");
?>
