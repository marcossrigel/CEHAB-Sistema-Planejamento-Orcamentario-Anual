<?php
// templates/atualizar_contrato.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (!isset($_SESSION['usuario'])) {
  http_response_code(401);
  echo "Sessão não iniciada. Acesse via login.";
  exit;
}

require_once __DIR__ . '/../config.php'; // precisa existir $poa (mysqli)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  die('Método inválido.');
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
  die('ID de contrato inválido.');
}

// ------- helpers -------
function parse_brl($str) {
  if ($str === null || $str === '') return 0.0;
  $str = preg_replace('/[^\d,.-]/', '', $str); // tira R$, espaços etc
  $str = str_replace('.', '', $str);
  $str = str_replace(',', '.', $str);
  return (float)$str;
}

function bool_from_label($v) {
  $v = trim((string)$v);
  if ($v === '') return null;
  $v = mb_strtolower($v, 'UTF-8');
  if ($v === 'sim') return 1;
  if ($v === 'não' || $v === 'nao') return 0;
  return null;
}

function parse_vigencia($str) {
  // espera "mm/aaaa - mm/aaaa"
  $str = trim((string)$str);
  if ($str === '') return [null, null];

  $parts = explode('-', $str);
  if (count($parts) !== 2) return [null, null];

  $ini = trim($parts[0]); // mm/aaaa
  $fim = trim($parts[1]); // mm/aaaa

  $vigIni = null;
  $vigFim = null;

  if (preg_match('#^(\d{2})/(\d{4})$#', $ini, $m)) {
    $vigIni = $m[2] . '-' . $m[1] . '-01'; // primeiro dia do mês
  }
  if (preg_match('#^(\d{2})/(\d{4})$#', $fim, $m)) {
    // último dia do mês
    $d = new DateTime($m[2] . '-' . $m[1] . '-01');
    $d->modify('last day of this month');
    $vigFim = $d->format('Y-m-d');
  }
  return [$vigIni, $vigFim];
}

// ------- coleta campos -------
$tema_custo       = $_POST['tema_custo']       ?? '';
$setor            = $_POST['setor']            ?? '';
$gestor           = $_POST['gestor']           ?? '';
$objeto           = $_POST['objeto']           ?? '';
$status           = $_POST['status']           ?? '';
$numero_contrato  = $_POST['numero_contrato']  ?? '';
$credor           = $_POST['credor']           ?? '';
$vigencia_str     = $_POST['vigencia']         ?? '';
$dea_label        = $_POST['dea']              ?? '';
$reajuste_label   = $_POST['reajuste']         ?? '';
$fonte            = $_POST['fonte']            ?? '';
$grupo            = $_POST['grupo']            ?? '';
$sei              = $_POST['sei']              ?? '';
$valor_total_str  = $_POST['valor_total']      ?? '';
$acao             = $_POST['acao']             ?? '';
$subacao          = $_POST['subacao']          ?? '';
$ficha_financeira = $_POST['ficha_financeira'] ?? '';
$macro_tema       = $_POST['macro_tema']       ?? '';
$priorizacao      = $_POST['priorizacao']      ?? '';
$prorrogavel_lbl  = $_POST['prorrogavel']      ?? '';
$mesesPost        = $_POST['mes']              ?? [];

// converte vigência
list($vigencia_inicio, $vigencia_fim) = parse_vigencia($vigencia_str);

// converte booleanos
$dea        = bool_from_label($dea_label);
$reajuste   = bool_from_label($reajuste_label);
$prorrogavel = bool_from_label($prorrogavel_lbl);

// valor total
$valor_total = parse_brl($valor_total_str);

// meses (0..11)
$mesVals = [];
for ($i = 0; $i < 12; $i++) {
  $mesVals[$i] = isset($mesesPost[$i]) ? parse_brl($mesesPost[$i]) : 0.0;
}

// ------- monta UPDATE -------
$sql = "
  UPDATE novo_contrato
  SET
    tema_custo      = ?,
    setor           = ?,
    gestor          = ?,
    objeto          = ?,
    status_contrato = ?,
    numero_contrato = ?,
    credor          = ?,
    vigencia_inicio = ?,
    vigencia_fim    = ?,
    dea             = ?,
    reajuste        = ?,
    fonte           = ?,
    grupo_despesa   = ?,
    sei             = ?,
    valor_total     = ?,
    acao            = ?,
    subacao         = ?,
    ficha_financeira= ?,
    macro_tema      = ?,
    priorizacao     = ?,
    prorrogavel     = ?,
    janeiro         = ?,
    fevereiro       = ?,
    marco           = ?,
    abril           = ?,
    maio            = ?,
    junho           = ?,
    julho           = ?,
    agosto          = ?,
    setembro        = ?,
    outubro         = ?,
    novembro        = ?,
    dezembro        = ?
  WHERE id = ?
";

$stmt = $poa->prepare($sql);
if (!$stmt) {
  die('Erro ao preparar UPDATE: ' . $poa->error);
}

// monta types/params dinamicamente pra não errar
$types  = '';
$params = [];

$add = function($type, $val) use (&$types, &$params) {
  $types  .= $type;
  $params[] = $val;
};

$add('s', $tema_custo);
$add('s', $setor);
$add('s', $gestor);
$add('s', $objeto);
$add('s', $status);
$add('s', $numero_contrato);
$add('s', $credor);
$add('s', $vigencia_inicio);
$add('s', $vigencia_fim);
$add('i', $dea);
$add('i', $reajuste);
$add('s', $fonte);
$add('s', $grupo);
$add('s', $sei);
$add('d', $valor_total);
$add('s', $acao);
$add('s', $subacao);
$add('s', $ficha_financeira);
$add('s', $macro_tema);
$add('s', $priorizacao);
$add('i', $prorrogavel);

foreach ($mesVals as $v) {
  $add('d', $v);
}

$add('i', $id);

$stmt->bind_param($types, ...$params);

if (!$stmt->execute()) {
  die('Erro ao atualizar contrato: ' . $stmt->error);
}

// tudo ok -> volta para a edição com flag de sucesso
header("Location: editar_contrato.php?id=" . $id . "&ok=1");
exit;
