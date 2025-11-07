<?php
// index.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/config.php';  // usa sua conexão (remota ou local)

$token = $_GET['token'] ?? '';
$token = trim($token);

if ($token === '') {
  http_response_code(400);
  echo 'Token ausente. Use: index.php?token=...';
  exit;
}

// Busca o usuário correspondente ao token.
// Junta token_sessao.u_rede com users.u_rede (ou pode usar g_id se preferir).
$sql = "
  SELECT u.u_rede, u.u_nome_completo, u.u_email, u.u_cargo
  FROM token_sessao t
  JOIN users u ON u.u_rede = t.u_rede
  WHERE t.token = ?
  ORDER BY t.data_hora DESC
  LIMIT 1
";

$stmt = $conexao->prepare($sql);
if (!$stmt) {
  http_response_code(500);
  echo "Erro ao preparar consulta: " . $conexao->error;
  exit;
}
$stmt->bind_param('s', $token);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
  // Token inválido/expirado
  http_response_code(401);
  ?>
  <!doctype html>
  <html lang="pt-br">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Token inválido</title>
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="min-h-screen bg-slate-50 grid place-items-center p-6">
    <div class="bg-white border border-slate-200 rounded-2xl p-8 text-center max-w-md shadow-sm">
      <h1 class="text-xl font-semibold text-slate-800 mb-2">Token inválido ou expirado</h1>
      <p class="text-slate-600 mb-6">Verifique o link recebido e tente novamente.</p>
      <a href="templates/home.php"
         class="inline-flex items-center px-5 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700">Ir para a Home</a>
    </div>
  </body>
  </html>
  <?php
  exit;
}

// Achou o usuário
$user = $res->fetch_assoc();
$_SESSION['usuario'] = [
  'rede'  => $user['u_rede'],
  'nome'  => $user['u_nome_completo'],
  'email' => $user['u_email'],
  'cargo' => $user['u_cargo'] ?? null,
  'token' => $token,
  'auth_at' => date('Y-m-d H:i:s'),
];

// vai para a Home
header('Location: templates/home.php');
exit;
