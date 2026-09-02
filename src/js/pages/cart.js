/** Cart page + checkout page (shared summary rendering). */
import { $, $$, on } from '../core/dom.js';
import * as store from '../store/state.js';
import { cartItemHTML, summaryItemHTML } from '../core/render.js';
import { cartTotals } from '../components/cart-ui.js';
import { toast } from '../components/toast.js';
import { validateForm, liveValidation } from '../components/misc.js';

function renderSummary(ctx, root, { shippingKey = null, shippingRates = {} } = {}) {
  const t = cartTotals();
  const set = (sel, val) => { const el = $(sel, root); if (el) el.textContent = val; };
  const rate = shippingKey ? (shippingRates[shippingKey] ?? 0) : 0;
  const shipping = t.afterCoupon >= t.freeShipping ? 0 : (shippingKey ? rate : null);
  const tax = Math.round(t.afterCoupon * t.taxRate);
  const total = t.afterCoupon + (shipping || 0) + tax;
  set('[data-summary-count]', `${ctx.digits(t.count)} ${ctx.t('header.items')}`);
  set('[data-summary-subtotal]', ctx.money(t.subtotalOld));
  const dRow = $('[data-summary-discount-row]', root); if (dRow) { dRow.hidden = t.discount <= 0; set('[data-summary-discount]', `− ${ctx.money(t.discount)}`); }
  const cRow = $('[data-summary-coupon-row]', root); if (cRow) { cRow.hidden = !t.coupon; set('[data-summary-coupon-code]', t.coupon || ''); set('[data-summary-coupon]', `− ${ctx.money(t.couponValue)}`); }
  set('[data-summary-shipping]', shipping === 0 ? ctx.t('cart.shippingFree') : shipping == null ? ctx.t('cart.shippingCalc') : ctx.money(shipping));
  set('[data-summary-tax]', ctx.money(tax));
  set('[data-summary-total]', ctx.money(total));
  // free shipping progress
  const prog = $('[data-shipping-progress]', root);
  if (prog) {
    const pct = t.freeShipping ? Math.min(100, Math.round((t.afterCoupon / t.freeShipping) * 100)) : 100;
    $('[data-shipping-progress-fill]', prog).style.inlineSize = `${pct}%`;
    $('[data-shipping-progress-text]', prog).textContent = t.freeShippingLeft > 0 ? ctx.t('cart.freeShippingLeft', { n: ctx.money(t.freeShippingLeft) }) : ctx.t('cart.freeShippingUnlocked');
  }
  return { ...t, shipping, tax, total };
}

/* ---------------- Cart page ---------------- */
export function initCartPage(ctx) {
  const page = $('[data-cart-page]');
  if (!page) return;
  const itemsEl = $('[data-cart-items]', page);
  const filled = $('[data-cart-filled]', page); const empty = $('[data-cart-empty]', page);
  const clearBtn = $('[data-cart-clear]', page);

  function render() {
    const t = renderSummary(ctx, page);
    filled.hidden = t.count === 0; empty.hidden = t.count > 0; clearBtn.hidden = t.count === 0;
    $('[data-cart-count-label]', page).textContent = t.count ? `(${ctx.t('cart.count', { n: ctx.digits(t.count) })})` : '';
    itemsEl.innerHTML = t.lines.map((l) => cartItemHTML(ctx, l)).join('');
    const sav = $('[data-cart-savings]', page);
    if (sav) { sav.hidden = t.discount + t.couponValue <= 0; $('[data-cart-savings-text]', sav).textContent = ctx.t('cart.savings', { n: ctx.money(t.discount + t.couponValue) }); }
    const applied = $('[data-coupon-applied]', page);
    if (applied) applied.innerHTML = t.coupon ? `<span class="coupon__applied"><span class="icon icon--xs linear-icon-checkmark-circle" aria-hidden="true"></span>${t.coupon}</span>` : '';
  }
  render();
  store.subscribe(() => render());

  clearBtn.addEventListener('click', () => { if (confirm(ctx.t('cart.clearConfirm'))) { store.cartClear(); toast(ctx.t('toast.cartCleared')); } });
  $('[data-coupon-form]', page)?.addEventListener('submit', (e) => {
    e.preventDefault();
    const input = $('input', e.target);
    const code = input.value.trim().toUpperCase();
    const coupons = ctx.config.coupons || {};
    if (!code) return;
    if (coupons[code]) { store.setCoupon(code); toast(ctx.t('toast.couponApplied'), { type: 'success' }); input.value = ''; }
    else { toast(ctx.t('toast.couponInvalid'), { type: 'error' }); input.setAttribute('aria-invalid', 'true'); input.focus(); }
  });
  on(page, 'click', '[data-coupon-remove]', () => { store.setCoupon(null); toast(ctx.t('toast.couponRemoved')); });
}

/* ---------------- Checkout page ---------------- */
export function initCheckoutPage(ctx) {
  const page = $('[data-checkout-page]');
  if (!page) return;
  const form = $('[data-checkout-form]', page);
  const filled = $('[data-checkout-filled]', page); const empty = $('[data-checkout-empty]', page); const success = $('[data-checkout-success]', page);
  const rates = ctx.config.shippingRates || {};
  const shippingKey = () => $('input[name="shipping"]:checked', form)?.value || 'post';

  // shipping prices
  Object.entries(rates).forEach(([k, v]) => { const el = $(`[data-ship-price="${k}"]`, page); if (el) el.textContent = ctx.money(v); });

  function render() {
    const t = renderSummary(ctx, page, { shippingKey: shippingKey(), shippingRates: rates });
    const isEmpty = t.count === 0 && success.hidden;
    filled.hidden = isEmpty; empty.hidden = !isEmpty;
    $('[data-checkout-items]', page).innerHTML = t.lines.map((l) => summaryItemHTML(ctx, l)).join('');
    if (t.afterCoupon >= t.freeShipping) $$('[data-ship-price]', page).forEach((el) => { if (el.dataset.shipPrice !== 'courier') { el.textContent = ctx.t('common.free'); el.classList.add('option-card__price--free'); } });
    return t;
  }
  render();
  store.subscribe(() => { if (success.hidden) render(); });

  // address mode
  const newAddr = $('[data-new-address]', form);
  $$('input[name="addressMode"]', form).forEach((r) => r.addEventListener('change', () => { newAddr.hidden = r.value !== 'new' || !r.checked; if (!newAddr.hidden) $('input', newAddr)?.focus(); }));
  $$('input[name="shipping"]', form).forEach((r) => r.addEventListener('change', render));
  liveValidation(form, ctx);

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    if (!validateForm(form, ctx)) return;
    const btn = $('[data-place-order]', form);
    btn.classList.add('is-loading'); btn.disabled = true;
    const label = btn.textContent;
    setTimeout(() => {
      const code = `NX-${Date.now().toString().slice(-6)}`;
      $('[data-order-code]', page).textContent = code;
      success.hidden = false; filled.hidden = true;
      $$('.steps__item', page).forEach((s) => { s.classList.add('is-done'); s.classList.remove('is-active'); });
      $$('.steps__line', page).forEach((s) => s.classList.add('is-done'));
      store.cartClear();
      window.scrollTo({ top: 0, behavior: 'smooth' });
      btn.classList.remove('is-loading'); btn.disabled = false; btn.textContent = label;
    }, 1400);
  });
}
