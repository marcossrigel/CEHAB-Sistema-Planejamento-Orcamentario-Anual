<?php
// templates/salvar_contrato.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../config.php'; // aponta para C:\laragon\www\...\config.php

/* ---------- Helpers ---------- */

// "R$ 1.234,56" -> 1234.56
function brl_to_decimal(?string $str): float {
  $str = (string)$str;
  $str = preg_replace('/[^0-9,.-]/', '', $str);
  $str = str_replace(['.', ' '], '', $str);
  $str = str_replace(',', '.', $str);
  return is_numeric($str) ? (float)$str : 0.0;
}

// "mm/aaaa - mm/aaaa" -> ("YYYY-mm-01", "YYYY-mm-últimoDia")
function parse_vigencia(?string $vigencia): array {
  $vigencia = trim((string)$vigencia);
  // aceita "mm/aaaa-mm/aaaa" ou "mm/aaaa - mm/aaaa"
  $parts = preg_split('/\s*-\s*/', $vigencia);
  $ini = $fim = null;

  if (!empty($parts[0]) && preg_match('~^(\d{2})/(\d{4})$~', trim($parts[0]), $m1)) {
    [$all,$mm,$yyyy] = $m1;
    $ini = sprintf('%04d-%02d-01', (int)$yyyy, (int)$mm);
  }
  if (!empty($parts[1]) && preg_match('~^(\d{2})/(\d{4})$~', trim($parts[1]), $m2)) {
    [$all,$mm,$yyyy] = $m2;
    $dt  = DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-01', (int)$yyyy, (int)$mm));
    if ($dt) { $dt->modify('last day of this month'); $fim = $dt->format('Y-m-d'); }
  }
  return [$ini, $fim];
}

// "Sim"->1, "Não/Nao"->0, vazio->NULL
function bool_ptbr($v) {
  if ($v === 'Sim') return 1;
  if ($v === 'Não' || $v === 'Nao') return 0;
  return null;
}

/* ---------- Coleta POST ---------- */

[$vig_ini, $vig_fim] = parse_vigencia($_POST['vigencia'] ?? '');

$mes = $_POST['mes'] ?? [];
$toDecimal = fn($i) => brl_to_decimal($mes[$i] ?? '0');

// nomes dos campos de mês NA TABELA (por extenso)
$mesCampos = [
  'janeiro','fevereiro','marco','abril',
  'maio','junho','julho','agosto',
  'setembro','outubro','novembro','dezembro'
];

$campos = [
  'tema_custo'       => $_POST['tema_custo'] ?? null,
  'setor'            => $_POST['setor'] ?? null,
  'gestor'           => $_POST['gestor'] ?? null,
  'objeto'           => $_POST['objeto'] ?? null,
  'status_contrato'  => $_POST['status'] ?? null,
  'numero_contrato'  => $_POST['numero_contrato'] ?? null,
  'credor'           => $_POST['credor'] ?? null,
  'vigencia_inicio'  => $vig_ini,
  'vigencia_fim'     => $vig_fim,
  'dea'              => bool_ptbr($_POST['dea'] ?? null),
  'reajuste'         => bool_ptbr($_POST['reajuste'] ?? null),
  'fonte'            => $_POST['fonte'] ?? null,
  'grupo_despesa'    => $_POST['grupo'] ?? null,
  'sei'              => $_POST['sei'] ?? null,
  'valor_total'      => brl_to_decimal($_POST['valor_total'] ?? '0'),
  'acao'             => $_POST['acao'] ?? null,
  'subacao'          => $_POST['subacao'] ?? null,
  'ficha_financeira' => $_POST['ficha_financeira'] ?? null,
  'macro_tema'       => $_POST['macro_tema'] ?? null,
  'priorizacao'      => $_POST['priorizacao'] ?? null,
  'prorrogavel'      => bool_ptbr($_POST['prorrogavel'] ?? null),
];

// adiciona os meses (form usa mes[0..11])
foreach ($mesCampos as $i => $nomeMes) {
  $campos[$nomeMes] = $toDecimal($i);
}

/* ---------- INSERT ---------- */

$cols = implode(',', array_map(fn($c) => "`$c`", array_keys($campos)));
$placeholders = rtrim(str_repeat('?,', count($campos)), ',');
$sql = "INSERT INTO `novo_contrato` ($cols) VALUES ($placeholders)";

$stmt = $conexao->prepare($sql);
if (!$stmt) {
  http_response_code(500);
  echo "Erro ao preparar statement: " . $conexao->error;
  exit;
}

$types = '';
$values = [];
$numericos = array_merge(['valor_total'], $mesCampos);

foreach ($campos as $k => $v) {
  if (in_array($k, $numericos, true)) { $types .= 'd'; $values[] = (float)$v; }
  elseif (in_array($k, ['dea','reajuste','prorrogavel'], true)) { $types .= 'i'; $values[] = is_null($v) ? null : (int)$v; }
  else { $types .= 's'; $values[] = $v; }
}

$stmt->bind_param($types, ...$values);

if ($stmt->execute()) {
  header('Location: sucesso.php');
  exit;
} else {
  http_response_code(500);
  echo "Erro ao salvar: " . $stmt->error;
}
