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

  // ====== regra 1: Tema 29 -> só Fonte 0500 ======
  function aplicarRegraTema() {
    if (!temaSelect) return;
    const raw = temaSelect.value || '';
    const temaCodigo = (raw.split(' - ')[0] || '').trim(); // "29"
    if (temaCodigo === '29') {
      // apenas Fonte
      pickOption(fonteEl, [
        '0500 - Tesouro do Estado',
        '0500 - (Tesouro do Estado)',
        '0500'
      ]);
    }
  }

  // ====== regra 2: Grupo -> Ação, Subação, Ficha ======
  function aplicarRegraGrupo() {
    if (!grupoSelect) return;
    const g = grupoSelect.value || '';
    const gNorm = norm(g);

    // Grupo 3 - Despesa(s) Corrente(s)
    if (gNorm.startsWith('3 - despesa') || gNorm.startsWith('3 - despesas')) {
      pickOption(acaoEl, [
        '2904 - Formulação e Promoção da Política de Regularização Fundiária',
        '2904 - Formulacao e Promocao da Politica de Regularizacao Fundiaria',
        '2904'
      ]);
      pickOption(subEl, [
        '0000 - OUTRAS MEDIDAS',
        '0000 - Outras Medidas',
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
        '2904 - Formulacao e Promocao da Politica de Regularizacao Fundiaria',
        '2904'
      ]);
      pickOption(subEl, [
        '2793 - Regularização Fundiária e Oferta de Lotes Urbanos com Interesse Social',
        '2793 - Regularizacao Fundiaria e Oferta de Lotes Urbanos com Interesse Social',
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
