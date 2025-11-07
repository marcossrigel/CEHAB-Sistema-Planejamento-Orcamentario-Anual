<?php
// templates/salvar_contrato.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../config.php'; // agora temos $poa e $cehab

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
  'usuario_cehab'    => $_SESSION['usuario']['nome'] ?? null,
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

$stmt = $poa->prepare($sql);
if (!$stmt) {
  http_response_code(500);
  echo "Erro ao preparar statement: " . $poa->error;
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
  // Em vez de redirecionar, exibe o modal de sucesso
  ?>
  <!doctype html>
  <html lang="pt-br">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contrato Salvo • POA</title>
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="bg-slate-50 flex items-center justify-center min-h-screen">
    <!-- Modal de sucesso -->
    <div class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
      <div class="bg-white rounded-2xl shadow-lg p-8 w-full max-w-md text-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-14 w-14 text-green-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <h1 class="text-xl font-semibold text-slate-800 mb-2">Contrato salvo com sucesso!</h1>
        <p class="text-slate-600 mb-6">As informações do contrato foram registradas corretamente no sistema.</p>
        <button onclick="window.location.href='home.php'"
                class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-xl shadow-sm">
          OK
        </button>
      </div>
    </div>
  </body>
  </html>
  <?php
  exit;
} else {
  http_response_code(500);
  echo "Erro ao salvar: " . $stmt->error;
}
