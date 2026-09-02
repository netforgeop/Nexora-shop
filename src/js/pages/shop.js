/**
 * Shop & search page: URL-synced filters, sorting, pagination, grid/list view,
 * sidebar → drawer move on small screens, active filter chips.
 */
import { $, $$, on, debounce, mq, scrollToEl } from '../core/dom.js';
import { filterProducts, sorters } from '../core/catalog.js';
import { productCardHTML, paginationHTML, productCardSkeletonHTML } from '../core/render.js';
import { esc } from '../core/i18n.js';
import { syncPressedStates } from '../components/cart-ui.js';
import { toast } from '../components/toast.js';

export function initShop(ctx) {
  const page = $('[data-shop]');
  if (!page) return;
  const isSearch = page.dataset.mode === 'search';
  const perPage = Number(page.dataset.perPage || 12);
  const grid = $('[data-shop-grid]', page);
  const results = $('[data-shop-results]', page);
  const emptyBox = $('[data-shop-empty]', page);
  const countEl = $('[data-shop-count]', page);
  const pagEl = $('[data-shop-pagination]', page);
  const activeEl = $('[data-active-filters]', page);
  const filtersForm = $('[data-filters]', page);
  const filtersHost = $('[data-filters-host]', page);
  const drawerBody = $('[data-filters-drawer-body]');
  const catalog = ctx.catalog;
  const products = catalog.products;
  const priceMin = catalog.priceMin; const priceMax = catalog.priceMax;

  /* ---------- state from URL ---------- */
  const state = { q: '', cats: [], brands: [], min: null, max: null, rating: 0, colors: [], inStock: false, discount: false, sort: 'popular', page: 1, view: 'grid' };
  try { state.view = localStorage.getItem('nx:view') || 'grid'; } catch { /* noop */ }

  function readURL() {
    const sp = new URLSearchParams(location.search);
    state.q = sp.get('q') || '';
    state.cats = sp.getAll('cat').flatMap((v) => v.split(',')).filter(Boolean);
    state.brands = sp.getAll('brand').flatMap((v) => v.split(',')).filter(Boolean);
    state.min = sp.get('min') ? Number(sp.get('min')) : null;
    state.max = sp.get('max') ? Number(sp.get('max')) : null;
    state.rating = Number(sp.get('rating')) || 0;
    state.colors = sp.getAll('color').flatMap((v) => v.split(',')).filter(Boolean);
    state.inStock = sp.get('instock') === '1';
    state.discount = sp.get('discount') === '1';
    state.sort = sp.get('sort') || (isSearch ? 'popular' : 'popular');
    state.page = Number(sp.get('page')) || 1;
  }

  function writeURL(replace = false) {
    const sp = new URLSearchParams();
    if (state.q) sp.set('q', state.q);
    if (state.cats.length) sp.set('cat', state.cats.join(','));
    if (state.brands.length) sp.set('brand', state.brands.join(','));
    if (state.min != null && state.min > priceMin) sp.set('min', state.min);
    if (state.max != null && state.max < priceMax) sp.set('max', state.max);
    if (state.rating) sp.set('rating', state.rating);
    if (state.colors.length) sp.set('color', state.colors.join(','));
    if (state.inStock) sp.set('instock', '1');
    if (state.discount) sp.set('discount', '1');
    if (state.sort !== 'popular') sp.set('sort', state.sort);
    if (state.page > 1) sp.set('page', state.page);
    const url = `${location.pathname}${sp.toString() ? `?${sp}` : ''}`;
    history[replace ? 'replaceState' : 'pushState'](null, '', url);
  }

  /* ---------- sync form controls with state ---------- */
  function syncForm() {
    if (!filtersForm) return;
    $$('input[name="cat"]', filtersForm).forEach((i) => { i.checked = state.cats.includes(i.value); });
    $$('input[name="brand"]', filtersForm).forEach((i) => { i.checked = state.brands.includes(i.value); });
    $$('input[name="color"]', filtersForm).forEach((i) => { i.checked = state.colors.includes(i.value); });
    $$('input[name="rating"]', filtersForm).forEach((i) => { i.checked = Number(i.value || 0) === state.rating; });
    const inStock = $('input[name="instock"]', filtersForm); if (inStock) inStock.checked = state.inStock;
    const disc = $('input[name="discount"]', filtersForm); if (disc) disc.checked = state.discount;
    const rMin = $('[data-range-min]', filtersForm); const rMax = $('[data-range-max]', filtersForm);
    if (rMin && rMax) { rMin.value = state.min ?? priceMin; rMax.value = state.max ?? priceMax; updateRange(); }
    $$('[data-sort]', page).forEach((b) => b.classList.toggle('is-active', b.dataset.sort === state.sort));
    const sel = $('[data-sort-select]', page); if (sel) sel.value = state.sort;
    $$('[data-view]', page).forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.view === state.view)));
    $$('[data-cat-chip]', page).forEach((c) => c.classList.toggle('is-active', state.cats.length === 1 ? c.dataset.catChip === state.cats[0] : c.dataset.catChip === '' && !state.cats.length));
  }

  function updateRange() {
    const rMin = $('[data-range-min]', filtersForm); const rMax = $('[data-range-max]', filtersForm);
    if (!rMin) return;
    let a = Number(rMin.value); let b = Number(rMax.value);
    if (a > b) { [a, b] = [b, a]; rMin.value = a; rMax.value = b; }
    const pct = (v) => ((v - priceMin) / (priceMax - priceMin || 1)) * 100;
    const fill = $('[data-range-fill]', filtersForm);
    if (fill) { fill.style.insetInlineStart = `${pct(a)}%`; fill.style.insetInlineEnd = `${100 - pct(b)}%`; }
    $('[data-range-min-label]', filtersForm).textContent = ctx.money(a);
    $('[data-range-max-label]', filtersForm).textContent = ctx.money(b);
    const iMin = $('[data-price-input="min"]', filtersForm); const iMax = $('[data-price-input="max"]', filtersForm);
    if (iMin && document.activeElement !== iMin) iMin.value = ctx.price(a, { symbol: false });
    if (iMax && document.activeElement !== iMax) iMax.value = ctx.price(b, { symbol: false });
  }

  /* ---------- render ---------- */
  let renderTimer;
  function render({ scroll = false } = {}) {
    results.classList.add('is-loading');
    results.setAttribute('aria-busy', 'true');
    clearTimeout(renderTimer);
    renderTimer = setTimeout(() => {
      const filtered = filterProducts(products, state).sort(sorters[state.sort] || sorters.popular);
      const total = filtered.length;
      const pages = Math.max(1, Math.ceil(total / perPage));
      if (state.page > pages) state.page = pages;
      const from = (state.page - 1) * perPage;
      const slice = filtered.slice(from, from + perPage);

      grid.classList.toggle('product-grid--list', state.view === 'list');
      grid.innerHTML = slice.map((p, i) => productCardHTML(ctx, p, { view: state.view, priority: i < 4 && state.page === 1 })).join('');
      syncPressedStates(grid);
      grid.hidden = total === 0;
      if (emptyBox) {
        emptyBox.hidden = total > 0;
        if (isSearch && !state.q && !hasFilters()) {
          const h = $('.empty__title', emptyBox); const p = $('.empty__text', emptyBox);
          if (h) h.textContent = ctx.t('search.noQuery'); if (p) p.textContent = ctx.t('search.noQueryText');
        } else if (isSearch) {
          const h = $('.empty__title', emptyBox); const p = $('.empty__text', emptyBox);
          if (h) h.textContent = ctx.t('search.noResults', { q: state.q }); if (p) p.textContent = ctx.t('search.noResultsText');
        }
      }
      if (countEl) countEl.innerHTML = total ? ctx.t('common.showing', { from: `<strong>${ctx.digits(from + 1)}</strong>`, to: `<strong>${ctx.digits(Math.min(from + perPage, total))}</strong>`, total: `<strong>${ctx.digits(total)}</strong>` }) : ctx.t('shop.results', { n: ctx.digits(0) });
      if (pagEl) pagEl.innerHTML = paginationHTML(ctx, { page: state.page, pages, hrefFor: (p) => { const sp = new URLSearchParams(location.search); sp.set('page', p); return `?${sp}`; } });
      renderActive();
      renderTitles(total);
      results.classList.remove('is-loading');
      results.setAttribute('aria-busy', 'false');
      if (scroll) scrollToEl(page.querySelector('.toolbar') || page, 120);
    }, 120);
  }

  function hasFilters() {
    return state.cats.length || state.brands.length || state.colors.length || state.rating || state.inStock || state.discount || (state.min != null && state.min > priceMin) || (state.max != null && state.max < priceMax);
  }

  function renderActive() {
    if (!activeEl) return;
    const chips = [];
    const chip = (label, key, value) => `<button type="button" class="chip is-active" data-remove-filter="${key}" data-value="${esc(value)}">${esc(label)}<span class="chip__remove icon icon--xs linear-icon-cross" aria-hidden="true"></span><span class="visually-hidden">${esc(ctx.t('common.remove'))}</span></button>`;
    const catName = (slug) => { for (const c of catalog.categories) { if (c.slug === slug) return c.name; const ch = c.children.find((x) => x.slug === slug); if (ch) return ch.name; } return slug; };
    state.cats.forEach((c) => chips.push(chip(catName(c), 'cat', c)));
    state.brands.forEach((b) => chips.push(chip(catalog.brands.find((x) => x.slug === b)?.name || b, 'brand', b)));
    if ((state.min != null && state.min > priceMin) || (state.max != null && state.max < priceMax)) chips.push(chip(`${ctx.money(state.min ?? priceMin)} – ${ctx.money(state.max ?? priceMax)}`, 'price', ''));
    if (state.rating) chips.push(chip(ctx.t('shop.ratingLabel', { n: ctx.digits(state.rating) }), 'rating', ''));
    state.colors.forEach((c) => chips.push(`<button type="button" class="chip is-active" data-remove-filter="color" data-value="${esc(c)}"><span class="product-card__swatch" style="background:${esc(c)}"></span>${esc(ctx.t('common.color'))}<span class="chip__remove icon icon--xs linear-icon-cross" aria-hidden="true"></span></button>`));
    if (state.inStock) chips.push(chip(ctx.t('shop.onlyInStock'), 'instock', ''));
    if (state.discount) chips.push(chip(ctx.t('shop.onlyDiscount'), 'discount', ''));
    activeEl.innerHTML = chips.length ? `<span class="small text-muted">${esc(ctx.t('shop.activeFilters'))}</span>${chips.join('')}<button type="button" class="btn btn--link btn--sm" data-filters-clear>${esc(ctx.t('common.clearAll'))}</button>` : '';
    const n = chips.length;
    $$('[data-filters-count]').forEach((b) => { b.textContent = ctx.digits(n); b.hidden = n === 0; });
  }

  function renderTitles(total) {
    const title = $('[data-shop-title]'); const sub = $('[data-shop-subtitle]'); const q = $('[data-search-query]');
    if (title && state.cats.length === 1) {
      const c = catalog.categories.find((x) => x.slug === state.cats[0]) || catalog.categories.flatMap((x) => x.children).find((x) => x.slug === state.cats[0]);
      if (c) { title.textContent = c.name; if (sub) sub.textContent = ctx.t('shop.results', { n: ctx.digits(total) }); document.title = `${c.name}${ctx.L.meta?.titleSuffix || ''}`; }
    } else if (title && state.brands.length === 1) {
      const b = catalog.brands.find((x) => x.slug === state.brands[0]);
      if (b) { title.textContent = b.name; if (sub) sub.textContent = ctx.t('shop.results', { n: ctx.digits(total) }); }
    } else if (title) { title.textContent = ctx.t('shop.bannerTitle'); if (sub) sub.textContent = ctx.t('shop.bannerText'); }
    if (q) q.innerHTML = state.q ? `${esc(ctx.t('search.resultsFor', { q: state.q }))} — <strong>${esc(ctx.t('search.count', { n: ctx.digits(total) }))}</strong>` : '';
    const popular = $('[data-search-popular]'); if (popular) popular.hidden = !!state.q;
  }

  /* ---------- events ---------- */
  function apply({ resetPage = true, scroll = false } = {}) {
    if (resetPage) state.page = 1;
    writeURL();
    render({ scroll });
  }

  if (filtersForm) {
    filtersForm.addEventListener('change', (e) => {
      const f = e.target;
      if (f.name === 'cat') state.cats = $$('input[name="cat"]:checked', filtersForm).map((i) => i.value);
      else if (f.name === 'brand') state.brands = $$('input[name="brand"]:checked', filtersForm).map((i) => i.value);
      else if (f.name === 'color') state.colors = $$('input[name="color"]:checked', filtersForm).map((i) => i.value);
      else if (f.name === 'rating') state.rating = Number(f.value) || 0;
      else if (f.name === 'instock') state.inStock = f.checked;
      else if (f.name === 'discount') state.discount = f.checked;
      else if (f.name === 'min' || f.name === 'max') { updateRange(); state.min = Number($('[data-range-min]', filtersForm).value); state.max = Number($('[data-range-max]', filtersForm).value); }
      else return;
      apply();
    });
    filtersForm.addEventListener('input', (e) => { if (e.target.name === 'min' || e.target.name === 'max') updateRange(); });
    filtersForm.addEventListener('submit', (e) => e.preventDefault());
    $$('[data-price-input]', filtersForm).forEach((inp) => {
      inp.addEventListener('change', () => {
        const raw = Number(String(inp.value).replace(/[۰-۹]/g, (d) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d)).replace(/[^\d.]/g, ''));
        if (!raw) return;
        const base = ctx.currency.rate && ctx.currency.rate !== 1 ? raw / ctx.currency.rate : raw;
        const v = Math.max(priceMin, Math.min(priceMax, Math.round(base)));
        if (inp.dataset.priceInput === 'min') { state.min = v; $('[data-range-min]', filtersForm).value = v; } else { state.max = v; $('[data-range-max]', filtersForm).value = v; }
        updateRange(); apply();
      });
    });
    $('[data-brand-search]', filtersForm)?.addEventListener('input', debounce((e) => {
      const q = e.target.value.trim().toLowerCase();
      $$('[data-brand-item]', filtersForm).forEach((l) => { l.hidden = q && !l.dataset.brandItem.toLowerCase().includes(q); });
    }, 100));
  }

  on(document, 'click', '[data-filters-clear]', (e) => {
    e.preventDefault();
    Object.assign(state, { cats: [], brands: [], min: null, max: null, rating: 0, colors: [], inStock: false, discount: false });
    syncForm(); apply();
  });
  on(page, 'click', '[data-remove-filter]', (e, btn) => {
    const k = btn.dataset.removeFilter; const v = btn.dataset.value;
    if (k === 'cat') state.cats = state.cats.filter((x) => x !== v);
    else if (k === 'brand') state.brands = state.brands.filter((x) => x !== v);
    else if (k === 'color') state.colors = state.colors.filter((x) => x !== v);
    else if (k === 'price') { state.min = null; state.max = null; }
    else if (k === 'rating') state.rating = 0;
    else if (k === 'instock') state.inStock = false;
    else if (k === 'discount') state.discount = false;
    syncForm(); apply();
  });
  on(page, 'click', '[data-sort]', (e, btn) => { state.sort = btn.dataset.sort; syncForm(); apply(); });
  $('[data-sort-select]', page)?.addEventListener('change', (e) => { state.sort = e.target.value; syncForm(); apply(); });
  on(page, 'click', '[data-view]', (e, btn) => { state.view = btn.dataset.view; try { localStorage.setItem('nx:view', state.view); } catch { /* noop */ } syncForm(); render(); });
  on(page, 'click', '[data-cat-chip]', (e, a) => { e.preventDefault(); state.cats = a.dataset.catChip ? [a.dataset.catChip] : []; syncForm(); apply(); });
  on(page, 'click', '.pagination__link[data-page]', (e, a) => { e.preventDefault(); if (a.classList.contains('is-disabled')) return; state.page = Number(a.dataset.page); writeURL(); render({ scroll: true }); });
  on(page, 'click', '.empty .btn', (e, a) => { if (a.getAttribute('href') === '#') { e.preventDefault(); $('[data-filters-clear]')?.click(); } });

  // Search page: submitting the inline search form filters in place
  const searchForm = $('[data-search-page] form', page);
  searchForm?.addEventListener('submit', (e) => { e.preventDefault(); state.q = $('[data-search-input]', searchForm).value.trim(); apply(); });

  window.addEventListener('popstate', () => { readURL(); syncForm(); render(); });

  /* ---------- sidebar ↔ drawer relocation ---------- */
  const mql = mq('(max-width: 991.98px)');
  const relocate = () => {
    if (!filtersForm || !drawerBody || !filtersHost) return;
    if (mql.matches) { if (filtersForm.parentElement !== drawerBody) drawerBody.append(filtersForm); }
    else if (filtersForm.parentElement !== filtersHost) filtersHost.append(filtersForm);
  };
  mql.addEventListener('change', relocate);
  relocate();
  document.getElementById('drawer-filters')?.addEventListener('drawer:open', () => { $('[data-filters-apply-label]').textContent = ctx.t('shop.applyFilters'); });

  /* ---------- go ---------- */
  readURL();
  syncForm();
  render();
}
