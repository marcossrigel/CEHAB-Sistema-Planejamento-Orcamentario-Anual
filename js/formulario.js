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

  // ====== destacar mudanças ======
  function flashSelect(el) {
    if (!el) return;
    // adiciona classes Tailwind pra evidenciar
    el.classList.add('ring-2','ring-sky-400','bg-sky-50','transition-colors','duration-700');
    // também dá um leve destaque no label (se existir)
    const label = document.querySelector(`label[for="${el.id}"]`);
    if (label) label.classList.add('text-sky-700');

    // remove depois de ~1.2s
    setTimeout(() => {
      el.classList.remove('ring-2','ring-sky-400','bg-sky-50');
      if (label) label.classList.remove('text-sky-700');
    }, 1200);
  }

  // ====== helper para selecionar opções por texto (robusto a acentos/caixa) ======
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
      let found = opts.find(o => norm(o.text) === qn)              // 1) exato
              || opts.find(o => norm(o.text).startsWith(qn))       // 2) começa com
              || opts.find(o => norm(o.text).includes(qn));        // 3) contém

      if (found) {
        // como suas <option> não têm value, usamos o texto
        selectEl.value = found.text;
        const changed = (selectEl.value !== prev);
        selectEl.dispatchEvent(new Event('change', { bubbles: true }));
        if (changed) flashSelect(selectEl); // destaca só se de fato mudou
        return true;
      }
    }
    return false;
  }

  // ====== mapa de preenchimento automático por Tema ======
  const temaAutofill = {
    '29': { // 29 - Regularização Fundiária
      fonte: [
        '0500 - Tesouro do Estado',
        '0500 - (Tesouro do Estado)',
        '0500'
      ],
      grupo: [
        '3 - Despesa Corrente',
        '3 - Despesas Correntes',
        '3 -'
      ],
      acao: [
        '2904 - Formulação e Promoção da Política de Regularização Fundiária',
        '2904 - Formulacao e Promocao da Politica de Regularizacao Fundiaria',
        '2904'
      ],
      subacao: [
        '0000 - Outras Medidas',
        '0000 - OUTRAS MEDIDAS',
        '0000'
      ],
      ficha_financeira: [
        'G3 - Outros',
        'Outros'
      ]
    }
  };

  // ====== listener do Tema de Custo ======
  const temaSelect = document.getElementById('tema_custo');
  if (temaSelect) {
    temaSelect.addEventListener('change', () => {
      const raw = temaSelect.value || '';
      const temaCodigo = (raw.split(' - ')[0] || '').trim(); // ex: "29"
      const cfg = temaAutofill[temaCodigo];
      if (!cfg) return;

      pickOption(document.getElementById('fonte'),            cfg.fonte);
      pickOption(document.getElementById('grupo'),            cfg.grupo);
      pickOption(document.getElementById('acao'),             cfg.acao);
      pickOption(document.getElementById('subacao'),          cfg.subacao);
      pickOption(document.getElementById('ficha_financeira'), cfg.ficha_financeira);
    });

    // aplica se já vier selecionado do servidor
    if (temaSelect.value) {
      temaSelect.dispatchEvent(new Event('change'));
    }
  }

  // calcula total inicial (caso tenha valores preenchidos)
  somar();
});
