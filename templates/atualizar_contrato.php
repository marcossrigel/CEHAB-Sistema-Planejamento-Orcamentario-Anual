<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (!isset($_SESSION['usuario'])) {
  http_response_code(401);
  exit("Sessão não iniciada. Acesse via login.");
}

require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Método inválido.');
}

if (!isset($poa) || !($poa instanceof mysqli)) {
  http_response_code(500);
  exit('Conexão com o banco ($poa) não foi inicializada. Verifique o config.php');
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) exit('ID de contrato inválido.');

function parse_brl($str) {
  if ($str === null) return 0.0;
  $str = trim((string)$str);
  if ($str === '') return 0.0;

  $str = preg_replace('/[^\d\.,-]/', '', $str);
  if ($str === '' || $str === '-' || $str === '.' || $str === ',') return 0.0;

  $neg = false;
  if (strpos($str, '-') !== false) { $neg = true; $str = str_replace('-', '', $str); }

  $lastComma = strrpos($str, ',');
  $lastDot   = strrpos($str, '.');

  $decSep = null;
  if ($lastComma !== false && $lastDot !== false) $decSep = ($lastComma > $lastDot) ? ',' : '.';
  elseif ($lastComma !== false) $decSep = ',';
  elseif ($lastDot !== false) $decSep = '.';

  if ($decSep !== null) {
    $parts = explode($decSep, $str);
    $dec   = preg_replace('/\D/', '', array_pop($parts));
    $int   = preg_replace('/\D/', '', implode('', $parts));

    $dec = substr($dec, 0, 2);
    $dec = str_pad($dec, 2, '0');
    $num = ($int === '' ? '0' : $int) . '.' . $dec;
  } else {
    $num = preg_replace('/\D/', '', $str);
    if ($num === '') $num = '0';
  }

  $val = (float)$num;
  return $neg ? -$val : $val;
}

function bool_from_label($v) {
  $v = trim((string)$v);
  if ($v === '') return null;
  $v = mb_strtolower($v, 'UTF-8');
  if ($v === 'sim') return 1;
  if ($v === 'não' || $v === 'nao') return 0;
  return null;
}

// ------ inputs ------
$tema_custo       = $_POST['tema_custo']        ?? '';
$setor            = $_POST['setor']             ?? '';
$gestor           = $_POST['gestor']            ?? '';
$objeto           = $_POST['objeto']            ?? '';
$status           = $_POST['status']            ?? '';
$numero_contrato  = $_POST['numero_contrato']   ?? '';
$credor           = $_POST['credor']            ?? '';
$vigencia_fim_txt = trim((string)($_POST['vigencia'] ?? '')); // TEXTO LIVRE
$dea_label        = $_POST['dea']               ?? '';
$reajuste_label   = $_POST['reajuste']          ?? '';
$fonte            = $_POST['fonte']             ?? '';
$grupo            = $_POST['grupo']             ?? '';
$sei              = $_POST['sei']               ?? '';
$valor_total_str  = $_POST['valor_total_contrato'] ?? '0';
$acao             = $_POST['acao']              ?? '';
$subacao          = $_POST['subacao']           ?? '';
$ficha_financeira = $_POST['ficha_financeira']  ?? '';
$macro_tema       = $_POST['macro_tema']        ?? '';
$priorizacao      = $_POST['priorizacao']       ?? '';
$prorrogavel_lbl  = $_POST['prorrogavel']       ?? '';
$observacoes      = $_POST['observacoes']       ?? '';
$mesesPost        = $_POST['mes']               ?? [];

$dea         = bool_from_label($dea_label);
$reajuste    = bool_from_label($reajuste_label);
$prorrogavel = bool_from_label($prorrogavel_lbl);

$valor_total = parse_brl($valor_total_str);
if ($valor_total > 999999999999999.99) exit('Valor total excede o limite permitido.');
$valor_total_db = number_format($valor_total, 2, '.', '');

$mesValsDb = [];
for ($i = 0; $i < 12; $i++) {
  $v = isset($mesesPost[$i]) ? parse_brl($mesesPost[$i]) : 0.0;
  $mesValsDb[$i] = number_format($v, 2, '.', '');
}

$sql = "
  UPDATE novo_contrato
  SET
    tema_custo        = ?,
    setor             = ?,
    gestor            = ?,
    objeto            = ?,
    status_contrato   = ?,
    numero_contrato   = ?,
    credor            = ?,
    vigencia_fim      = ?,
    observacoes       = ?,
    dea               = ?,
    reajuste          = ?,
    fonte             = ?,
    grupo_despesa     = ?,
    sei               = ?,
    valor_total       = ?,
    acao              = ?,
    subacao           = ?,
    ficha_financeira  = ?,
    macro_tema        = ?,
    priorizacao       = ?,
    prorrogavel       = ?,
    janeiro           = ?,
    fevereiro         = ?,
    marco             = ?,
    abril             = ?,
    maio              = ?,
    junho             = ?,
    julho             = ?,
    agosto            = ?,
    setembro          = ?,
    outubro           = ?,
    novembro          = ?,
    dezembro          = ?
  WHERE id = ?
";

$stmt = $poa->prepare($sql);

// 9 strings + 2 ints + 4 strings + 5 strings + 1 int + 12 strings + 1 int
$types =
  "sssssssss" .  // tema..observacoes (9)
  "ii" .         // dea, reajuste
  "ssss" .       // fonte, grupo, sei, valor_total
  "sssss" .      // acao, subacao, ficha, macro, priorizacao
  "i" .          // prorrogavel
  "ssssssssssss" . // 12 meses
  "i";           // id

$params = [
  $tema_custo,
  $setor,
  $gestor,
  $objeto,
  $status,
  $numero_contrato,
  $credor,
  ($vigencia_fim_txt === '' ? null : $vigencia_fim_txt),
  $observacoes,
  ($dea === null ? 0 : $dea),
  ($reajuste === null ? 0 : $reajuste),
  $fonte,
  $grupo,
  $sei,
  $valor_total_db,
  $acao,
  $subacao,
  $ficha_financeira,
  $macro_tema,
  $priorizacao,
  ($prorrogavel === null ? 0 : $prorrogavel),
  ...$mesValsDb,
  $id
];

$stmt->bind_param($types, ...$params);
$stmt->execute();

header("Location: editar_contrato.php?id={$id}&ok=1");
exit;
