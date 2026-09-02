/**
 * Cart / wishlist / compare UI glue:
 *  - header badges + mini cart rendering
 *  - global [data-action] click handling (add-to-cart, wishlist, compare, quick view, share)
 *  - compare floating bar
 *  - quantity steppers
 */
import { $, $$, on, announce } from '../core/dom.js';
import * as store from '../store/state.js';
import { miniCartItemHTML } from '../core/render.js';
import { quickViewHTML } from '../core/render-pages.js';
import { toast } from './toast.js';
import { openModal } from './drawer.js';

let ctx;

export function cartLines() {
  return store.getState().cart.map((i) => ({ ...i, product: ctx.product(i.id) })).filter((i) => i.product);
}

export function cartTotals() {
  const lines = cartLines();
  const subtotalOld = lines.reduce((a, l) => a + (l.product.oldPrice || l.product.price) * l.qty, 0);
  const subtotal = lines.reduce((a, l) => a + l.product.price * l.qty, 0);
  const discount = subtotalOld - subtotal;
  const coupons = ctx.config.coupons || {};
  const code = store.getState().coupon;
  const coupon = code && coupons[code] ? coupons[code] : null;
  const couponValue = coupon ? (coupon.type === 'percent' ? Math.round(subtotal * coupon.value / 100) : coupon.value) : 0;
  const afterCoupon = Math.max(0, subtotal - couponValue);
  const freeShipping = ctx.config.freeShipping || 0;
  const taxRate = ctx.config.taxRate || 0;
  return { lines, count: lines.reduce((a, l) => a + l.qty, 0), subtotal, subtotalOld, discount, coupon: code && coupon ? code : null, couponValue, afterCoupon, freeShipping, freeShippingLeft: Math.max(0, freeShipping - afterCoupon), taxRate };
}

export function initCartUI(appCtx) {
  ctx = appCtx;
  renderBadges();
  renderMiniCart();
  renderCompareBar();
  syncPressedStates(document);

  store.subscribe(({ type, payload }) => {
    renderBadges();
    if (type.startsWith('cart') || type === 'sync' || type === 'coupon') renderMiniCart();
    if (type === 'compare' || type === 'sync') renderCompareBar();
    syncPressedStates(document);
    if (type === 'auth' || type === 'sync') renderAuth();
  });
  renderAuth();

  /* ---- Global action delegation ---- */
  on(document, 'click', '[data-action]', (e, btn) => {
    const action = btn.dataset.action;
    const id = Number(btn.dataset.id);
    const product = id ? ctx.product(id) : null;
    switch (action) {
      case 'add-to-cart': { e.preventDefault(); addToCart(btn, product); break; }
      case 'buy-now': { e.preventDefault(); if (addToCart(btn, product, { silent: true })) location.href = ctx.url('checkout.html'); break; }
      case 'wishlist': { e.preventDefault(); if (!product) return; const added = store.wishlistToggle(id); toast(ctx.t(added ? 'toast.addedToWishlist' : 'toast.removedFromWishlist'), { type: added ? 'success' : 'default', action: added ? ctx.t('header.wishlist') : undefined, actionHref: ctx.url('wishlist.html') }); break; }
      case 'compare': { e.preventDefault(); if (!product) return; const r = store.compareToggle(id, ctx.config.compareLimit || 4); if (r.limit) toast(ctx.t('toast.compareLimit'), { type: 'error' }); else toast(ctx.t(r.added ? 'toast.addedToCompare' : 'toast.removedFromCompare'), { type: r.added ? 'success' : 'default', action: r.added ? ctx.t('common.compareNow') : undefined, actionHref: ctx.url('compare.html') }); break; }
      case 'quick-view': { e.preventDefault(); openQuickView(product); break; }
      case 'cart-remove': { e.preventDefault(); const removed = store.cartRemove(btn.dataset.key); if (removed) { const p = ctx.product(removed.id); toast(ctx.t('toast.removedFromCart', { name: p?.name || '' }), { onUndo: () => store.cartRestore(removed) }); } break; }
      case 'cart-to-wishlist': { e.preventDefault(); store.cartRemove(btn.dataset.key); if (!store.wishlistHas(id)) store.wishlistToggle(id); toast(ctx.t('toast.addedToWishlist'), { type: 'success' }); break; }
      case 'share': { e.preventDefault(); share(); break; }
      case 'copy-link': { e.preventDefault(); copyLink(); break; }
      case 'notify': { e.preventDefault(); toast(ctx.t('toast.saved'), { type: 'success' }); break; }
      default: break;
    }
  });

  /* ---- Quantity steppers (generic) ---- */
  on(document, 'click', '[data-qty] button', (e, btn) => {
    const wrap = btn.closest('[data-qty]');
    const input = $('input', wrap);
    const max = Number(wrap.dataset.max || input.max || 5);
    let v = Number(input.value) || 1;
    v = btn.hasAttribute('data-qty-inc') ? v + 1 : v - 1;
    if (v > max) { toast(ctx.t('toast.maxQty', { n: ctx.digits(max) }), { type: 'info' }); v = max; }
    v = Math.max(1, v);
    input.value = v;
    updateQtyButtons(wrap);
    input.dispatchEvent(new Event('change', { bubbles: true }));
  });
  on(document, 'change', '[data-qty] input', (e, input) => {
    const wrap = input.closest('[data-qty]');
    const max = Number(wrap.dataset.max || input.max || 5);
    let v = Math.round(Number(input.value) || 1);
    if (v > max) { toast(ctx.t('toast.maxQty', { n: ctx.digits(max) }), { type: 'info' }); v = max; }
    input.value = Math.max(1, v);
    updateQtyButtons(wrap);
    if (wrap.dataset.key) store.cartSetQty(wrap.dataset.key, input.value, max);
  });

  /* ---- Variant label updates ---- */
  on(document, 'change', '[data-variant] input', (e, input) => {
    const fs = input.closest('[data-variant]');
    const label = $('[data-variant-value]', fs);
    if (label) label.textContent = input.value;
  });

  /* ---- Compare bar clear ---- */
  $('[data-compare-clear]')?.addEventListener('click', () => store.compareClear());

  /* ---- Logout ---- */
  on(document, 'click', '[data-logout]', (e) => {
    e.preventDefault();
    if (confirm(ctx.t('account.logoutConfirm'))) { store.logout(); toast(ctx.t('account.loggedOut'), { type: 'success' }); setTimeout(() => { location.href = ctx.url('index.html'); }, 700); }
  });
}

