<?php /* templates/formulario.php */ ?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Novo Contrato • POA</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <script src="https://cdn.tailwindcss.com"></script>

  <link rel="stylesheet" href="../assests/css/formulario.css">
</head>
<body class="min-h-screen">
  <!-- Topbar -->
  <header class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="grid place-items-center w-9 h-9 rounded-xl bg-blue-600 text-white shadow-sm">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M3 21h18v-2H3v2Zm14-4h2V3h-6v4H5v10h2v-8h4v8h2v-4h4v4Zm-4-6V5h2v6h-2Z"/></svg>
        </div>
        <div class="flex flex-col">
          <span class="text-slate-900 font-semibold leading-none">POA - Planejamento Orçamentário Anual</span>
          <span class="text-xs text-slate-500 leading-none">Novo Contrato</span>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <a href="home.php" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Voltar</a>
      </div>
    </div>
  </header>

  <!-- Form container -->
  <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
    <form class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-8" action="salvar_contrato.php" method="post" id="formContrato">
      <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-900">Formulário POA 2025</h1>
        <span class="text-xs text-slate-500">Campos marcados com * são obrigatórios</span>
      </div>

      <section class="grid grid-cols-1 md:grid-cols-12 gap-5">
        <!-- L1: Tema de Custo | Setor | Gestor -->
        <div class="md:col-span-4">
          <label class="label" for="tema_custo">Tema de Custo *</label>
          <select id="tema_custo" name="tema_custo" class="input select">
            <option value="">Selecione uma opção</option>
            <option>01 - Obras</option><option>02 - Serviços</option><option>21 - Outros</option>
          </select>
        </div>
        <div class="md:col-span-4">
          <label class="label" for="setor">Setor Responsável *</label>
          <select id="setor" name="setor" class="input select">
            <option value="">Selecione um setor</option>
            <option>GOP - Orçamento e Planejamento</option>
            <option>GEFIN - Financeiro</option>
            <option>CELOE II</option>
          </select>
        </div>
        <div class="md:col-span-4">
          <label class="label" for="gestor">Gestor Responsável</label>
          <input id="gestor" name="gestor" class="input" placeholder="Nome do gestor">
        </div>

        <!-- L2 + L3 (Objeto à esquerda; à direita 2 linhas: Status/Nº/ Credor e Vigência/DEA/Reajuste) -->
        <div class="md:col-span-4">
          <label class="label" for="objeto">Objeto / Atividade</label>
          <textarea id="objeto" name="objeto" class="input min-h-[140px]" placeholder="Descreva o objeto ou atividade"></textarea>
        </div>

        <div class="md:col-span-8">
          <div class="grid md:grid-cols-8 gap-x-5 gap-y-1.5">
            <div class="md:col-span-4">
              <label class="label" for="status">Status</label>
              <select id="status" name="status" class="input select">
                <option>Planejado</option><option>Em andamento</option><option>Concluído</option>
              </select>
            </div>
            <div class="md:col-span-2">
              <label class="label" for="numero_contrato">Nº Contrato</label>
              <input id="numero_contrato" name="numero_contrato" class="input" placeholder="0000/0000">
            </div>
            <div class="md:col-span-2">
              <label class="label" for="credor">Credor</label>
              <input id="credor" name="credor" class="input" placeholder="Nome do credor">
            </div>

            <div class="md:col-span-4">
              <label class="label" for="vigencia">Vigência</label>
              <input id="vigencia" name="vigencia" class="input" placeholder="mm/aaaa - mm/aaaa">
            </div>
            <div class="md:col-span-2">
              <label class="label" for="dea">DEA</label>
              <select id="dea" name="dea" class="input select">
                <option value="">Selecione...</option><option>Sim</option><option>Não</option>
              </select>
            </div>
            <div class="md:col-span-2">
              <label class="label" for="reajuste">Reajuste</label>
              <select id="reajuste" name="reajuste" class="input select">
                <option>Sem reajuste</option><option>Anual</option><option>Outro</option>
              </select>
            </div>
          </div>
        </div>

        <!-- L3b: Fonte | Grupo | Nº SEI -->
        <div class="md:col-span-2">
          <label class="label" for="fonte">Fonte</label>
          <select id="fonte" name="fonte" class="input select">
            <option>0500 - Tesouro do Estado</option><option>0600 - Convênio</option>
          </select>
        </div>
        <div class="md:col-span-2">
          <label class="label" for="grupo">Grupo</label>
          <select id="grupo" name="grupo" class="input select">
            <option>3 - Despesa Corrente</option><option>4 - Despesa de Capital</option>
          </select>
        </div>
        <div class="md:col-span-2">
          <label class="label" for="sei">Número do SEI</label>
          <input id="sei" name="sei" class="input" placeholder="0000000-00.0000.0.00.0000">
        </div>

        <!-- L4: Valor do Contrato | Ação | Subação -->
        <div class="md:col-span-4">
          <label class="label" for="valor_total">Valor Total do Contrato *</label>
          <input id="valor_total" name="valor_total" class="input moeda" placeholder="R$ 0,00" inputmode="numeric">
        </div>
        <div class="md:col-span-4">
          <label class="label" for="acao">Ação</label>
          <input id="acao" name="acao" class="input" placeholder="Ex.: 4354 - Gestão ...">
        </div>
        <div class="md:col-span-4">
          <label class="label" for="subacao">Subação</label>
          <input id="subacao" name="subacao" class="input" placeholder="Ex.: B667 - Prestação ...">
        </div>

        <!-- L5: Ficha Financeira | Macro Tema | Priorização | É Prorrogável? -->
        <div class="md:col-span-4">
          <label class="label" for="ficha_financeira">Ficha Financeira</label>
          <input id="ficha_financeira" name="ficha_financeira" class="input" placeholder="Ex.: G3 - Motoristas">
        </div>
        <div class="md:col-span-3">
          <label class="label" for="macro_tema">Macro Tema</label>
          <select id="macro_tema" name="macro_tema" class="input select">
            <option>Selecione uma opção</option><option>Infraestrutura</option><option>Social</option>
          </select>
        </div>
        <div class="md:col-span-3">
          <label class="label" for="priorizacao">Grau de Priorização</label>
          <select id="priorizacao" name="priorizacao" class="input select">
            <option>Baixo</option><option>Médio</option><option>Alto</option>
          </select>
        </div>
        <div class="md:col-span-2">
          <label class="label" for="prorrogavel">É Prorrogável?</label>
          <select id="prorrogavel" name="prorrogavel" class="input select">
            <option value="">Selecione</option><option>Sim</option><option>Não</option>
          </select>
        </div>
      </section>

      <!-- Tabela de Meses -->
      <section class="space-y-3">
        <h2 class="text-base font-semibold text-slate-900">Tabela de Meses</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
          <?php $meses=['JAN','FEV','MAR','ABR','MAI','JUN','JUL','AGO','SET','OUT','NOV','DEZ']; foreach($meses as $i=>$m): ?>
            <div class="space-y-1">
              <div class="label"><?= $m ?></div>
              <input type="text" inputmode="numeric" name="mes[<?= $i ?>]" class="input moeda" placeholder="R$ 0,00">
            </div>
          <?php endforeach; ?>
        </div>
        <div class="mt-3 rounded-xl bg-sky-50 border border-sky-100 px-4 py-3 flex items-center justify-between">
          <span class="text-sm text-slate-700 font-medium">Total:</span>
          <strong class="text-slate-900" id="totalMeses">R$ 0,00</strong>
        </div>
      </section>

      <div class="flex items-center justify-end gap-3 pt-2">
        <button type="button" onclick="history.back()" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2 text-slate-700 text-sm hover:bg-slate-50">Cancelar</button>
        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-white text-sm font-medium shadow hover:bg-blue-700">Salvar Contrato</button>
      </div>
    </form>
  </main>

  <script>
    const fmt = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
    const parseMoeda = v => (v||'').replace(/[^0-9]/g,'')/100 || 0;
    function formatar(e){ e.target.value = fmt.format(parseMoeda(e.target.value)); somar(); }
    function somar(){
      let total = 0;
      document.querySelectorAll('.moeda').forEach(i => total += parseMoeda(i.value));
      document.getElementById('totalMeses').textContent = fmt.format(total);
    }
    document.querySelectorAll('.moeda').forEach(i => i.addEventListener('input', formatar));
    document.getElementById('valor_total')?.addEventListener('input', formatar);
  </script>
</body>
</html>
