/** Live search suggestions for every [data-search] box (header, search page, 404). */
import { $, $$, debounce } from '../core/dom.js';
import { searchProducts } from '../core/catalog.js';
import { suggestItemHTML } from '../core/render.js';
import { esc } from '../core/i18n.js';

const POPULAR = { fa: ['هدفون', 'گوشی', 'کفش', 'چرم', 'سرم', 'ساعت'], en: ['headphones', 'phone', 'sneakers', 'leather', 'serum', 'watch'] };

export function initSearch(ctx) {
  $$('[data-search]').forEach((box) => {
    const input = $('[data-search-input]', box);
    const panel = $('[data-search-suggest]', box);
    const form = $('form', box);
    if (!input || !panel) return;
    let active = -1;

    const close = () => { box.classList.remove('is-open'); input.setAttribute('aria-expanded', 'false'); active = -1; };
    const open = () => { box.classList.add('is-open'); input.setAttribute('aria-expanded', 'true'); };

    const renderPopular = () => {
      const chips = POPULAR[ctx.lang] || POPULAR.en;
      panel.innerHTML = `<div class="search__suggest-title">${esc(ctx.t('header.popularSearches'))}</div><div class="search__suggest-chips">${chips.map((c) => `<a class="chip" href="${ctx.url('search.html')}?q=${encodeURIComponent(c)}" role="option">${esc(c)}</a>`).join('')}</div>`;
    };

    const render = () => {
      const q = input.value.trim();
      if (q.length < 2) { renderPopular(); open(); return; }
      const results = searchProducts(ctx.catalog.products, q, 6);
      if (!results.length) {
        panel.innerHTML = `<div class="search__suggest-empty">${esc(ctx.t('header.noSuggestion'))}</div>`;
      } else {
        panel.innerHTML = `<div class="search__suggest-title">${esc(ctx.t('header.suggestedProducts'))}</div><div class="search__suggest-list">${results.map((p) => suggestItemHTML(ctx, p)).join('')}</div><a class="search__suggest-item fw-medium" href="${ctx.url('search.html')}?q=${encodeURIComponent(q)}" role="option" style="justify-content:center;color:var(--color-primary-hover)">${esc(ctx.t('shop.searchResultsFor'))} «${esc(q)}»</a>`;
      }
      open();
    };

    input.addEventListener('input', debounce(render, 150));
    input.addEventListener('focus', render);
    input.addEventListener('keydown', (e) => {
      const items = $$('[role="option"]', panel);
      if (e.key === 'Escape') { close(); return; }
      if (!items.length) return;
      if (e.key === 'ArrowDown') { e.preventDefault(); active = (active + 1) % items.length; items[active].focus(); }
    });
    panel.addEventListener('keydown', (e) => {
      const items = $$('[role="option"]', panel);
      const idx = items.indexOf(document.activeElement);
      if (e.key === 'ArrowDown') { e.preventDefault(); items[(idx + 1) % items.length]?.focus(); }
      else if (e.key === 'ArrowUp') { e.preventDefault(); if (idx <= 0) input.focus(); else items[idx - 1].focus(); }
      else if (e.key === 'Escape') { close(); input.focus(); }
    });
    document.addEventListener('click', (e) => { if (!box.contains(e.target)) close(); });
    box.addEventListener('focusout', (e) => { if (!box.contains(e.relatedTarget)) setTimeout(() => { if (!box.contains(document.activeElement)) close(); }, 0); });
    form?.addEventListener('submit', (e) => { if (!input.value.trim()) e.preventDefault(); });

    // pre-fill from ?q=
    const q = new URLSearchParams(location.search).get('q');
    if (q && !input.value) input.value = q;
  });
}
