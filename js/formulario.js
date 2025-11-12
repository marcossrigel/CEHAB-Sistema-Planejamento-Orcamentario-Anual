// js/formulario.js (versão ajustada)
document.addEventListener('DOMContentLoaded', () => {
  // ====== utilidades de moeda ======
  const fmt = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
  const parseMoeda = v => (v || '').replace(/[^0-9]/g, '') / 100 || 0;

  function somar() {
    let total = 0;
    document.querySelectorAll('.moeda').forEach(i => total += parseMoeda(i.value));
    const totalEl = document.getElementById('totalMeses');
    if (totalEl) totalEl.textContent = fmt.format(total);
  }

  function formatar(e) {
    e.target.value = fmt.format(parseMoeda(e.target.value));
    somar();
  }

  // liga as máscaras/soma
  document.querySelectorAll('.moeda').forEach(i => i.addEventListener('input', formatar));
  const valorTotal = document.getElementById('valor_total');
  if (valorTotal) valorTotal.addEventListener('input', formatar);

  // ====== destaque visual (flash azul) ======
  function flashSelect(el) {
    if (!el) return;
    el.classList.add('ring-2','ring-sky-400','bg-sky-50','transition-colors','duration-700');
    const label = document.querySelector(`label[for="${el.id}"]`);
    if (label) label.classList.add('text-sky-700');
    setTimeout(() => {
      el.classList.remove('ring-2','ring-sky-400','bg-sky-50');
      if (label) label.classList.remove('text-sky-700');
    }, 1200);
  }

  // ====== helpers ======
  const norm = s => (s || '')
    .toString()
    .normalize('NFD')
    .replace(/\p{Diacritic}/gu, '')
    .toLowerCase()
    .trim();

  function pickOption(selectEl, queries) {
    if (!selectEl) return false;
    const prev = selectEl.value;
    const opts = Array.from(selectEl.options);
    for (const q of (Array.isArray(queries) ? queries : [queries])) {
      const qn = norm(q);
      let found = opts.find(o => norm(o.text) === qn)
             ||    opts.find(o => norm(o.text).startsWith(qn))
             ||    opts.find(o => norm(o.text).includes(qn));
      if (found) {
        selectEl.value = found.text; // seus <option> usam o próprio texto como value
        const changed = (selectEl.value !== prev);
        selectEl.dispatchEvent(new Event('change', { bubbles: true }));
        if (changed) flashSelect(selectEl);
        return true;
      }
    }
    return false;
  }

  const temaSelect   = document.getElementById('tema_custo');
  const grupoSelect  = document.getElementById('grupo');
  const fonteEl      = document.getElementById('fonte');
  const acaoEl       = document.getElementById('acao');
  const subEl        = document.getElementById('subacao');
  const fichaEl      = document.getElementById('ficha_financeira');

  let autoLock = false;

  const getTemaCodigo = () =>
    ((temaSelect?.value || '').split(' - ')[0] || '').trim();

  // ===================== REGRAS POR TEMA =====================
  function aplicarRegraTema() {
    if (!temaSelect) return;

    const temaCodigo = getTemaCodigo();

    // mapa herdado (seu bloco anterior) — mantive como estava
    const THEME_RULES = {
      '01': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS', ficha:'G3 - Apoio Administrativo - Estagiários' },
      '02': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'B662 - Despesas com combustível da CEHAB', ficha:['G3 - Combustíveis/Manutenção Veículos','G3 - Combustíveis/Manutenção/ Veículos'] },
      '03': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS', ficha:'G3 - Demandas Judiciais' },
      '04': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS', ficha:'G3 - Diárias Civil' },
      '05': { acao:'2928 - Conservação do Patrimonio Público na Companhia Estadual de Habitação e Obras - CHEAB', sub:'B665 - Prestação de serviços de limpeza e conservação da CEHAB', ficha:'G3 - Limpeza e Conservação' },
      '06': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'B666 - Despesas com locação de veículos da CEHAB', ficha:'G3 - Locação de Veículos' },
      '07': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS', ficha:'G3 - Manutenção Predial' },
      '08': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS', ficha:'G3 - Material de Expediente/Copa/Limpeza/Gráfico' },
      '09': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'B667 - Prestação de serviços de motorista na CEHAB', ficha:'G3 - Motoristas' },
      '10': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'B669 - Pagamento de apenados em processo de ressocialização na CEHAB', ficha:'G3 - Salário de Apenados' },
      '11': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'2790 - Manutenção da Tecnologia de Informação e Comunicação da CEHAB', ficha:'G3 - Rede Digital Corporativa do Estado' },
      '12': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS', ficha:'G3 - Serviços de Portaria' },
      '13': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS', ficha:'G3 - Serviços de Informática' },
      '14': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS', ficha:'G3 - Suprimento Individual' },
      '15': { acao:['2928 - Conservação do Patrimônio Público na Companhia Estadual de Habitação','2928 - Conservação do Patrimônio Público na Companhia Estadual de Habitação e Obras - CEHAB'], sub:'B670 - Prestação de serviços de segurança pessoal e patrimonial na CEHAB', ficha:'G3 - Vigilância Ostensiva' },
      '16': { acao:'4300 - Execução de Obras de Infraestrutura e de Urbanização', sub:'2794 - Auxílio Moradia - CEHAB' }, // sem ficha
      '17': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS', ficha:'G3 - Cota Global' },
      '18': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS', ficha:'G3 - Fornecimento de Passagens' },
      '19': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:['B664 - Despesas com tarifa de energia elétrica da CEHAB','B664 - Despesas com tarifa de energia'], ficha:'G3 - Energia Elétrica' },
      '20': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:['B661 - Despesas com taxa de água e esgoto da CEHAB','B661 - Despesas com taxa de água e esgoto'], ficha:'G3 - Água e Esgoto' },
      '22': { grupo:['1 - Pessoal','1 - Pessoal e Encargos Sociais'], acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS', ficha:'G1 - Pessoal e Encargos Sociais' },
      '23': { grupo:['1 - Pessoal','1 - Pessoal e Encargos Sociais'], acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS', ficha:'G1 - FGTS' },
      '24': { grupo:['1 - Pessoal','1 - Pessoal e Encargos Sociais'], acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS', ficha:'G1 - INSS' },
      '25': { grupo:['1 - Pessoal','1 - Pessoal e Encargos Sociais'], acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS', ficha:'G1 - Ressarcimento Pessoal à Disposição' },
      '30': { acao:'3927 - Manutenção da Ouvidoria da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS', ficha:'G3 - Outros' },
      '33': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'2791 - Fornecimento de vale transporte para servidores da CEHAB', ficha:['G3 - Vale / Auxílio Transporte','G3 - Vale/Auxílio Transporte','G3 - Vale Transporte'] },
      '34': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'2792 - Fornecimento de vale alimentação para servidores da CEHAB', ficha:['G3 - Vale / Auxílio Alimentação','G3 - Vale/Auxílio Alimentação','G3 - Vale Alimentação'] },
      '35': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:['B668 - Despesas com publicações oficiais da CEHAB em diário oficial','B668 - Despesas com publicações oficiais'], ficha:'G3 - Publicações Oficiais' },
      '36': { grupo:['1 - Pessoal','1 - Pessoal e Encargos Sociais'], acao:'4587 - Contribuições Patronais da CEHAB', sub:'2787 - Contribuições Patronais da CEHAB ao FUNAFIN', ficha:'G1 - Pessoal e Encargos Sociais' },
      '37': { acao:'4613 - Encargos com o PIS e o COFINS da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS', ficha:'G3 - Outros' },
      '38': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'B669 - Pagamento de apenados em processo de ressocialização na CEHAB', ficha:'G3 - Apoio Administrativo' }
    };

    // Tema 29 (regras por GRUPO já existentes)
    if (temaCodigo === '29') {
      pickOption(fonteEl, ['0500 - Tesouro do Estado','0500 - (Tesouro do Estado)','0500']);
      [acaoEl, subEl, fichaEl].forEach(sel => { if (sel) sel.value = ''; });
      return;
    }

    // --------- Novas regras solicitadas ---------

    // 31 - FINHIS: ação/sub fixas; grupo=4; ficha depende da FONTE (deixa em aberto p/ 0500)
    if (temaCodigo === '31') {
      autoLock = true;
      pickOption(grupoSelect, ['4 - Investimentos','4 -']);
      pickOption(acaoEl,  '4058 - Ampliação da Oferta e Requalificação de Habitação de Interesse Social');
      pickOption(subEl,   '0055 - Programa Minha Casa (Operações Coletivas, CAIC, FNHIS e PSH)');
      if (fichaEl) fichaEl.value = ''; // deixar usuário escolher no caso 0500
      autoLock = false;
      aplicarRegraFonte(); // se já houver fonte escolhida, aplica ficha conforme a fonte
      return;
    }

    // 32 - Minha Casa Minha Vida: ação/sub fixas; grupo=4; ficha depende da FONTE (em 0500 deixar aberto)
    if (temaCodigo === '32') {
      autoLock = true;
      pickOption(grupoSelect, ['4 - Investimentos','4 -']);
      pickOption(acaoEl,  '4058 - Ampliação da Oferta e Requalificação de Habitação de Interesse Social');
      pickOption(subEl,   '0865 - Operacionalização do Programa Minha Casa Minha Vida');
      if (fichaEl) fichaEl.value = ''; // pode ser G4 - Minha Casa Minha Vida ou G4 - Contrapartida de Convênio (se existir na lista)
      autoLock = false;
      aplicarRegraFonte();
      return;
    }

    // 39 - Apoio Especializado: ação/sub fixas; grupo e ficha dependem da FONTE
    if (temaCodigo === '39') {
      autoLock = true;
      pickOption(acaoEl, '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB');
      pickOption(subEl,  '0000 - OUTRAS MEDIDAS');
      if (fichaEl) fichaEl.value = '';
      autoLock = false;
      aplicarRegraFonte();
      return;
    }

    // 28 - Projetos de Obras: força fonte 0500; grupo=4; ação=4300; sub livre; ficha sempre "G4 - Projeto de Obra"
    if (temaCodigo === '28') {
      autoLock = true;
      pickOption(fonteEl, ['0500 - Tesouro do Estado','0500 - (Tesouro do Estado)','0500']);
      pickOption(grupoSelect, ['4 - Investimentos','4 -']);
      pickOption(acaoEl, '4300 - Execução de Obras de Infraestrutura e de Urbanização');
      if (subEl) subEl.value = ''; // usuário escolhe entre várias
      pickOption(fichaEl, 'G4 - Projeto de Obra');
      autoLock = false;
      return;
    }

    // 27 - Gerenciamento de Obras: força fonte 0500; grupo=4; ação=4300; sub livre; ficha sempre "G4 - Supervisão de Obra"
    if (temaCodigo === '27') {
      autoLock = true;
      pickOption(fonteEl, ['0500 - Tesouro do Estado','0500 - (Tesouro do Estado)','0500']);
      pickOption(grupoSelect, ['4 - Investimentos','4 -']);
      pickOption(acaoEl, '4300 - Execução de Obras de Infraestrutura e de Urbanização');
      if (subEl) subEl.value = ''; // várias alternativas
      pickOption(fichaEl, 'G4 - Supervisão de Obra');
      autoLock = false;
      return;
    }

    // Se existir regra direta herdada do mapa, aplica
    const cfg = THEME_RULES[temaCodigo] || THEME_RULES[String(Number(temaCodigo))];
    if (cfg) {
      autoLock = true;

      // Fonte default 0500 quando apropriado às regras antigas
      pickOption(fonteEl, ['0500 - Tesouro do Estado','0500 - (Tesouro do Estado)','0500']);

      const toQueries = (v) => {
        if (!v) return [];
        const arr = Array.isArray(v) ? v.filter(Boolean) : [v].filter(Boolean);
        return arr.flatMap(t => [t, t.normalize('NFD').replace(/\p{Diacritic}/gu,'')]);
      };

      const grupoQueries = cfg.grupo
        ? (Array.isArray(cfg.grupo) ? cfg.grupo : [cfg.grupo])
        : ['3 - Despesa Corrente','3 - Despesas Correntes','3 -'];

      pickOption(grupoSelect, grupoQueries.concat(
        grupoQueries.map(t => t.normalize('NFD').replace(/\p{Diacritic}/gu,''))
      ));

      pickOption(acaoEl,  toQueries(cfg.acao));
      pickOption(subEl,   toQueries(cfg.sub));
      pickOption(fichaEl, toQueries(cfg.ficha));

      autoLock = false;
    }
  }

  // ===================== REGRAS POR FONTE (dependendo do tema) =====================
  function aplicarRegraFonte() {
    if (autoLock) return;

    const temaCodigo = getTemaCodigo();
    const f = norm(fonteEl?.value || '');
    const is0500 = f.includes('0500');
    const is0700 = f.includes('0700');
    const is0754 = f.includes('0754');

    // 31 - FINHIS
    if (temaCodigo === '31') {
      autoLock = true;
      pickOption(grupoSelect, ['4 - Investimentos','4 -']);
      pickOption(acaoEl, '4058 - Ampliação da Oferta e Requalificação de Habitação de Interesse Social');
      pickOption(subEl,  '0055 - Programa Minha Casa (Operações Coletivas, CAIC, FNHIS e PSH)');

      if (is0700) pickOption(fichaEl, 'G4 - Recursos do Concedente');
      else if (is0754) pickOption(fichaEl, 'G4 - Operações de Crédito');
      else if (is0500) { if (fichaEl) fichaEl.value = ''; } // deixar o usuário escolher

      autoLock = false;
      return;
    }

    // 32 - Minha Casa Minha Vida
    if (temaCodigo === '32') {
      autoLock = true;
      pickOption(grupoSelect, ['4 - Investimentos','4 -']);
      pickOption(acaoEl, '4058 - Ampliação da Oferta e Requalificação de Habitação de Interesse Social');
      pickOption(subEl,  '0865 - Operacionalização do Programa Minha Casa Minha Vida');

      if (is0700) pickOption(fichaEl, 'G4 - Recursos do Concedente');
      else if (is0500) { if (fichaEl) fichaEl.value = ''; } // pode ser G4 - MCMV ou G4 - Contrapartida (se existirem)

      autoLock = false;
      return;
    }

    // 39 - Apoio Especializado
    if (temaCodigo === '39') {
      autoLock = true;
      pickOption(acaoEl, '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB');
      pickOption(subEl,  '0000 - OUTRAS MEDIDAS');

      if (is0500) {
        pickOption(grupoSelect, ['3 - Despesa Corrente','3 - Despesas Correntes','3 -']);
        // vai preencher se a opção existir; caso não, fica em aberto
        pickOption(fichaEl, 'G3 - Apoio Especializado');
      } else if (is0754) {
        pickOption(grupoSelect, ['4 - Investimentos','4 -']);
        pickOption(fichaEl, 'G4 - Operações de Crédito');
      }

      autoLock = false;
      return;
    }

    // 28 e 27 são independentes da fonte (forçamos 0500 no tema), então não tratamos aqui.
  }

  // ===================== listeners =====================
  if (temaSelect)  temaSelect.addEventListener('change', aplicarRegraTema);
  if (grupoSelect) grupoSelect.addEventListener('change', aplicarRegraGrupo);
  if (fonteEl)     fonteEl.addEventListener('change', aplicarRegraFonte);

  // ===================== regra 2 existente: Grupo -> ... (somente Tema 29) =====================
  function aplicarRegraGrupo() {
    if (!grupoSelect || autoLock) return;
    const temaCodigo = getTemaCodigo();
    if (temaCodigo !== '29') return;

    const gNorm = norm(grupoSelect.value || '');

    if (gNorm.startsWith('3 - despesa') || gNorm.startsWith('3 - despesas')) {
      pickOption(acaoEl, ['2904 - Formulação e Promoção da Política de Regularização Fundiária','2904']);
      pickOption(subEl,  ['0000 - OUTRAS MEDIDAS','0000']);
      pickOption(fichaEl,['G3 - Outros','Outros']);
      return;
    }

    if (gNorm.startsWith('4 - investimento')) {
      pickOption(acaoEl, ['2904 - Formulação e Promoção da Política de Regularização Fundiária','2904']);
      pickOption(subEl,  ['2793 - Regularização Fundiária e Oferta de Lotes Urbanos com Interesse Social','2793']);
      pickOption(fichaEl,['G4 - Outros','Outros']);
      return;
    }
  }

  // ===================== inicialização =====================
  aplicarRegraTema();
  aplicarRegraGrupo();
  aplicarRegraFonte(); // caso dados venham preenchidos do servidor

  somar(); // total inicial
});