function updateQtyButtons(wrap) {
  const input = $('input', wrap);
  const max = Number(wrap.dataset.max || input.max || 5);
  const v = Number(input.value) || 1;
  const dec = $('[data-qty-dec]', wrap); const inc = $('[data-qty-inc]', wrap);
  if (dec) dec.disabled = v <= 1;
  if (inc) inc.disabled = v >= max;
}

function readVariants(btn) {
  const scope = btn.closest('[data-product-page]') || document;
  const form = $('[data-variants]', scope);
  const color = form ? $('input[name="color"]:checked', form) : null;
  const size = form ? $('input[name="size"]:checked', form) : null;
  const qtyInput = $('[data-qty] input', scope);
  return { color: color?.value || '', colorHex: color?.dataset.hex || '', size: size?.value || '', qty: Number(qtyInput?.value) || 1 };
}

export function addToCart(btn, product, { silent = false } = {}) {
  if (!product) return false;
  if (!product.inStock) { toast(ctx.t('toast.outOfStock'), { type: 'error' }); return false; }
  const withVariants = btn.hasAttribute('data-with-variants');
  const v = withVariants ? readVariants(btn) : { color: product.colors?.[0]?.name || '', colorHex: product.colors?.[0]?.hex || '', size: '', qty: 1 };
  const { clamped } = store.cartAdd({ id: product.id, qty: v.qty, color: v.color, colorHex: v.colorHex, size: v.size, max: product.maxQty });
  if (clamped) toast(ctx.t('toast.maxQty', { n: ctx.digits(product.maxQty) }), { type: 'info' });
  else if (!silent) toast(ctx.t('toast.addedToCart', { name: product.name }), { type: 'success', action: ctx.t('toast.viewCart'), actionHref: ctx.url('cart.html') });
  // micro feedback
  btn.classList.add('is-added');
  const label = $('.product-card__add-text, span:not(.icon)', btn);
  const prevText = label?.textContent;
  if (label) label.textContent = ctx.t('common.added');
  setTimeout(() => { btn.classList.remove('is-added'); if (label && prevText) label.textContent = prevText; }, 1400);
  announce(ctx.t('toast.addedToCart', { name: product.name }));
  return true;
}

