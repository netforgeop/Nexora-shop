/** Wishlist page, compare page, blog filtering, account demo pages. */
import { $, $$, on } from '../core/dom.js';
import * as store from '../store/state.js';
import { wishItemHTML, ratingHTML, priceHTML, icon } from '../core/render.js';
import { esc } from '../core/i18n.js';
import { toast } from '../components/toast.js';
import { addToCart } from '../components/cart-ui.js';
import { validateForm, liveValidation } from '../components/misc.js';
import { openModal } from '../components/drawer.js';

/* ---------------- Wishlist ---------------- */
export function initWishlistPage(ctx) {
  const page = $('[data-wishlist-page]');
  if (!page) return;
  const items = $('[data-wishlist-items]', page); const filled = $('[data-wishlist-filled]', page); const empty = $('[data-wishlist-empty]', page);
  const actions = $('[data-wishlist-actions]', page); const count = $('[data-wishlist-count]', page);
  function render() {
    const list = store.getState().wishlist.map((id) => ctx.product(id)).filter(Boolean);
    filled.hidden = !list.length; empty.hidden = !!list.length; if (actions) actions.hidden = !list.length;
    if (count) count.textContent = list.length ? `(${ctx.t('wishlist.count', { n: ctx.digits(list.length) })})` : '';
    items.innerHTML = list.map((p) => wishItemHTML(ctx, p)).join('');
  }
  render();
  store.subscribe(({ type }) => { if (type === 'wishlist' || type === 'sync') render(); });
  $('[data-wishlist-clear]', page)?.addEventListener('click', () => { if (confirm(ctx.t('cart.clearConfirm'))) store.wishlistClear(); });
  $('[data-wishlist-add-all]', page)?.addEventListener('click', (e) => {
    const list = store.getState().wishlist.map((id) => ctx.product(id)).filter((p) => p && p.inStock);
    list.forEach((p) => store.cartAdd({ id: p.id, qty: 1, color: p.colors?.[0]?.name || '', colorHex: p.colors?.[0]?.hex || '', max: p.maxQty }));
    toast(ctx.t('toast.addedToCart', { name: `${ctx.digits(list.length)} ${ctx.t('common.product')}` }), { type: 'success', action: ctx.t('toast.viewCart'), actionHref: ctx.url('cart.html') });
  });
}

