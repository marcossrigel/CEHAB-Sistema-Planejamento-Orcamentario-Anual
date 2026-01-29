<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (!isset($_SESSION['usuario'])) {
  http_response_code(401);
  echo "Sessão não iniciada. Acesse via index.php?token=...";
  exit;
}

require_once __DIR__ . '/../config.php';

$nomeUsuario  = trim($_SESSION['usuario']['nome']  ?? 'usuário');
$loginUsuario = trim($_SESSION['usuario']['login'] ?? '');
$cargoUsuario = trim($_SESSION['usuario']['cargo'] ?? '');

$nomeCheck  = mb_strtolower($nomeUsuario, 'UTF-8');
$loginCheck = mb_strtolower($loginUsuario, 'UTF-8');
$cargoCheck = mb_strtolower($cargoUsuario, 'UTF-8');

$isAdmin = ($cargoCheck === 'gestor' || $loginCheck === 'bruno.passavante' || $nomeCheck === 'bruno passavante de oliveira');
$isBruno = ($loginCheck === 'bruno.passavante' || $nomeCheck === 'bruno passavante de oliveira');

// ✅ Segurança extra: este relatório é só do Bruno
if (!$isBruno) {
  http_response_code(403);
  echo "Acesso negado.";
  exit;
}

$busca = trim($_GET['q'] ?? '');

$where  = "1";
$params = [];
$types  = "";

// Se não for admin, filtra por usuario_cehab
if (!$isAdmin) {
  $where .= " AND usuario_cehab = ?";
  $params[] = $nomeUsuario;
  $types   .= "s";
}

if ($busca !== '') {
  $where .= " AND (numero_contrato LIKE ? OR credor LIKE ? OR objeto LIKE ?)";
  $like = "%{$busca}%";
  $params[] = $like;
  $params[] = $like;
  $params[] = $like;
  $types   .= "sss";
}

/**
 * ✅ TODAS as colunas do print
 */
$sql = "
  SELECT
    id,
    codigo_poa,
    usuario_cehab,
    tema_custo,
    setor,
    gestor,
    objeto,
    observacoes,
    status_contrato,
    numero_contrato,
    credor,
    vigencia_fim,
    dea,
    reajuste,
    fonte,
    grupo_despesa,
    sei,
    valor_total_contrato,
    acao,
    subacao,
    ficha_financeira,
    macro_tema,
    priorizacao,
    prorrogavel,
    janeiro,
    fevereiro,
    marco,
    abril,
    maio,
    junho,
    julho,
    agosto,
    setembro,
    outubro,
    novembro,
    dezembro,
    created_at,
    updated_at
  FROM contrato_modificado
  WHERE $where
  ORDER BY created_at DESC
";

$stmt = $poa->prepare($sql);
if ($stmt === false) {
  http_response_code(500);
  echo "Erro ao preparar consulta: " . $poa->error;
  exit;
}
if ($params) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();
$rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

// helpers
function csv_out($v) {
  return (string)($v ?? '');
}

/**
 * Converte strings tipo:
 * "R$ 1.234,56" | "1.234,56" | "1234,56" | "1234.56" | "" -> float
 */
function money_to_float($v) {
  $s = trim((string)($v ?? ''));
  if ($s === '') return 0.0;

  $s = str_replace(['R$', ' ', "\u{00A0}"], '', $s); // remove R$ e espaços
  // se tiver vírgula, assume pt-BR: remove pontos de milhar e troca vírgula por ponto
  if (strpos($s, ',') !== false) {
    $s = str_replace('.', '', $s);
    $s = str_replace(',', '.', $s);
  }
  // mantém só números, sinal e ponto
  $s = preg_replace('/[^0-9\.\-]/', '', $s);
  if ($s === '' || $s === '-' || $s === '.') return 0.0;

  return (float)$s;
}

function fmt_data($v) {
  if (!$v) return '';
  // seus campos são varchar(50), então só tenta formatar se parecer data
  try {
    return (new DateTime($v))->format('d/m/Y H:i');
  } catch (Throwable $e) {
    return (string)$v;
  }
}

// Monta CSV
$filename = 'relatorio_contrato_modificado_' . date('Y-m-d_H-i-s') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');

// BOM UTF-8 pro Excel PT-BR abrir acento certinho
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

// Cabeçalho (todas as colunas + TOTAL calculado)
fputcsv($out, [
  'id',
  'codigo_poa',
  'usuario_cehab',
  'tema_custo',
  'setor',
  'gestor',
  'objeto',
  'observacoes',
  'status_contrato',
  'numero_contrato',
  'credor',
  'vigencia_fim',
  'dea',
  'reajuste',
  'fonte',
  'grupo_despesa',
  'sei',
  'valor_total_contrato',
  'acao',
  'subacao',
  'ficha_financeira',
  'macro_tema',
  'priorizacao',
  'prorrogavel',
  'janeiro',
  'fevereiro',
  'marco',
  'abril',
  'maio',
  'junho',
  'julho',
  'agosto',
  'setembro',
  'outubro',
  'novembro',
  'dezembro',
  'created_at',
  'updated_at',
  'total_meses'
], ';');

// Linhas
$meses = ['janeiro','fevereiro','marco','abril','maio','junho','julho','agosto','setembro','outubro','novembro','dezembro'];

foreach ($rows as $r) {
  $total = 0.0;
  foreach ($meses as $m) {
    $total += money_to_float($r[$m] ?? '');
  }

  fputcsv($out, [
    csv_out($r['id']),
    csv_out($r['codigo_poa']),
    csv_out($r['usuario_cehab']),
    csv_out($r['tema_custo']),
    csv_out($r['setor']),
    csv_out($r['gestor']),
    csv_out($r['objeto']),
    csv_out($r['observacoes']),
    csv_out($r['status_contrato']),
    csv_out($r['numero_contrato']),
    csv_out($r['credor']),
    csv_out($r['vigencia_fim']),
    csv_out($r['dea']),
    csv_out($r['reajuste']),
    csv_out($r['fonte']),
    csv_out($r['grupo_despesa']),
    csv_out($r['sei']),
    csv_out($r['valor_total_contrato']),
    csv_out($r['acao']),
    csv_out($r['subacao']),
    csv_out($r['ficha_financeira']),
    csv_out($r['macro_tema']),
    csv_out($r['priorizacao']),
    csv_out($r['prorrogavel']),
    csv_out($r['janeiro']),
    csv_out($r['fevereiro']),
    csv_out($r['marco']),
    csv_out($r['abril']),
    csv_out($r['maio']),
    csv_out($r['junho']),
    csv_out($r['julho']),
    csv_out($r['agosto']),
    csv_out($r['setembro']),
    csv_out($r['outubro']),
    csv_out($r['novembro']),
    csv_out($r['dezembro']),
    fmt_data($r['created_at']),
    fmt_data($r['updated_at']),
    number_format($total, 2, ',', '.'),
  ], ';');
}

fclose($out);
exit;