/* ---------------- Badges ---------------- */
function renderBadges() {
  const counts = { cart: store.cartCount(), wishlist: store.getState().wishlist.length, compare: store.getState().compare.length };
  $$('[data-count]').forEach((el) => {
    const n = counts[el.dataset.count] ?? 0;
    el.textContent = ctx.digits(n);
    el.setAttribute('data-count', el.dataset.count); // keep type
    el.hidden = n === 0 && el.classList.contains('icon-btn__badge');
    if (el.classList.contains('badge')) el.hidden = false;
  });
  const total = $('[data-cart-total]');
  if (total) { const t = cartTotals(); total.textContent = t.count ? ctx.money(t.afterCoupon) : ctx.t('header.cartEmptyShort'); }
}

/* ---------------- Mini cart ---------------- */
function renderMiniCart() {
  const list = $('[data-mini-cart-list]');
  if (!list) return;
  const t = cartTotals();
  const empty = $('[data-mini-cart-empty]'); const foot = $('[data-mini-cart-foot]'); const count = $('[data-mini-cart-count]');
  list.innerHTML = t.lines.map((l) => miniCartItemHTML(ctx, l)).join('');
  if (empty) empty.hidden = t.count > 0;
  if (foot) foot.hidden = t.count === 0;
  if (count) count.textContent = t.count ? `${ctx.digits(t.count)} ${ctx.t('header.items')}` : '';
  const sub = $('[data-mini-cart-subtotal]'); if (sub) sub.textContent = ctx.money(t.afterCoupon);
}

/* ---------------- Pressed states (wishlist / compare buttons) ---------------- */
export function syncPressedStates(root) {
  $$('[data-action="wishlist"][data-id]', root).forEach((b) => {
    const active = store.wishlistHas(Number(b.dataset.id));
    b.classList.toggle('is-active', active);
    b.setAttribute('aria-pressed', String(active));
    b.setAttribute('aria-label', ctx.t(active ? 'common.removeFromWishlist' : 'common.addToWishlist'));
    b.title = b.getAttribute('aria-label');
  });
  $$('[data-action="compare"][data-id]', root).forEach((b) => {
    const active = store.compareHas(Number(b.dataset.id));
    b.classList.toggle('is-active', active);
    b.setAttribute('aria-pressed', String(active));
    b.setAttribute('aria-label', ctx.t(active ? 'common.removeFromCompare' : 'common.addToCompare'));
    b.title = b.getAttribute('aria-label');
  });
}

/* ---------------- Compare bar ---------------- */
function renderCompareBar() {
  const bar = $('[data-compare-bar]');
  if (!bar) return;
  const ids = store.getState().compare;
  const hideOn = ['compare', 'checkout', 'login', 'register', 'forgot'];
  bar.classList.toggle('is-visible', ids.length > 0 && !hideOn.includes(ctx.page));
  $('[data-compare-thumbs]', bar).innerHTML = ids.map((id) => ctx.product(id)).filter(Boolean).map((p) => `<img src="${ctx.img(p.image)}" width="32" height="32" alt="${p.name}">`).join('');
  $('[data-compare-label]', bar).textContent = ctx.t('compare.bar', { n: ctx.digits(ids.length) });
}

/* ---------------- Quick view ---------------- */
function openQuickView(product) {
  if (!product) return;
  const dialog = $('[data-quick-view]');
  const body = $('[data-quick-view-body]', dialog);
  if (!dialog || !body) { location.href = ctx.productUrl(product); return; }
  body.innerHTML = quickViewHTML(ctx, product);
  syncPressedStates(body);
  openModal(dialog);
  $('.modal__close', dialog)?.focus();
}

/* ---------------- Auth label ---------------- */
function renderAuth() {
  const user = store.getState().user;
  $$('[data-auth-link]').forEach((a) => {
    a.href = ctx.url(user ? 'account.html' : 'login.html');
  });
  $$('[data-auth-label]').forEach((el) => { el.textContent = user ? user.name : ctx.t('header.login'); });
  if (user) {
    $$('[data-user-name]').forEach((el) => { el.textContent = user.name; });
    $$('[data-user-email]').forEach((el) => { el.textContent = user.email || el.textContent; });
    $$('[data-user-initial]').forEach((el) => { el.textContent = user.name.trim().charAt(0); });
  }
}

/* ---------------- Share ---------------- */
async function share() {
  const data = { title: document.title, url: location.href };
  try {
    if (navigator.share) { await navigator.share(data); return; }
  } catch { /* cancelled */ return; }
  copyLink();
}
async function copyLink() {
  try { await navigator.clipboard.writeText(location.href); toast(ctx.t('common.linkCopied'), { type: 'success' }); }
  catch { toast(location.href, { type: 'info', duration: 6000 }); }
}