/* ---------------- Compare ---------------- */
export function initComparePage(ctx) {
  const page = $('[data-compare-page]');
  if (!page) return;
  const table = $('[data-compare-table]', page); const filled = $('[data-compare-filled]', page); const empty = $('[data-compare-empty]', page); const clear = $('[data-compare-clear-page]', page);
  const t = ctx.t;
  function render() {
    const list = store.getState().compare.map((id) => ctx.product(id)).filter(Boolean);
    filled.hidden = !list.length; empty.hidden = !!list.length; clear.hidden = !list.length;
    if (!list.length) return;
    const row = (label, cells) => `<tr><th scope="row">${esc(label)}</th>${cells.map((c) => `<td>${c}</td>`).join('')}</tr>`;
    const specKeys = [];
    list.forEach((p) => (p.highlights || []).forEach((h, i) => { if (!specKeys[i]) specKeys[i] = true; }));
    table.innerHTML = `
      <tbody>
        ${row(t('compare.image'), list.map((p) => `<div style="position:relative"><a href="${ctx.productUrl(p)}"><img src="${ctx.img(p.image)}" width="200" height="200" alt="${esc(p.name)}"></a><button type="button" class="icon-btn icon-btn--light icon-btn--circle" data-action="compare" data-id="${p.id}" aria-label="${esc(t('common.removeFromCompare'))}" style="position:absolute;inset-block-start:4px;inset-inline-end:4px">${icon('cross', 'xs')}</button></div>`))}
        ${row(t('compare.name'), list.map((p) => `<a class="fw-medium text-strong" href="${ctx.productUrl(p)}">${esc(p.name)}</a>`))}
        ${row(t('compare.price'), list.map((p) => priceHTML(ctx, p, { showDiscount: true })))}
        ${row(t('compare.rating'), list.map((p) => ratingHTML(ctx, p.rating, p.reviewCount)))}
        ${row(t('compare.brand'), list.map((p) => `<span class="ltr">${esc(p.brandName)}</span>`))}
        ${row(t('common.category'), list.map((p) => esc(p.categoryName)))}
        ${row(t('compare.availability'), list.map((p) => p.inStock ? `<span class="status status--success">${esc(t('common.inStock'))}</span>` : `<span class="status status--danger">${esc(t('common.outOfStock'))}</span>`))}
        ${row(t('compare.colors'), list.map((p) => p.colors.length ? `<div class="cluster" style="gap:6px">${p.colors.map((c) => `<span class="swatch" style="inline-size:22px;block-size:22px;background:${esc(c.hex)}" title="${esc(c.name)}"></span>`).join('')}</div>` : '—'))}
        ${row(t('product.highlights'), list.map((p) => `<ul class="review__pros review__pros--plus" style="margin:0">${(p.highlights || []).map((h) => `<li>${icon('check')} ${esc(h)}</li>`).join('')}</ul>`))}
        ${row(t('compare.actions'), list.map((p) => `<button type="button" class="btn btn--dark btn--sm btn--block" data-action="add-to-cart" data-id="${p.id}"${p.inStock ? '' : ' disabled'}>${icon('cart-add', 'xs')}${esc(t('common.addToCart'))}</button>`))}
      </tbody>`;
  }
  render();
  store.subscribe(({ type }) => { if (type === 'compare' || type === 'sync') render(); });
  clear.addEventListener('click', () => store.compareClear());
}

/* ---------------- Blog list filtering ---------------- */
export function initBlogPage(ctx) {
  const page = $('[data-blog-page]');
  if (!page) return;
  const posts = $$('[data-post]', page); const empty = $('[data-blog-empty]', page);
  const sp = new URLSearchParams(location.search);
  const state = { cat: sp.get('cat') || '', tag: sp.get('tag') || '', q: '' };
  const apply = () => {
    let n = 0;
    posts.forEach((p) => {
      const ok = (!state.cat || p.dataset.cat === state.cat) && (!state.tag || p.dataset.tags.split(',').includes(state.tag)) && (!state.q || p.dataset.title.toLowerCase().includes(state.q));
      p.hidden = !ok; if (ok) n++;
    });
    if (empty) empty.hidden = n > 0;
    $$('[data-blog-cat]', page).forEach((b) => b.setAttribute('aria-selected', String(b.dataset.blogCat === state.cat)));
  };
  on(page, 'click', '[data-blog-cat]', (e, b) => { state.cat = b.dataset.blogCat; state.tag = ''; apply(); });
  $('[data-blog-search]', page)?.addEventListener('input', (e) => { state.q = e.target.value.trim().toLowerCase(); apply(); });
  $('[data-blog-search]', page)?.addEventListener('submit', (e) => e.preventDefault());
  apply();
}

