// js/formulario.js
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
    for (const q of queries) {
      const qn = norm(q);
      let found = opts.find(o => norm(o.text) === qn)
             ||    opts.find(o => norm(o.text).startsWith(qn))
             ||    opts.find(o => norm(o.text).includes(qn));
      if (found) {
        selectEl.value = found.text; // seu HTML usa o texto como value
        const changed = (selectEl.value !== prev);
        selectEl.dispatchEvent(new Event('change', { bubbles: true }));
        if (changed) flashSelect(selectEl);
        return true;
      }
    }
    return false;
  }

  // ====== elementos ======
  const temaSelect   = document.getElementById('tema_custo');
  const grupoSelect  = document.getElementById('grupo');
  const fonteEl      = document.getElementById('fonte');
  const acaoEl       = document.getElementById('acao');
  const subEl        = document.getElementById('subacao');
  const fichaEl      = document.getElementById('ficha_financeira');

  let autoLock = false;

function aplicarRegraTema() {
  if (!temaSelect) return;
  const raw = temaSelect.value || '';
  const temaCodigo = (raw.split(' - ')[0] || '').trim(); // "01","02","05","15","29", etc.

  // --- helper DRY para caminhos diretos ---
  function aplicarDireto(cfg) {
    autoLock = true;

    pickOption(fonteEl, [
      '0500 - Tesouro do Estado',
      '0500 - (Tesouro do Estado)',
      '0500'
    ]);

    pickOption(grupoSelect, [
      '3 - Despesa Corrente',
      '3 - Despesas Correntes',
      '3 -'
    ]);

    pickOption(fonteEl, [
      '0500 - Tesouro do Estado',
      '0500 - (Tesouro do Estado)',
      '0500'
    ]);

    // Grupo: usa o passado no cfg, senão default = Grupo 3
    const grupoQueries = cfg.grupo
      ? (Array.isArray(cfg.grupo) ? cfg.grupo : [cfg.grupo])
      : ['3 - Despesa Corrente','3 - Despesas Correntes','3 -'];

    pickOption(grupoSelect, grupoQueries.concat(
      grupoQueries.map(t => t.normalize('NFD').replace(/\p{Diacritic}/gu,''))
    ));

    // aceita string OU array e gera também versões sem acento
    const toQueries = (v) => {
      if (!v) return []; // permite cfg sem ficha, por ex. Tema 16
      const arr = Array.isArray(v) ? v.filter(Boolean) : [v].filter(Boolean);
      return arr.flatMap(t => [t, t.normalize('NFD').replace(/\p{Diacritic}/gu,'')]);
    };

    pickOption(acaoEl,  toQueries(cfg.acao));
    pickOption(subEl,   toQueries(cfg.sub));
    pickOption(fichaEl, toQueries(cfg.ficha));

    setTimeout(() => { autoLock = false; }, 0);
  }

  const THEME_RULES = {
  // 01 - Apoio Administrativo – Estagiários
  '01': {
    acao:  '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB',
    sub:   '0000 - OUTRAS MEDIDAS',
    ficha: 'G3 - Apoio Administrativo - Estagiários'
  },

  // 02 - Combustível/Manutenção de Veículos
  '02': {
    acao:  '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB',
    sub:   'B662 - Despesas com combustível da CEHAB',
    ficha: [
      'G3 - Combustível/Manutenção Veículos',
      'G3 - Combustíveis/Manutenção/ Veículos'
    ]
  },

  // 03 - Demandas Judiciais
  '03': {
    acao:  '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB',
    sub:   '0000 - OUTRAS MEDIDAS',
    ficha: 'G3 - Demandas Judiciais'
  },

  // 04 - Diárias Civil
  '04': {
    acao:  '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB',
    sub:   '0000 - OUTRAS MEDIDAS',
    ficha: 'G3 - Diárias Civil'
  },

  // 05 - Limpeza e Conservação
  '05': {
    acao:  '2928 - Conservação do Patrimonio Público na Companhia Estadual de Habitação e Obras - CHEAB',
    sub:   'B665 - Prestação de serviços de limpeza e conservação da CEHAB',
    ficha: 'G3 - Limpeza e Conservação'
  },

  // 06 - Locação de Veículos
  '06': {
    acao:  '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB',
    sub:   'B666 - Despesas com locação de veículos da CEHAB',
    ficha: 'G3 - Locação de Veículos'
  },

  // 07 - Manutenção Predial
  '07': {
    acao:  '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB',
    sub:   '0000 - OUTRAS MEDIDAS',
    ficha: 'G3 - Manutenção Predial'
  },

  // 08 - Material de Expediente/Copa/Limpeza/Gráfico
  '08': {
    acao:  '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB',
    sub:   '0000 - OUTRAS MEDIDAS',
    ficha: 'G3 - Material de Expediente/Copa/Limpeza/Gráfico'
  },

  // 09 - Motoristas
  '09': {
    acao:  '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB',
    sub:   'B667 - Prestação de serviços de motorista na CEHAB',
    ficha: 'G3 - Motoristas'
  },

  // 10 - Salário de Apenados
  '10': {
    acao:  '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB',
    sub:   'B669 - Pagamento de apenados em processo de ressocialização na CEHAB',
    ficha: 'G3 - Salário de Apenados'
  },

  // 11 - Rede Digital Corporativa do Estado
  '11': {
    acao:  '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB',
    sub:   '2790 - Manutenção da Tecnologia de Informação e Comunicação da CEHAB',
    ficha: 'G3 - Rede Digital Corporativa do Estado'
  },

  // 12 - Serviços de Portaria
  '12': {
    acao:  '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB',
    sub:   '0000 - OUTRAS MEDIDAS',
    ficha: 'G3 - Serviços de Portaria'
  },

  // 13 - Serviços de Informática
  '13': {
    acao:  '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB',
    sub:   '0000 - OUTRAS MEDIDAS',
    ficha: 'G3 - Serviços de Informática'
  },

  // 14 - Suprimento Individual
  '14': {
    acao:  '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB',
    sub:   '0000 - OUTRAS MEDIDAS',
    ficha: 'G3 - Suprimento Individual'
  },

  // 15 - Vigilância Ostensiva
  '15': {
    acao: [
      '2928 - Conservação do Patrimônio Público na Companhia Estadual de Habitação',
      '2928 - Conservação do Patrimônio Público na Companhia Estadual de Habitação e Obras - CEHAB'
    ],
    sub:   'B670 - Prestação de serviços de segurança pessoal e patrimonial na CEHAB',
    ficha: 'G3 - Vigilância Ostensiva'
  },

  // 16 - Auxílio Moradia  (sem ficha, como solicitado)
  '16': {
    acao:  '4300 - Execução de Obras de Infraestrutura e de Urbanização',
    sub:   '2794 - Auxílio Moradia - CEHAB'
    // ficha: 'G3 - Auxílio Moradia'
  },

  // 17 - Cota Global
  '17': {
    acao:  '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB',
    sub:   '0000 - OUTRAS MEDIDAS',
    ficha: 'G3 - Cota Global'
  },

  // 18 - Passagens Aéreas
  '18': {
    acao:  '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB',
    sub:   '0000 - OUTRAS MEDIDAS',
    ficha: 'G3 - Fornecimento de Passagens'
  },

  // 19 - Energia Elétrica
  '19': {
    acao:  '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB',
    sub:   [
      'B664 - Despesas com tarifa de energia elétrica da CEHAB',
      'B664 - Despesas com tarifa de energia'
    ],
    ficha: 'G3 - Energia Elétrica'
  },

  // 20 - Água e Esgoto
  '20': {
    acao:  '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB',
    sub:   [
      'B661 - Despesas com taxa de água e esgoto da CEHAB',
      'B661 - Despesas com taxa de água e esgoto'
    ],
    ficha: 'G3 - Água e Esgoto'
  },

  // 22 - Folha de Pessoal
  '22': {
    grupo: ['1 - Pessoal','1 - Pessoal e Encargos Sociais'],
    acao:  '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB',
    sub:   '0000 - OUTRAS MEDIDAS',
    ficha: 'G1 - Pessoal e Encargos Sociais'
  },

  // 23 - FGTS
  '23': {
    grupo: ['1 - Pessoal','1 - Pessoal e Encargos Sociais'],
    acao:  '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB',
    sub:   '0000 - OUTRAS MEDIDAS',
    ficha: 'G1 - FGTS'
  },

  // 24 - INSS
  '24': {
    grupo: ['1 - Pessoal','1 - Pessoal e Encargos Sociais'],
    acao:  '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB',
    sub:   '0000 - OUTRAS MEDIDAS',
    ficha: 'G1 - INSS'
  },

  // 25 - Ressarcimento Pessoal à Disposição
  '25': {
    grupo: ['1 - Pessoal','1 - Pessoal e Encargos Sociais'],
    acao:  '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB',
    sub:   '0000 - OUTRAS MEDIDAS',
    ficha: 'G1 - Ressarcimento Pessoal à Disposição'
  },

  // 30 - Ouvidoria
  '30': {
    acao:  '3927 - Manutenção da Ouvidoria da Companhia Estadual de Habitação e Obras - CEHAB',
    sub:   '0000 - OUTRAS MEDIDAS',
    ficha: 'G3 - Outros'
  },

  // 33 - Vale Transporte
  '33': {
    acao:  '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB',
    sub:   '2791 - Fornecimento de vale transporte para servidores da CEHAB',
    ficha: [
      'G3 - Vale / Auxílio Transporte',
      'G3 - Vale/Auxílio Transporte',
      'G3 - Vale Transporte'
    ]
  },

  // 34 - Vale Alimentação
  '34': {
    acao:  '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB',
    sub:   '2792 - Fornecimento de vale alimentação para servidores da CEHAB',
    ficha: [
      'G3 - Vale / Auxílio Alimentação',
      'G3 - Vale/Auxílio Alimentação',
      'G3 - Vale Alimentação'
    ]
  },

  // 35 - Publicações Oficiais
  '35': {
    acao:  '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB',
    sub:   [
      'B668 - Despesas com publicações oficiais da CEHAB em diário oficial',
      'B668 - Despesas com publicações oficiais'
    ],
    ficha: 'G3 - Publicações Oficiais'
  },

  // 36 - Contribuições Patronais da CEHAB
  '36': {
    grupo: ['1 - Pessoal','1 - Pessoal e Encargos Sociais'],
    acao:  '4587 - Contribuições Patronais da CEHAB',
    sub:   '2787 - Contribuições Patronais da CEHAB ao FUNAFIN',
    ficha: 'G1 - Pessoal e Encargos Sociais'
  },

  // 37 - Encargos com o PIS e o COFINS
  '37': {
    acao:  '4613 - Encargos com o PIS e o COFINS da Companhia Estadual de Habitação e Obras - CEHAB',
    sub:   '0000 - OUTRAS MEDIDAS',
    ficha: 'G3 - Outros'
  },

  // 38 - Apoio Administrativo
  '38': {
    acao:  '4354 - Gestão das Atividades da Companhia Estadual de Habitação e Obras - CEHAB',
    sub:   'B669 - Pagamento de apenados em processo de ressocialização na CEHAB',
    ficha: 'G3 - Apoio Administrativo'
  }
};

  // Tema 29: apenas FONTE 0500 e limpa dependentes p/ o Grupo ditar depois
  if (temaCodigo === '29') {
    pickOption(fonteEl, [
      '0500 - Tesouro do Estado',
      '0500 - (Tesouro do Estado)',
      '0500'
    ]);
    // limpa Ação/Subação/Ficha para aguardar Grupo
    [acaoEl, subEl, fichaEl].forEach(sel => { if (sel) sel.value = ''; });
    return;
  }

  // Se existir regra direta no mapa, aplica
  if (THEME_RULES[temaCodigo] || THEME_RULES[String(Number(temaCodigo))]) {
    const cfg = THEME_RULES[temaCodigo] || THEME_RULES[String(Number(temaCodigo))];
    aplicarDireto(cfg);
    return;
  }

}

  // ====== regra 2: Grupo -> Ação, Subação, Ficha (APENAS para Tema 29)
