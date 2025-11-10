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

  <link rel="stylesheet" href="../assets/css/formulario.css">
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
          <select id="tema_custo" name="tema_custo" class="input select" required>
            <option value="">Selecione uma opção</option>
            <option>01 - Apoio Administrativo - Estagiários</option>
            <option>02 - Combustível/Manutenção de Veículos</option>
            <option>03 - Demandas Judiciais</option>
            <option>04 - Diárias Civil</option>
            <option>05 - Limpeza e Conservação</option>
            <option>06 - Locação de Veículos</option>
            <option>07 - Manutenção Predial</option>
            <option>08 - Material de Expediente/Copa/limpeza/Gráfico</option>
            <option>09 - Motoristas</option>
            <option>10 - Salário de Apenados</option>
            <option>11 - Rede Digital Corporativa do Estado</option>
            <option>12 - Serviços de Portaria</option>
            <option>13 - Serviços de Informática</option>
            <option>14 - Suprimento Individual</option>
            <option>15 - Vigilência Ostensiva</option>
            <option>16 - Auxílio Moradia</option>
            <option>17 - Cota Global</option>
            <option>18 - Passagnes Aéreas</option>
            <option>19 - Energia Elétrica</option>
            <option>20 - Água e Esgoto</option>
            <option>21 - Outros</option>
            <option>22 - Folha de Pessoal</option>
            <option>23 - FGTS</option>
            <option>24 - INSS</option>
            <option>25 - Ressarcimento Pessoal à Disposição</option>
            <option>26 - Obras</option>
            <option>27 - Gerenciamento de Obras</option>
            <option>28 - Projetos de Obras</option>
            <option>29 - Regularização Fundiária</option>
            <option>30 - Ouvidoria</option>
            <option>31 - Ouvidoria</option>
            <option>32 - Ouvidoria</option>
            <option>33 - Ouvidoria</option>
            <option>34 - Ouvidoria</option>
            <option>35 - Ouvidoria</option>
            <option>36 - Contribuições Patronais da CEHAB</option>
            <option>37 - Encargos com o PIS e o COFINS</option>
            <option>38 - Apoio Administrativo</option>
            <option>39 - Apoio Especializado</option>

          </select>
        </div>
        <div class="md:col-span-4">
          <label class="label" for="setor">Setor Responsável *</label>
          <select id="setor" name="setor" class="input select" required>
            <option value="">Selecione um setor</option>
            <option>DAF</option>
            <option>DOB</option>
            <option>DOE</option>
            <option>SPO</option>
            <option>DP (PESSOAL)</option>
            <option>DPH</option>
            <option>SAJ</option>
          </select>
        </div>
        <div class="md:col-span-4">
          <label class="label" for="gestor">Gestor Responsável</label>
          <input id="gestor" name="gestor" class="input" placeholder="Nome do gestor">
        </div>

        <div class="md:col-span-12">
          <hr class="my-4 border-slate-200">
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
              <select id="status" name="status" class="input select" required>
                  <option value="">Selecione...</option>
                  <option>Continuidade</option>
                  <option>Novo</option>
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
                <option value="">Selecione...</option>
                <option>Sim</option>
                <option>Não</option>
              </select>
            </div>
          </div>
        </div>

        <div class="md:col-span-2">
          <label class="label" for="fonte">Fonte</label>
          <select id="fonte" name="fonte" class="input select">
            <option value="">Selecione...</option>
            <option>0500 - (Tesouro do Estado)</option>
            <option>0700 - (Repasse de Convênio)</option>
            <option>0754 - (Operação de Crédito)</option>
          </select>
        </div>
        <div class="md:col-span-2">
          <label class="label" for="grupo">Grupo</label>
          <select id="grupo" name="grupo" class="input select">
            <option value="">Selecione...</option>
            <option>3 - Despesa Corrente</option>
            <option>4 - Investimentos</option>
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
          <label class="label" for="setor">Ação</label>
          <select id="acao" name="acao" class="input select">
            <option value="">Selecione...</option>
            <option>2904 - Formulação e Promoção da Política de Regularização Fundiária</option>
            <option>2928 - Conservação do Patrimonio Público na Companhia Estadual de Habitação e Obras - CHEAB</option>
            <option>2998 - Encargos Gerais da Companhia Estadual de Habitação e Obras - CEHAB</option>
            <option>3902 - Fomento e Apoio ao Conselho Estaudal de Habitação de Interesse Social - CEHAB</option>
            <option>3927 - Manutenção da Ouvidoria da Companhia Estadual de Habitação e Obras - CEHAB</option>
            <option>4058 - Ampliação da Oferta e Requalificação de Habitação de Interesse Social</option>
            <option>4300 - Execução de Obras de Infraestrutura e de Urbanização</option>
            <option>4301 - Pesquisa e Assessoria Técnica para Habitação de Interesse Social</option>
            <option>4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB</option>
            <option>4587 - Contribuições Patronais da CEHAB</option>
            <option>4613 - Encargos com o PIS e o COFINS da Companhia Estadual de Habitação e Obras - CEHAB</option>
          </select>

        </div>
        <div class="md:col-span-4">
          <label class="label" for="subacao">Subação</label>
          <select id="subacao" name="subacao" class="input select">
            <option value="">Selecione...</option>
            <option>0000 - Outras Medidas</option>
            <option>0055 - Programa Minha Casa (Operações Coletivas, CAIC, FNHIS e PSH) - Conclusão da construção de moradias</option>
            <option>0865 - Operacionalização do Programa Minha Casa Minha Vida</option>
            <option>1163 - Acompanhamento do cadastro de famílias beneficiadas pelo auxílio moradia </option>
            <option>1399 - Execução de obras de infraestrutura e construção de unidades habitacionais na comunidade de Escorregou Tá Dentro (Afogrados - Recife)</option>
            <option>1400 - Execução de obras de infraestrutura e construção de unidades habitacionais na comunidade de Mulheres de Tejucupapo (Iputinga - Recife)</option>
            <option>2067 - Obras e Projetos da Vila Claudete</option>
            <option>2217 - Execução das obras de implantação de adutora de recalque do Loteamento Snta Clara - Barreiros/PE</option>
            <option>2409 - Entrada Garantida - Programa Morar Bem</option>
            <option>2787 - Contribuições Patronais da CEHAB ao FUNAFIN</option>
            <option>2790 - Manutenção da Tecnologia de Informação e Comunicação da CEHAB</option>
            <option>2791 - Fornecimento de vale transporte para servidores da CEHAB</option>
            <option>2792 - Fornecimento de vale alimentação para servidores da CEHAB</option>
            <option>2793 - Regularização Fundiária e Oferta de Lotes Urbanos com Interesse Social</option>
            <option>2794 - Auxílio Moradia - CEHAB</option>
            <option>2885 - Reforma no Lar - PROGRAMA MORAR BEM PE</option>
            <option>3242 - Execução das obras de pavimentação, drenagem e sinalização da estrada Lygia Gomes da Silva - Ouro Preto</option>
            <option>3325 - Obras não incidentes - FAR e FDS</option>
            <option>3352 - Programa Morar Bem - Construção de Unidades Habitacionais</option>
            <option>A386 - Execução de obras de infraestrutura e construção de unidades habitacionais na Bacia do Fregoso II</option>
            <option>A389 - Execução de obras de infraestrutura e construção de unidades habitacionais no Canal do Jordão</option>
            <option>A401 - Execução de obras de infraestrutura e construção de unidades habitacionais em Azeitona(UE11) e Peixinhos (UE12)</option>
            <option>B156 - Construção da Via Metropolitana Norte (Fragoso - viaduto da PE-15/revestimento do canal/viário até Janga)</option>
            <option>B661 - Despesas com taxa de água e esgoto da CEHAB</option>
            <option>B662 - Despesas com combustível da CEHAB</option>
            <option>B664 - Despesas com tarifa de energia </option>
            <option>B665 - Prestação de serviços de limpeza e conservação da CEHAB</option>
            <option>B666 - Despesas com locação de veículos da CEHAB</option>
            <option>B667 - Prestação de serviços de motorista na CEHAB</option>
            <option>B668 - Despesas com publicação oficiais de CEHAB em diário oficial</option>
            <option>B669 - Pagamento de apenados em processo de ressocialização na CEHAB</option>
            <option>B670 - Prestação de serviços de segurança pessoal e patrimonial na CEHAB </option>

          </select>
        </div>

        <!-- L5: Ficha Financeira | Macro Tema | Priorização | É Prorrogável? -->
        <div class="md:col-span-4">
          <label class="label" for="ficha_financeira">Ficha Financeira</label>
          <select id="ficha_financeira" name="ficha_financeira" class="input">
            <option value="">Selecione...</option>
            <option>G3 - Água e Esgoto</option>
            <option>G3 - Apoio Administrativo - Estagiários</option>
            <option>G3 - Auxílio Funeral</option>
            <option>G3 - Auxílio Moradia</option>
            <option>G3 - Auxílio Moradia - Operação Prontidão</option>
            <option>G3 - Combustíveis/Manutenção/ Veículos</option>
            <option>G3 - Cota Global</option>
            <option>G3 - Demandas Judiciais</option>
            <option>G4 - Devolução - Recursos do Concedente</option>
            <option>G3 - Diárias Civil</option>
            <option>G3 - Energia Elétrica</option>
            <option>G1 - FGTS</option>
            <option>G3 - Fornecimento de Passagens</option>
            <option>G1 - INSS</option>
            <option>G3 - Limpeza e Conservação</option>
            <option>G3 - Locação de Veículos</option>
            <option>G3 - Manutenção Predial</option>
            <option>G3 - Material de Expediente/Copa/Limpeza/Gráfico</option>
            <option>G3 - Motoristas</option>
            <option>G4 - Obra</option>
            <option>G4 - Operações de Crédito</option>
            <option>G4 - Outros</option>
            <option>G3 - Outros</option>
            <option>G1 - Pessoal e Encargos Sociais</option>
            <option>G3 - Publicação Oficiais</option>
            <option>G4 - Recursos do Concedente</option>
            <option>G3 - Rede Digital Corporativa do Estado</option>
            <option>G1 - Ressarcimento Pessoal à Disposição</option>
            <option>G3 - Salário de Apenados</option>
            <option>G3 - Serviços de Informática</option>
            <option>G3 - Serviços de Portaria</option>
            <option>G3 - Suprimento Individual</option>
            <option>G3 - Vigilância Ostensiva</option>
            <option>G4 - Supervisão de Obra</option>
          </select>
        </div>

        <div class="md:col-span-12">
          <hr class="my-4 border-slate-200">
        </div>

        <div class="md:col-span-3">
          <label class="label" for="macro_tema">Macro Tema</label>
          <select id="macro_tema" name="macro_tema" class="input select">
            <option value="">Selecione...</option>
            <option>Suporte a Gestão</option>
            <option>Mão de Obra Terceirizada</option>
            <option>Outros</option>
            <option>Auxílio</option>
            <option>Frota</option>
            <option>Convênio</option>
            <option>Pessoal e Encargos Sociais</option>
            <option>Obras</option>
            <option>Operações de Crédito</option>
          </select>
        </div>
        <div class="md:col-span-3">
          <label class="label" for="priorizacao">Grau de Priorização</label>
          <select id="priorizacao" name="priorizacao" class="input select" required>
            <option value="">Selecione...</option>
            <option>Grau Alto</option>
            <option>Grau Médio/Alto</option>
            <option>Grau Médio</option>
            <option>Grau Médio/Baixo</option>
            <option>Grau Baixo</option>
          </select>
        </div>
        <div class="md:col-span-2">
          <label class="label" for="prorrogavel">É Prorrogável?</label>
          <select id="prorrogavel" name="prorrogavel" class="input select">
            <option value="">Selecione...</option>
            <option>Sim</option>
            <option>Não</option>
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

  <script src="../js/formulario.js"></script>
</body>
</html>