/* ---------------- Account: orders filter, addresses modal, reorder ---------------- */
export function initAccountPages(ctx) {
  const filter = $('[data-order-filter]');
  if (filter) {
    on(filter, 'click', '[data-order-status]', (e, b) => {
      const s = b.dataset.orderStatus;
      $$('[data-order-status]', filter).forEach((x) => x.setAttribute('aria-selected', String(x === b)));
      let n = 0;
      $$('[data-order-row]').forEach((r) => { const ok = !s || r.dataset.orderRow === s; r.hidden = !ok; if (ok) n++; });
      const empty = $('[data-orders-empty]'); if (empty) empty.hidden = n > 0;
    });
  }
  on(document, 'click', '[data-reorder]', (e, b) => {
    const codes = { 'NX-240871': [2001] };
    (codes[b.dataset.reorder] || [1001]).forEach((id) => { const p = ctx.product(id); if (p) addToCart(b, p, { silent: true }); });
    toast(ctx.t('toast.addedToCart', { name: b.dataset.reorder }), { type: 'success', action: ctx.t('toast.viewCart'), actionHref: ctx.url('cart.html') });
  });

  const addrPage = $('[data-addresses-page]');
  if (addrPage) {
    const modal = $('[data-address-modal]'); const form = $('[data-address-form]', modal); const list = $('[data-address-list]', addrPage);
    let editing = null;
    liveValidation(form, ctx);
    on(addrPage, 'click', '[data-address-add]', () => { editing = null; form.reset(); $('[data-address-modal-title]', modal).textContent = ctx.t('account.addAddress'); openModal(modal); $('input', form).focus(); });
    on(addrPage, 'click', '[data-address-edit]', (e, b) => {
      editing = b.closest('[data-address]');
      $('[data-address-modal-title]', modal).textContent = ctx.t('account.editAddress');
      form.title.value = $('[data-address-title]', editing).textContent.trim();
      form.receiver.value = $('[data-address-receiver]', editing).textContent.trim();
      form.phone.value = $('[data-address-phone]', editing).textContent.trim();
      form.address.value = $('[data-address-text]', editing).textContent.trim();
      form.city.value = form.city.value || (ctx.lang === 'fa' ? 'تهران' : 'San Jose');
      form.postal.value = form.postal.value || '1968934511';
      openModal(modal); $('input', form).focus();
    });
    on(addrPage, 'click', '[data-address-delete]', (e, b) => { if (confirm(ctx.t('account.deleteAddressConfirm'))) { b.closest('[data-address]').remove(); toast(ctx.t('toast.saved'), { type: 'success' }); } });
    on(addrPage, 'click', '[data-address-default]', (e, b) => {
      $$('[data-address]', list).forEach((c) => { c.classList.remove('is-default'); $('.badge', c)?.remove(); });
      const card = b.closest('[data-address]'); card.classList.add('is-default');
      $('.address-card__title', card).insertAdjacentHTML('beforeend', ` <span class="badge badge--discount">${esc(ctx.t('account.default'))}</span>`);
      toast(ctx.t('toast.saved'), { type: 'success' });
    });
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      if (!validateForm(form, ctx)) return;
      const data = Object.fromEntries(new FormData(form).entries());
      const html = `<div class="address-card__title">${icon('map-marker', 'xs')}<span data-address-title>${esc(data.title)}</span>${data.default ? ` <span class="badge badge--discount">${esc(ctx.t('account.default'))}</span>` : ''}</div><p class="address-card__text" data-address-text>${esc(data.city)}، ${esc(data.address)}</p><div class="address-card__meta"><span data-address-receiver>${esc(data.receiver)}</span> · <span class="ltr" data-address-phone>${esc(data.phone)}</span> · <span class="ltr">${esc(data.postal)}</span></div><div class="address-card__actions"><button type="button" data-address-edit>${icon('pencil', 'xs')}${esc(ctx.t('common.edit'))}</button>${data.default ? '' : `<button type="button" data-address-default>${icon('check', 'xs')}${esc(ctx.t('account.setDefault'))}</button>`}<button type="button" data-address-delete>${icon('trash2', 'xs')}${esc(ctx.t('common.delete'))}</button></div>`;
      if (data.default) $$('[data-address]', list).forEach((c) => { c.classList.remove('is-default'); $('.badge', c)?.remove(); });
      if (editing) { editing.innerHTML = html; editing.classList.toggle('is-default', !!data.default); }
      else { const card = document.createElement('article'); card.className = `address-card${data.default ? ' is-default' : ''}`; card.setAttribute('data-address', ''); card.innerHTML = html; list.insertBefore(card, $('[data-address-add]', list)); }
      modal.close(); toast(ctx.t('toast.saved'), { type: 'success' });
    });
  }
}
