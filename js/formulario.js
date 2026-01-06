// js/formulario.js (com Tema 21 - Outros + filtros dinâmicos)
document.addEventListener('DOMContentLoaded', () => {
  // ====== utilidades de moeda ======
  const fmt = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
  const parseMoeda = v => (v || '').replace(/[^0-9]/g, '') / 100 || 0;

  function somar() {
    let total = 0;

    // soma apenas os campos da Tabela de Meses
    document.querySelectorAll('.moeda-mes').forEach(i => {
      total += parseMoeda(i.value);
    });

    const totalEl = document.getElementById('totalMeses');
    if (totalEl) totalEl.textContent = fmt.format(total);
  }

  function formatar(e) {
    e.target.value = fmt.format(parseMoeda(e.target.value));
    somar();
  }

document.querySelectorAll('.moeda-mes').forEach(i => i.addEventListener('input', formatar));
// e também no Valor Total do Contrato
document.querySelectorAll('.moeda-total').forEach(i => i.addEventListener('input', formatar));


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

  // ==== snapshot/restauração de opções (para filtro dinâmico) ====
  const originals = new Map(); // selectEl -> [{text, value, disabled}]
  function snapshotOptions(selectEl) {
    if (!selectEl || originals.has(selectEl)) return;
    originals.set(selectEl, Array.from(selectEl.options).map(o => ({
      text: o.text, value: o.value, disabled: o.disabled
    })));
  }
  function restoreOptions(selectEl) {
    if (!selectEl) return;
    const snap = originals.get(selectEl);
    if (!snap) return;
    const current = selectEl.value;
    selectEl.innerHTML = '';
    for (const o of snap) {
      const opt = new Option(o.text, o.value);
      opt.disabled = !!o.disabled;
      selectEl.add(opt);
    }
    // tenta manter seleção anterior, se existir
    const had = Array.from(selectEl.options).find(o => o.text === current);
    selectEl.value = had ? current : '';
  }
  function setAllowedOptions(selectEl, allowedTexts) {
    // mantém "Selecione..." se houver
    if (!selectEl) return;
    snapshotOptions(selectEl);
    const snap = originals.get(selectEl) || [];
    const allowedNorm = new Set(allowedTexts.map(t => norm(t)));

    const kept = [];
    // mantém primeira opção (Selecione...) se existir
    if (snap.length && norm(snap[0].text).includes('selecione')) {
      kept.push(snap[0]);
    }
    for (let i = 0; i < snap.length; i++) {
      const o = snap[i];
      if (norm(o.text).includes('selecione')) continue;
      if (allowedNorm.has(norm(o.text))) kept.push(o);
    }
    selectEl.innerHTML = '';
    for (const o of kept) {
      const opt = new Option(o.text, o.value);
      opt.disabled = !!o.disabled;
      selectEl.add(opt);
    }
    selectEl.value = '';
  }

  const temaSelect   = document.getElementById('tema_custo');
  const grupoSelect  = document.getElementById('grupo');
  const fonteEl      = document.getElementById('fonte');
  const acaoEl       = document.getElementById('acao');
  const subEl        = document.getElementById('subacao');
  const fichaEl      = document.getElementById('ficha_financeira');

    // campos que podem ser travados pelo Tema de Custo
  const camposAuto = [fonteEl, grupoSelect, acaoEl, subEl];

  function setCamposDisabled(disabled) {
  camposAuto.forEach(el => {
    if (!el) return;

    if (!disabled) {
      // Libera geral
      el.disabled = false;
      el.classList.remove('bg-slate-100', 'cursor-not-allowed');
      return;
    }

    // Quando for travar, só trava se já tiver um valor escolhido
    const opt  = el.options[el.selectedIndex] || null;
    const text = opt ? opt.text || '' : '';
    const semEscolha = !el.value || norm(text).includes('selecione');

    if (semEscolha) {
      // Continua editável se estiver em "Selecione..."
      el.disabled = false;
      el.classList.remove('bg-slate-100', 'cursor-not-allowed');
    } else {
      // Já tem valor auto-preenchido → trava
      el.disabled = true;
      el.classList.add('bg-slate-100', 'cursor-not-allowed');
    }
  });
}

  // snapshot inicial dos selects que serão filtrados
[fonteEl, grupoSelect, acaoEl, fichaEl].forEach(snapshotOptions);


  let autoLock = false;
  const getTemaCodigo = () =>
    ((temaSelect?.value || '').split(' - ')[0] || '').trim();
  // ===================== REGRAS POR TEMA =====================
function aplicarRegraTema() {
  if (!temaSelect) return;
  const temaCodigo = getTemaCodigo();

  // Sempre começa liberando tudo e restaurando as opções
  restoreOptions(fonteEl);
  restoreOptions(grupoSelect);
  restoreOptions(acaoEl);
  restoreOptions(fichaEl);
  setCamposDisabled(false);
  if (subEl) {
    subEl.disabled = false;
    subEl.value = '';
  }

  // ================== NENHUM TEMA SELECIONADO ==================
  if (!temaCodigo) {
    return;
  }

  // ================== TEMA 21 - OUTROS (livre, mas filtrado) ==================
  if (temaCodigo === '21') {
    autoLock = true;

    // Filtrar FONTES: 0500 ou 0754
    setAllowedOptions(fonteEl, [
      '0500 - (Tesouro do Estado)',
      '0500 - Tesouro do Estado',
      '0754 - (Operação de Crédito)',
      '0754 - Operações de Crédito',
      '0754 - Operação de Crédito'
    ]);

    // Filtrar GRUPOS: 3 ou 4
    setAllowedOptions(grupoSelect, [
      '3 - Despesa Corrente',
      '3 - Despesas Correntes',
      '4 - Investimentos'
    ]);

    // Ações permitidas
    setAllowedOptions(acaoEl, [
      '4300 - Execução de Obras de Infraestrutura e de Urbanização',
      '4301 - Pesquisa e Assessoria Técnica para Habitação de Interesse Social',
      '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB'
    ]);

    // Ficha Financeira: só G3/G4 Outros
    setAllowedOptions(fichaEl, [
      'G3 - Outros',
      'G4 - Outros'
    ]);

    // limpar seleção para o usuário escolher
    if (subEl) subEl.value = '';
    if (fichaEl) fichaEl.value = '';

    autoLock = false;       // Tema 21 FICA LIVRE, não chama setCamposDisabled aqui

    flashSelect(fonteEl);
    flashSelect(grupoSelect);
    flashSelect(acaoEl);
    return;
  }

  // ======== Regras já existentes (mantidas) ========
  const THEME_RULES = {
    '01': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS'},
    '02': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'B662 - Despesas com combustível da CEHAB'},
    '03': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS'},
    '04': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS'},
    '05': { acao:'2928 - Conservação do Patrimonio Público na Companhia Estadual de Habitação e Obras - CHEAB', sub:'B665 - Prestação de serviços de limpeza e conservação da CEHAB'},
    '06': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'B666 - Despesas com locação de veículos da CEHAB'},
    '07': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS'},
    '08': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS'},
    '09': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'B667 - Prestação de serviços de motorista na CEHAB' },
    '10': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'B669 - Pagamento de apenados em processo de ressocialização na CEHAB'},
    '11': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'2790 - Manutenção da Tecnologia de Informação e Comunicação da CEHAB'},
    '12': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS'},
    '13': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS'},
    '14': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS'},
    '15': { acao:['2928 - Conservação do Patrimônio Público na Companhia Estadual de Habitação','2928 - Conservação do Patrimônio Público na Companhia Estadual de Habitação e Obras - CEHAB'], sub:'B670 - Prestação de serviços de segurança pessoal e patrimonial na CEHAB'},
    '16': { acao:'4300 - Execução de Obras de Infraestrutura e de Urbanização', sub:'2794 - Auxílio Moradia - CEHAB' }, // sem ficha
    '17': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS'},
    '18': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS'},
    '19': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:['B664 - Despesas com tarifa de energia elétrica da CEHAB','B664 - Despesas com tarifa de energia']},
    '20': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:['B661 - Despesas com taxa de água e esgoto da CEHAB','B661 - Despesas com taxa de água e esgoto']},
    '22': { grupo:['1 - Pessoal','1 - Pessoal e Encargos Sociais'], acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS'},
    '23': { grupo:['1 - Pessoal','1 - Pessoal e Encargos Sociais'], acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS'},
    '24': { grupo:['1 - Pessoal','1 - Pessoal e Encargos Sociais'], acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS'},
    '25': { grupo:['1 - Pessoal','1 - Pessoal e Encargos Sociais'], acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS'},
    '26': { grupo:['4 - Investimentos'], acao:'4300 - Execução de Obras de Infraestrutura e de Urbanização' },
    '30': { acao:'3927 - Manutenção da Ouvidoria da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS'},
    '33': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'2791 - Fornecimento de vale transporte para servidores da CEHAB'},
    '34': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'2792 - Fornecimento de vale alimentação para servidores da CEHAB'},
    '35': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:['B668 - Despesas com publicações oficiais da CEHAB em diário oficial','B668 - Despesas com publicações oficiais']},
    '36': { grupo:['1 - Pessoal','1 - Pessoal e Encargos Sociais'], acao:'4587 - Contribuições Patronais da CEHAB', sub:'2787 - Contribuições Patronais da CEHAB ao FUNAFIN' },
    '37': { acao:'4613 - Encargos com o PIS e o COFINS da Companhia Estadual de Habitação e Obras - CEHAB', sub:'0000 - OUTRAS MEDIDAS'},
    '38': { acao:'4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB', sub:'B669 - Pagamento de apenados em processo de ressocialização na CEHAB'}
  };

  // ================== Tema 29 (regras por GRUPO, mas campos livres) ==================
  if (temaCodigo === '29') {
    setCamposDisabled(false); // usuário ainda mexe em grupo/ação/etc.
    pickOption(fonteEl, ['0500 - Tesouro do Estado','0500 - (Tesouro do Estado)','0500']);
    [acaoEl, subEl, fichaEl].forEach(sel => { if (sel) sel.value = ''; });
    return;
  }

  // ================== 31 - FINHIS ==================
  if (temaCodigo === '31') {
    autoLock = true;
    pickOption(grupoSelect, ['4 - Investimentos','4 -']);
    pickOption(acaoEl,  '4058 - Ampliação da Oferta e Requalificação de Habitação de Interesse Social');
    pickOption(subEl,   '0055 - Programa Minha Casa (Operações Coletivas, CAIC, FNHIS e PSH)');
    if (fichaEl) fichaEl.value = '';
    autoLock = false;
    aplicarRegraFonte();
    setCamposDisabled(true);
    return;
  }

  // ================== 32 - MCMV ==================
  if (temaCodigo === '32') {
    autoLock = true;
    pickOption(grupoSelect, ['4 - Investimentos','4 -']);
    pickOption(acaoEl,  '4058 - Ampliação da Oferta e Requalificação de Habitação de Interesse Social');
    pickOption(subEl,   '0865 - Operacionalização do Programa Minha Casa Minha Vida');
    if (fichaEl) fichaEl.value = '';
    autoLock = false;
    aplicarRegraFonte();
    setCamposDisabled(true);
    return;
  }

  // ================== 39 - Apoio Especializado ==================
  if (temaCodigo === '39') {
    autoLock = true;
    pickOption(acaoEl, '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB');
    pickOption(subEl,  '0000 - OUTRAS MEDIDAS');
    if (fichaEl) fichaEl.value = '';
    autoLock = false;
    aplicarRegraFonte();
    setCamposDisabled(true);
    return;
  }

  // ================== 28 - Projetos de Obras ==================
  if (temaCodigo === '28') {
    autoLock = true;
    pickOption(fonteEl, ['0500 - Tesouro do Estado','0500 - (Tesouro do Estado)','0500']);
    pickOption(grupoSelect, ['4 - Investimentos','4 -']);
    pickOption(acaoEl, '4300 - Execução de Obras de Infraestrutura e de Urbanização');
    if (subEl) subEl.value = '';
    autoLock = false;
    setCamposDisabled(true);
    return;
  }

  // ================== 27 - Gerenciamento de Obras ==================
  if (temaCodigo === '27') {
    autoLock = true;
    pickOption(fonteEl, ['0500 - Tesouro do Estado','0500 - (Tesouro do Estado)','0500']);
    pickOption(grupoSelect, ['4 - Investimentos','4 -']);
    pickOption(acaoEl, '4300 - Execução de Obras de Infraestrutura e de Urbanização');
    if (subEl) subEl.value = '';
    autoLock = false;
    setCamposDisabled(true);
    return;
  }

  // ================== Regras default do mapa (demais temas) ==================
  const cfg = THEME_RULES[temaCodigo] || THEME_RULES[String(Number(temaCodigo))];
  if (cfg) {
    autoLock = true;

    // Só força a fonte 0500 se NÃO for Tema 26 - Obras
    if (temaCodigo !== '26') {
      pickOption(fonteEl, ['0500 - Tesouro do Estado','0500 - (Tesouro do Estado)','0500']);
    }

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
    if (fichaEl) fichaEl.value = '';

    autoLock = false;
    setCamposDisabled(true); // aqui trava para os temas "fechados"
  }
}