function aplicarRegraGrupo() {
  if (!grupoSelect) return;
  if (autoLock) return;

  // garante que só aplica as regras de grupo para o tema 29
  const temaCodigo = ((temaSelect?.value || '').split(' - ')[0] || '').trim();
  if (temaCodigo !== '29') return;

  const g = grupoSelect.value || '';
  const gNorm = norm(g);

  // Grupo 3 - Despesas Correntes
  if (gNorm.startsWith('3 - despesa') || gNorm.startsWith('3 - despesas')) {
    pickOption(acaoEl, [
      '2904 - Formulação e Promoção da Política de Regularização Fundiária',
      '2904'
    ]);
    pickOption(subEl, [
      '0000 - OUTRAS MEDIDAS',
      '0000'
    ]);
    pickOption(fichaEl, [
      'G3 - Outros',
      'Outros'
    ]);
    return;
  }

  // Grupo 4 - Investimentos
  if (gNorm.startsWith('4 - investimento')) {
    pickOption(acaoEl, [
      '2904 - Formulação e Promoção da Política de Regularização Fundiária',
      '2904'
    ]);
    pickOption(subEl, [
      '2793 - Regularização Fundiária e Oferta de Lotes Urbanos com Interesse Social',
      '2793'
    ]);
    pickOption(fichaEl, [
      'G4 - Outros',
      'Outros'
    ]);
    return;
  }
}

  // listeners
  if (temaSelect)  temaSelect.addEventListener('change', aplicarRegraTema);
  if (grupoSelect) grupoSelect.addEventListener('change', aplicarRegraGrupo);

  // aplica regras se vier preenchido do servidor
  aplicarRegraTema();
  aplicarRegraGrupo();

  // calcula total inicial (caso tenha valores preenchidos)
  somar();
});