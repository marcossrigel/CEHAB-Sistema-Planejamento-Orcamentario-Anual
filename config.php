<?php
// Conexão 1: escreve no planejamento_orcamentario (local)
$poa = new mysqli('127.0.0.1', 'root', '', 'planejamento_orcamentario');
if ($poa->connect_error) {
  die('Erro POA: '.$poa->connect_error);
}
$poa->set_charset('utf8mb4');


$cehab = new mysqli('172.19.16.15', 'siscreche', 'Cehab@123_', 'cehab_online');
if ($cehab->connect_error) {
  die('Erro CEHAB: '.$cehab->connect_error);
}
$cehab->set_charset('utf8mb4');
