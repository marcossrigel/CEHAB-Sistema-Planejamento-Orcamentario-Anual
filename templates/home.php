<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Se quiser forçar login por token:
if (!isset($_SESSION['usuario'])) {
  http_response_code(401);
  echo "Sessão não iniciada. Acesse via index.php?token=...";
  exit;
}

$nomeUsuario = $_SESSION['usuario']['nome'] ?? 'usuário';

// Conexão com o banco (mesmo config do salvar_contrato.php)
require_once __DIR__ . '/../config.php'; // aqui precisa existir $poa (mysqli)

// --------- BUSCA / LISTAGEM ---------
$busca = trim($_GET['q'] ?? '');

$where = '1';
$params = [];
$types  = '';

if ($busca !== '') {
  $where .= " AND (numero_contrato LIKE ? OR credor LIKE ? OR objeto LIKE ?)";
  $like = "%{$busca}%";
  $params = [$like, $like, $like];
  $types  = 'sss';
}

$sql = "
  SELECT
    id,
    objeto,
    status_contrato,
    numero_contrato,
    credor,
    dea,
    reajuste,
    ficha_financeira,
    priorizacao,
    created_at
  FROM novo_contrato
  WHERE $where
  ORDER BY created_at DESC
";

$stmt = $poa->prepare($sql);
if ($stmt === false) {
  die("Erro ao preparar consulta: " . $poa->error);
}

