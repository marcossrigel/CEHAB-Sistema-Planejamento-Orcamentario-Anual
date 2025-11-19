<?php
// templates/excluir_contrato.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Garante que só quem está logado consegue excluir
if (!isset($_SESSION['usuario'])) {
  http_response_code(401);
  echo "Sessão não iniciada. Acesse via login.";
  exit;
}

require_once __DIR__ . '/../config.php'; // mesma conexão $poa usada no sistema

// Pega o ID pela URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
  // ID inválido
  header('Location: home.php?erro=ID_INVAL');
  exit;
}

// Exclui o contrato
$stmt = $poa->prepare("DELETE FROM novo_contrato WHERE id = ?");
if (!$stmt) {
  // erro na preparação
  header('Location: home.php?erro=PREP_DEL');
  exit;
}

$stmt->bind_param('i', $id);
$stmt->execute();

// Se quiser, pode checar se realmente apagou:
if ($stmt->affected_rows > 0) {
  // apagou
  header('Location: home.php?msg=EXCLUIDO');
} else {
  // não encontrou o registro
  header('Location: home.php?erro=NAO_ENCONTRADO');
}
exit;
