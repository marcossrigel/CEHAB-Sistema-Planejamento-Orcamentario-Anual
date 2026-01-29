<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (!isset($_SESSION['usuario'])) {
  http_response_code(401);
  exit("Sessão não iniciada.");
}

require_once __DIR__ . '/../config.php';

$nomeUsuario  = trim($_SESSION['usuario']['nome']  ?? 'usuário');
$loginUsuario = trim($_SESSION['usuario']['login'] ?? '');
$cargoUsuario = trim($_SESSION['usuario']['cargo'] ?? '');

$nomeCheck   = mb_strtolower($nomeUsuario, 'UTF-8');
$loginCheck  = mb_strtolower($loginUsuario, 'UTF-8');
$cargoCheck  = mb_strtolower($cargoUsuario, 'UTF-8');

$isAdmin = ($cargoCheck === 'gestor' || $loginCheck === 'bruno.passavante' || $nomeCheck === 'bruno passavante de oliveira');
$isBruno = ($loginCheck === 'bruno.passavante' || $nomeCheck === 'bruno passavante de oliveira');

$identUsuario = $nomeUsuario;

$busca = trim($_GET['q'] ?? '');

$where  = "1";
$params = [];
$types  = "";

// mesmo filtro do home
if (!$isAdmin) {
  $where .= " AND usuario_cehab = ?";
  $params[] = $identUsuario;
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

// Query igual ao home (pré-visualização)
if ($isBruno) {
  $sql = "
    SELECT
      id,
      codigo_poa,
      usuario_cehab,
      objeto,
      status_contrato,
      numero_contrato,
      credor,
      dea,
      reajuste,
      ficha_financeira,
      priorizacao,
      created_at,
      janeiro, fevereiro, marco, abril, maio, junho, julho, agosto, setembro, outubro, novembro, dezembro
    FROM (
      SELECT
        id,
        codigo_poa       COLLATE utf8mb4_general_ci AS codigo_poa,
        usuario_cehab    COLLATE utf8mb4_general_ci AS usuario_cehab,
        objeto           COLLATE utf8mb4_general_ci AS objeto,
        status_contrato  COLLATE utf8mb4_general_ci AS status_contrato,
        numero_contrato  COLLATE utf8mb4_general_ci AS numero_contrato,
        credor           COLLATE utf8mb4_general_ci AS credor,
        dea, reajuste,
        ficha_financeira COLLATE utf8mb4_general_ci AS ficha_financeira,
        priorizacao      COLLATE utf8mb4_general_ci AS priorizacao,
        created_at,
        janeiro, fevereiro, marco, abril, maio, junho, julho, agosto, setembro, outubro, novembro, dezembro
      FROM contrato_modificado

      UNION ALL

      SELECT
        id,
        codigo_poa       COLLATE utf8mb4_general_ci,
        usuario_cehab    COLLATE utf8mb4_general_ci,
        objeto           COLLATE utf8mb4_general_ci,
        status_contrato  COLLATE utf8mb4_general_ci,
        numero_contrato  COLLATE utf8mb4_general_ci,
        credor           COLLATE utf8mb4_general_ci,
        dea, reajuste,
        ficha_financeira COLLATE utf8mb4_general_ci,
        priorizacao      COLLATE utf8mb4_general_ci,
        created_at,
        janeiro, fevereiro, marco, abril, maio, junho, julho, agosto, setembro, outubro, novembro, dezembro
      FROM novo_contrato
    ) base
    WHERE $where
    ORDER BY created_at DESC, id DESC
  ";
} else {
  $sql = "
    SELECT
      id,
      codigo_poa,
      usuario_cehab,
      objeto,
      status_contrato,
      numero_contrato,
      credor,
      dea,
      reajuste,
      ficha_financeira,
      priorizacao,
      created_at,
      janeiro, fevereiro, marco, abril, maio, junho, julho, agosto, setembro, outubro, novembro, dezembro
    FROM novo_contrato
    WHERE $where
    ORDER BY created_at DESC, id DESC
  ";
}

$stmt = $poa->prepare($sql);
if ($stmt === false) {
  http_response_code(500);
  exit("Erro ao preparar: " . $poa->error);
}
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
$rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

// ---------- DOWNLOAD CSV ----------
$filename = 'pre_visualizacao_' . date('Y-m-d_H-i-s') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// BOM para Excel (acentos)
echo "\xEF\xBB\xBF";

$out = fopen('php://output', 'w');

// separador ; (Excel PT-BR costuma abrir melhor)
$sep = ';';

// Cabeçalho igual à tabela da tela (com Total)
fputcsv($out, [
  'CÓDIGO POA',
  'RESPONSÁVEL',
  'OBJETO/ATIVIDADE',
  'STATUS',
  'Nº DO CONTRATO',
  'CREDOR',
  'DEA',
  'REAJUSTE',
  'FICHA FINANCEIRA',
  'GRAU DE PRIORIZAÇÃO',
  'TOTAL',
  'DATA DE CRIAÇÃO'
], $sep);

// meses para total
$meses = ['janeiro','fevereiro','marco','abril','maio','junho','julho','agosto','setembro','outubro','novembro','dezembro'];

foreach ($rows as $r) {
  $total = 0.0;
  foreach ($meses as $m) $total += (float)($r[$m] ?? 0);

  $dt = '';
  if (!empty($r['created_at'])) {
    try { $dt = (new DateTime($r['created_at']))->format('d/m/Y'); } catch (Exception $e) {}
  }

  $dea = is_null($r['dea']) ? '' : (((int)$r['dea'] === 1) ? 'sim' : 'não');
  $reaj = is_null($r['reajuste']) ? '' : (((int)$r['reajuste'] === 1) ? 'sim' : 'não');

  fputcsv($out, [
    $r['codigo_poa'] ?: $r['id'],
    $r['usuario_cehab'] ?? '',
    $r['objeto'] ?? '',
    $r['status_contrato'] ?? '',
    $r['numero_contrato'] ?? '',
    $r['credor'] ?? '',
    $dea,
    $reaj,
    $r['f]()