function aplicarRegraFonte() {
  if (autoLock) return;

  const temaCodigo = getTemaCodigo();
  const f = norm(fonteEl?.value || '');
  const is0500 = f.includes('0500');
  const is0700 = f.includes('0700');
  const is0754 = f.includes('0754');

  if (temaCodigo === '31') {
    autoLock = true;
    pickOption(grupoSelect, ['4 - Investimentos','4 -']);
    pickOption(acaoEl, '4058 - Ampliação da Oferta e Requalificação de Habitação de Interesse Social');
    pickOption(subEl,  '0055 - Programa Minha Casa (Operações Coletivas, CAIC, FNHIS e PSH)');
    if (fichaEl) fichaEl.value = '';
    autoLock = false;
    return;
  }

  if (temaCodigo === '32') {
    autoLock = true;
    pickOption(grupoSelect, ['4 - Investimentos','4 -']);
    pickOption(acaoEl, '4058 - Ampliação da Oferta e Requalificação de Habitação de Interesse Social');
    pickOption(subEl,  '0865 - Operacionalização do Programa Minha Casa Minha Vida');
    if (fichaEl) fichaEl.value = '';
    autoLock = false;
    return;
  }

  if (temaCodigo === '39') {
    autoLock = true;
    pickOption(acaoEl, '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB');
    pickOption(subEl,  '0000 - OUTRAS MEDIDAS');
    if (is0500) {
      pickOption(grupoSelect, ['3 - Despesa Corrente','3 - Despesas Correntes','3 -']);
    } else if (is0754) {
      pickOption(grupoSelect, ['4 - Investimentos','4 -']);
    }
    // FICHA LIVRE
    if (fichaEl) fichaEl.value = '';
    autoLock = false;
    return;
  }
  // 27 e 28 não dependem da fonte aqui
}

  // ===================== Regra especial por AÇÃO (Tema 21) =====================
  function aplicarRegraAcaoTema21() {
    if (getTemaCodigo() !== '21') return;
    const acao = (acaoEl?.value || '');
    const a = norm(acao);

    // 4301 → só sugere SUBAÇÃO, ficha fica a critério do usuário
    if (a.startsWith('4301 - pesquisa')) {
      pickOption(subEl, '1163 - Acompanhamento do cadastro de famílias beneficiadas pelo auxílio moradia');
      return;
    }

    // 4354 → só sugere SUBAÇÃO
    if (a.startsWith('4354 - gestao') || a.includes('gestão das atividades')) {
      pickOption(subEl, '0000 - OUTRAS MEDIDAS');
      return;
    }

    // 4300 → não força subnem ficha
    if (a.startsWith('4300 - execucao') || a.includes('infraestrutura e de urbanizacao')) {
      // deixa subação e ficha livres
      return;
    }
  }

  // ===================== regra 2 existente: Grupo -> ... (somente Tema 29) =====================
  function aplicarRegraGrupo() {
    if (!grupoSelect || autoLock) return;
    const temaCodigo = getTemaCodigo();
    if (temaCodigo !== '29') return;

    const gNorm = norm(grupoSelect.value || '');

    if (gNorm.startsWith('3 - despesa') || gNorm.startsWith('3 - despesas')) {
      pickOption(acaoEl, ['2904 - Formulação e Promoção da Política de Regularização Fundiária','2904']);
      pickOption(subEl,  ['0000 - OUTRAS MEDIDAS','0000']);
      if (fichaEl) fichaEl.value = '';
      return;
    }

    if (gNorm.startsWith('4 - investimento')) {
      pickOption(acaoEl, ['2904 - Formulação e Promoção da Política de Regularização Fundiária','2904']);
      pickOption(subEl,  ['2793 - Regularização Fundiária e Oferta de Lotes Urbanos com Interesse Social','2793']);
      if (fichaEl) fichaEl.value = '';
      return;
    }
  }

  // ===================== listeners =====================
  if (temaSelect)  temaSelect.addEventListener('change', () => {
    aplicarRegraTema();
    // Ao mudar o tema, se for 21, também escutamos mudança de Ação
    if (getTemaCodigo() === '21') aplicarRegraAcaoTema21();
  });

  if (grupoSelect) grupoSelect.addEventListener('change', aplicarRegraGrupo);
  if (fonteEl)     fonteEl.addEventListener('change', aplicarRegraFonte);
  if (acaoEl)      acaoEl.addEventListener('change', aplicarRegraAcaoTema21);

  // ===================== inicialização =====================
  aplicarRegraTema();
  aplicarRegraGrupo();
  aplicarRegraFonte(); // caso dados venham preenchidos do servidor
  aplicarRegraAcaoTema21();

  somar(); // total inicial
});