if ($params) {
  $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$res = $stmt->get_result();
$contratos = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

function bool_ptbr_view($v) {
  if (is_null($v)) return '';
  return (int)$v === 1 ? 'sim' : 'não';
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>POA - Planejamento Orçamentário Anual</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    :root {
      --bg: #f6f8fb;
    }
    html, body { height: 100%; }
    body {
      background: var(--bg);
      font-family: 'Poppins', system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
    }
  </style>
</head>
<body class="min-h-screen">
  <!-- Topbar -->
  <header class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
      <!-- Left / Brand -->
      <div class="flex items-center gap-3">
        <div class="grid place-items-center w-9 h-9 rounded-xl bg-blue-600 text-white shadow-sm">
          <!-- building icon -->
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M3 21h18v-2H3v2Zm14-4h2V3h-6v4H5v10h2v-8h4v8h2v-4h4v4Zm-4-6V5h2v6h-2Z"/></svg>
        </div>
        <div class="flex flex-col">
          <span class="text-slate-900 font-semibold leading-none">POA - Planejamento Orçamentário Anual</span>
          <span class="text-xs text-slate-500 leading-none">Painel inicial</span>
        </div>
      </div>

      <!-- Right / Actions -->
      <div class="flex items-center gap-3">
        <a href="formulario.php" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-white text-sm font-medium shadow hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path d="M11 11V6h2v5h5v2h-5v5h-2v-5H6v-2h5Z"/></svg>
          Novo Contrato
        </a>

        <a href="sair.php"
          class="inline-flex items-center gap-2 rounded-xl border border-red-300 px-4 py-2 text-red-600 bg-white text-sm font-medium hover:bg-red-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-400"
          title="Encerrar sessão">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
            <path d="M16 13v-2H7V8l-5 4 5 4v-3h9Zm3-10H8a2 2 0 0 0-2 2v3h2V5h11v14H8v-3H6v3a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2Z"/>
          </svg>
          Sair
        </a>
      </div>
    </div>
  </header>

  <!-- Content -->
  <main class="mx-auto w-full max-w-[1700px] px-4 sm:px-6 lg:px-8 py-8">
    <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 sm:p-6">
      <div class="flex flex-wrap items-center gap-3 text-sm text-slate-600">
        <div class="flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 text-slate-500"><path d="M12 12c2.21 0 4-1.79 4-4S14.21 4 12 4 8 5.79 8 8s1.79 4 4 4Zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4Z"/></svg>
          <span class="font-medium">Olá, seja bem-vindo:</span><strong><?= htmlspecialchars($nomeUsuario) ?></strong>
        </div>
      </div>

      <h2 class="mt-4 text-lg sm:text-xl font-semibold text-slate-900">Contratos em Andamento</h2>

      <!-- Filtro de busca -->
      <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
          <span class="absolute inset-y-0 left-0 flex items-center pl-3">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-slate-400"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5ZM9.5 14C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14Z"/></svg>
          </span>
          <input
            id="busca"
            type="text"
            class="w-full rounded-xl border border-slate-300 bg-white pl-11 pr-3 py-2.5 text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="Digite o nº do contrato/processo ou parte do credor"
            value="<?= htmlspecialchars($busca) ?>"
          >
        </div>
        <div class="flex gap-2">
          <button id="btnPesquisar" class="inline-flex items-center justify-center rounded-xl bg-blue-600 text-white text-sm font-medium px-4 py-2.5 shadow hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">Pesquisar</button>
          <button id="btnLimpar" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white text-slate-700 text-sm font-medium px-4 py-2.5 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-300">Limpar</button>
        </div>
      </div>

      <!-- Tabela / Planilha -->
      <?php if (empty($contratos)): ?>
        <div class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-slate-50/50 p-8 text-center">
          <p class="text-slate-600 text-sm">
            Nenhum contrato listado no momento.
            Use <span class="font-semibold">Pesquisar</span> ou clique em
            <span class="font-semibold">+ Novo Contrato</span> para cadastrar.
          </p>
        </div>
      <?php else: ?>
        <div class="mt-6 overflow-x-auto">
          <table class="min-w-full text-sm border border-slate-200 rounded-xl overflow-hidden">
            <thead class="bg-slate-50">
              <tr class="text-left text-xs font-semibold text-slate-600 uppercase">
                <th class="px-3 py-2 border-b border-slate-200">Código POA</th>
                <th class="px-3 py-2 border-b border-slate-200">Objeto/Atividade</th>
                <th class="px-3 py-2 border-b border-slate-200">Status</th>
                <th class="px-3 py-2 border-b border-slate-200">Nº do Contrato</th>
                <th class="px-3 py-2 border-b border-slate-200">Credor</th>
                <th class="px-3 py-2 border-b border-slate-200">DEA</th>
                <th class="px-3 py-2 border-b border-slate-200">Reajuste</th>
                <th class="px-3 py-2 border-b border-slate-200">Ficha Financeira</th>
                <th class="px-3 py-2 border-b border-slate-200">Grau de Priorização</th>
                <th class="px-3 py-2 border-b border-slate-200">Data de Criação</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <?php foreach ($contratos as $c): ?>
                <?php
                  $dataCriacao = '';
                  if (!empty($c['created_at'])) {
                    $dt = new DateTime($c['created_at']);
                    $dataCriacao = $dt->format('d/m/Y');
                  }
                ?>
                <tr class="hover:bg-slate-50">
                  <td class="px-3 py-2 text-sky-700 font-semibold whitespace-nowrap">
                    <?= htmlspecialchars($c['id']) ?>
                  </td>
                  <td class="px-3 py-2 max-w-xs truncate" title="<?= htmlspecialchars($c['objeto']) ?>">
                    <?= htmlspecialchars($c['objeto']) ?>
                  </td>
                  <td class="px-3 py-2 whitespace-nowrap">
                    <?= htmlspecialchars($c['status_contrato']) ?>
                  </td>
                  <td class="px-3 py-2 whitespace-nowrap">
                    <?= htmlspecialchars($c['numero_contrato']) ?>
                  </td>
                  <td class="px-3 py-2 whitespace-nowrap">
                    <?= htmlspecialchars($c['credor']) ?>
                  </td>
                  <td class="px-3 py-2 text-center">
                    <?= bool_ptbr_view($c['dea']) ?>
                  </td>
                  <td class="px-3 py-2 text-center">
                    <?= bool_ptbr_view($c['reajuste']) ?>
                  </td>
                  <td class="px-3 py-2 whitespace-nowrap">
                    <?= htmlspecialchars($c['ficha_financeira']) ?>
                  </td>
                  <td class="px-3 py-2 whitespace-nowrap">
                    <?= htmlspecialchars($c['priorizacao']) ?>
                  </td>
                  <td class="px-3 py-2 whitespace-nowrap">
                    <?= $dataCriacao ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>
  </main>

  <script>
    // botão limpar: limpa campo e volta para home sem parâmetro q
    document.getElementById('btnLimpar')?.addEventListener('click', () => {
      window.location.href = 'home.php';
    });

    // botão pesquisar: recarrega passando ?q=...
    document.getElementById('btnPesquisar')?.addEventListener('click', () => {
      const i = document.getElementById('busca');
      const q = i ? i.value.trim() : '';
      const url = new URL(window.location.href);
      if (q) {
        url.searchParams.set('q', q);
      } else {
        url.searchParams.delete('q');
      }
      window.location.href = url.toString();
    });

    // Enter no campo busca = pesquisar
    document.getElementById('busca')?.addEventListener('keyup', (e) => {
      if (e.key === 'Enter') {
        document.getElementById('btnPesquisar')?.click();
      }
    });
  </script>
</body>
</html>
