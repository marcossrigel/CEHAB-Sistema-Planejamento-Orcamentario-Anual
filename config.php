<?php

$local = [
  'host' => '127.0.0.1',
  'user' => 'root',
  'pass' => '', 
  'dbname' => 'planejamento_orcamentario'
];

// === CONFIGURAÇÃO REMOTA ===
$remote = [
  'host' => '172.19.16.15',
  'user' => 'siscreche',
  'pass' => 'Cehab@123_', 
  'dbname' => 'cehab_online'
];

$usarRemoto = true;

$cfg = $usarRemoto ? $remote : $local;

// === CONEXÃO ===
$conexao = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['dbname']);
if ($conexao->connect_error) {
  die("Erro na conexão com {$cfg['host']}: " . $conexao->connect_error);
}
$conexao->set_charset("utf8mb4");
?>
