<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// relatorio_poa.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../config.php';

// Garante login
if (!isset($_SESSION['usuario'])) {
  http_response_code(401);
  echo "Sessão não iniciada.";
  exit;
}

// ------- mesmas regras de admin do home.php -------
$nomeUsuario  = trim($_SESSION['usuario']['nome']  ?? 'usuário');
$loginUsuario = trim($_SESSION['usuario']['login'] ?? '');
$cargoUsuario = trim($_SESSION['usuario']['cargo'] ?? '');

$nomeCheck   = mb_strtolower($nomeUsuario, 'UTF-8');
$loginCheck  = mb_strtolower($loginUsuario, 'UTF-8');
$cargoCheck  = mb_strtolower($cargoUsuario, 'UTF-8');

$isAdmin = ($cargoCheck === 'gestor' || $loginCheck === 'bruno.passavante' || $nomeCheck === 'bruno passavante de oliveira');
$identUsuario = $nomeUsuario;

// Filtro base
$where  = "1";
$params = [];
$types  = "";

// se não for admin, filtra pelo usuário
if (!$isAdmin) {
    $where   .= " AND usuario_cehab = ?";
    $params[] = $identUsuario;
    $types   .= "s";
}

// (Opcional) permitir filtro por q, se quiser reaproveitar
$busca = trim($_GET['q'] ?? '');
if ($busca !== '') {
    $where .= " AND (numero_contrato LIKE ? OR credor LIKE ? OR objeto LIKE ?)";
    $like   = "%{$busca}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types   .= "sss";
}

$sql = "
  SELECT
    id,
    codigo_poa,
    usuario_cehab,
    tema_custo,
    setor,
    gestor,
    objeto,
    status_contrato,
    numero_contrato,
    credor,
    vigencia_fim,
    observacoes,
    dea,
    reajuste,
    fonte,
    grupo_despesa,
    sei,
    valor_total,
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
  FROM novo_contrato
  WHERE $where
  ORDER BY codigo_poa, id
";

$stmt = $poa->prepare($sql);
if (!$stmt) {
  http_response_code(500);
  echo "Erro ao preparar consulta: " . $poa->error;
  exit;
}

if ($params) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// ------- Cabeçalhos do download CSV -------
$filename = "relatorio_poa_" . date('Y-m-d_His') . ".csv";

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Pragma: no-cache');
header('Expires: 0');

// BOM para Excel reconhecer UTF-8
echo "\xEF\xBB\xBF";

$out = fopen('php://output', 'w');

function moedaBR($v) {
  if ($v === null || $v === '') return '';
  return number_format((float)$v, 2, ',', '.');
}

// Cabeçalho das colunas
$header = [
  'id',
  'codigo_poa',
  'usuario_cehab',
  'tema_custo',
  'setor',
  'gestor',
  'objeto',
  'status_contrato',
  'numero_contrato',
  'credor',
  'vigencia_fim',
  'observacoes',
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
  'updated_at'
];

fputcsv($out, $header, ';');

// Funçãozinha pra traduzir booleanos 0/1 em Sim/Não no CSV
$boolToSimNao = function($v) {
  if ($v === null || $v === '') return '';
  return ((int)$v === 1) ? 'Sim' : 'Não';
};

// Escreve linhas
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $linha = [
    $row['id'],
    $row['codigo_poa'],
    $row['usuario_cehab'],
    $row['tema_custo'],
    $row['setor'],
    $row['gestor'],
    $row['objeto'],
    $row['status_contrato'],
    $row['numero_contrato'],
    $row['credor'],
    $row['vigencia_fim'],
    $row['observacoes'],
    $boolToSimNao($row['dea']),
    $boolToSimNao($row['reajuste']),
    $row['fonte'],
    $row['grupo_despesa'],
    $row['sei'],
    moedaBR($row['valor_total']),
    $row['acao'],
    $row['subacao'],
    $row['ficha_financeira'],
    $row['macro_tema'],
    $row['priorizacao'],
    $boolToSimNao($row['prorrogavel']),
    moedaBR($row['janeiro']),
    moedaBR($row['fevereiro']),
    moedaBR($row['marco']),
    moedaBR($row['abril']),
    moedaBR($row['maio']),
    moedaBR($row['junho']),
    moedaBR($row['julho']),
    moedaBR($row['agosto']),
    moedaBR($row['setembro']),
    moedaBR($row['outubro']),
    moedaBR($row['novembro']),
    moedaBR($row['dezembro']),
    $row['created_at'],
    $row['updated_at'],
  ];


    // ; como separador
    fputcsv($out, $linha, ';');
  }
}

fclose($out);
exit;
