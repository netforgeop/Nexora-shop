/**
 * Pure HTML string renderers (no DOM access) shared by the static build and
 * the browser runtime, so a product card looks identical whether it was
 * rendered at build time or injected by JavaScript.
 *
 * `ctx` shape: { lang, dir, root, t(key, params), currency, price(base) }
 */
import { esc } from './i18n.js';
import { formatPrice, formatNumber, toLocaleDigits } from './format.js';

export function createCtx({ lang, dir, root, t, currency }) {
  return {
    lang,
    dir,
    root,
    t,
    currency,
    price: (base, opts) => formatPrice(base, lang, currency, opts),
    num: (n) => formatNumber(n, lang),
    digits: (s) => toLocaleDigits(s, lang),
    img: (p) => `${root}assets/img/${p}`,
    url: (p) => `${root}${p}`,
    productUrl: (p) => `${root}product/${p.slug}.html`,
    categoryUrl: (slug) => `${root}shop.html?cat=${encodeURIComponent(slug)}`,
    brandUrl: (slug) => `${root}shop.html?brand=${encodeURIComponent(slug)}`,
  };
}

export const icon = (name, size = '', extra = '') =>
  `<span class="icon${size ? ` icon--${size}` : ''} linear-icon-${name}${extra ? ` ${extra}` : ''}" aria-hidden="true"></span>`;

export const svgIcon = (id, cls = '') =>
  `<svg class="svg-icon${cls ? ` ${cls}` : ''}" aria-hidden="true" focusable="false"><use href="#${id}"></use></svg>`;

/* ---------- Rating (SVG stars, fractional fill) ---------- */
const STAR = `<svg class="rating__star" aria-hidden="true" focusable="false"><use href="#i-star"></use></svg>`;
export function ratingHTML(ctx, rating, count, { size = '', showValue = true, showCount = true } = {}) {
  const value = Math.max(0, Math.min(5, Number(rating) || 0));
  const pct = Math.round((value / 5) * 1000) / 10;
  const label = ctx.t('product.rating', { rating: ctx.digits(value.toFixed(1)) });
  const stars = STAR.repeat(5);
  return `<div class="rating${size ? ` rating--${size}` : ''}" role="img" aria-label="${esc(label)}${count != null ? ` – ${esc(ctx.t('common.reviews', { n: ctx.num(count) }))}` : ''}">
    <span class="rating__stars"><span class="rating__row rating__row--empty">${stars}</span><span class="rating__row rating__row--fill" style="inline-size:${pct}%">${stars}</span></span>
    ${showValue ? `<span class="rating__value" aria-hidden="true">${ctx.digits(value.toFixed(1))}</span>` : ''}
    ${showCount && count != null ? `<span class="rating__count" aria-hidden="true">(${ctx.num(count)})</span>` : ''}
  </div>`;
}
export const starIcon = () => STAR;

/* ---------- Price ---------- */
export function priceHTML(ctx, product, { size = '', showDiscount = false } = {}) {
  const cur = ctx.currency;
  const symbol = `<span class="price__currency">${esc(cur.symbol)}</span>`;
  const amount = ctx.price(product.price, { symbol: false });
  const current = cur.position === 'before' ? `${symbol}${amount}` : `${amount} ${symbol}`;
  return `<div class="price${size ? ` price--${size}` : ''}">
    <span class="price__current">${current}</span>
    ${product.oldPrice ? `<s class="price__old"><span class="visually-hidden">${esc(ctx.t('common.price'))} </span>${ctx.price(product.oldPrice, { symbol: false })}</s>` : ''}
    ${showDiscount && product.discount ? `<span class="price__discount">${ctx.digits(product.discount)}%</span>` : ''}
  </div>`;
}

/* ---------- Badges ---------- */
export function badgesHTML(ctx, product, { max = 2 } = {}) {
  const out = [];
  if (product.discount) out.push(`<span class="badge badge--discount">${ctx.digits(product.discount)}% ${esc(ctx.t('common.off'))}</span>`);
  if (!product.inStock) out.push(`<span class="badge badge--out">${esc(ctx.t('common.outOfStock'))}</span>`);
  else if (product.flags?.new) out.push(`<span class="badge badge--new">${esc(ctx.t('common.new'))}</span>`);
  else if (product.flags?.bestseller) out.push(`<span class="badge badge--hot">${esc(ctx.t('common.bestseller'))}</span>`);
  return out.slice(0, max).join('');
}

/* ---------- Product card ---------- */
export function productCardHTML(ctx, p, opts = {}) {
  const { view = 'grid', lazy = true, flash = false, priority = false } = opts;
  const t = ctx.t;
  const url = ctx.productUrl(p);
  const swatches = p.colors.length
    ? `<div class="product-card__variants" aria-label="${esc(t('common.color'))}">${p.colors.slice(0, 4).map((c) => `<span class="product-card__swatch" style="background:${esc(c.hex)}" title="${esc(c.name)}"></span>`).join('')}${p.colors.length > 4 ? `<span class="product-card__swatch product-card__swatch--more">+${ctx.digits(p.colors.length - 4)}</span>` : ''}</div>`
    : '';
  const soldPct = flash ? Math.min(96, Math.round((p.sold / (p.sold + p.stock * 4)) * 100)) : 0;
  const loading = priority ? 'fetchpriority="high"' : (lazy ? 'loading="lazy" decoding="async"' : '');
  return `<article class="product-card${view === 'list' ? ' product-card--list' : ''}${p.inStock ? '' : ' is-out-of-stock'}" data-product-card data-id="${p.id}" data-slug="${esc(p.slug)}">
  <div class="product-card__media">
    <img class="product-card__img product-card__img--main" src="${ctx.img(p.image)}" width="640" height="640" alt="${esc(p.name)}" ${loading}>
    <img class="product-card__img product-card__img--hover" src="${ctx.img(p.imageHover)}" width="640" height="640" alt="" aria-hidden="true" loading="lazy" decoding="async">
    <div class="product-card__badges">${badgesHTML(ctx, p)}</div>
    <div class="product-card__actions">
      <button type="button" class="product-card__action" data-action="wishlist" data-id="${p.id}" aria-label="${esc(t('common.addToWishlist'))}" aria-pressed="false" title="${esc(t('common.addToWishlist'))}">${icon('heart')}</button>
      <button type="button" class="product-card__action" data-action="quick-view" data-id="${p.id}" aria-label="${esc(t('common.quickView'))}" title="${esc(t('common.quickView'))}">${icon('eye')}</button>
      <button type="button" class="product-card__action" data-action="compare" data-id="${p.id}" aria-label="${esc(t('common.addToCompare'))}" aria-pressed="false" title="${esc(t('common.addToCompare'))}">${icon('compare')}</button>
    </div>
    ${p.inStock ? '' : `<span class="product-card__out">${esc(t('common.outOfStock'))}</span>`}
  </div>
  <div class="product-card__body">
    <div class="product-card__category"><a href="${ctx.categoryUrl(p.category)}">${esc(p.categoryName)}</a></div>
    <h3 class="product-card__title"><a href="${url}">${esc(p.name)}</a></h3>
    ${ratingHTML(ctx, p.rating, p.reviewCount)}
    ${view === 'list' ? `<p class="product-card__desc">${esc(p.short)}</p>` : ''}
    ${flash ? `<div class="product-card__progress" role="progressbar" aria-valuenow="${soldPct}" aria-valuemin="0" aria-valuemax="100" aria-label="${esc(t('common.sold', { n: ctx.num(p.sold) }))}"><div class="product-card__progress-fill" style="inline-size:${soldPct}%"></div></div><div class="product-card__progress-text">${esc(t('common.sold', { n: ctx.num(p.sold) }))}</div>` : ''}
    <div class="product-card__footer">
      <div>${priceHTML(ctx, p)}${swatches}</div>
      <button type="button" class="product-card__add" data-action="add-to-cart" data-id="${p.id}" aria-label="${esc(t('common.addToCart'))}"${p.inStock ? '' : ' disabled'}>${icon('cart-add')}<span class="product-card__add-text">${esc(t('common.addToCart'))}</span></button>
    </div>
  </div>
</article>`;
}

export function productCardSkeletonHTML() {
  return `<div class="product-card product-card--skeleton" aria-hidden="true">
    <div class="product-card__media skeleton skeleton--media"></div>
    <div class="product-card__body"><div class="skeleton skeleton--text" style="inline-size:40%"></div><div class="skeleton skeleton--text"></div><div class="skeleton skeleton--text" style="inline-size:80%"></div><div class="skeleton skeleton--text" style="inline-size:50%"></div></div>
  </div>`;
}

/* ---------- Mini product (collections lists) ---------- */
export function productMiniHTML(ctx, p, rank) {
  return `<article class="product-mini${rank ? ' product-mini--ranked' : ''}">
    ${rank ? `<span class="product-mini__rank" aria-hidden="true">${ctx.digits(rank)}</span>` : ''}
    <a class="product-mini__media" href="${ctx.productUrl(p)}" tabindex="-1" aria-hidden="true"><img src="${ctx.img(p.image)}" width="84" height="84" alt="" loading="lazy" decoding="async"></a>
    <div>
      <h3 class="product-mini__title"><a href="${ctx.productUrl(p)}">${esc(p.name)}</a></h3>
      ${ratingHTML(ctx, p.rating, null, { showCount: false })}
      ${priceHTML(ctx, p, { size: 'sm' })}
    </div>
  </article>`;
}

/* ---------- Mini cart item ---------- */
export function miniCartItemHTML(ctx, item) {
  const p = item.product;
  return `<div class="mini-cart__item" data-key="${esc(item.key)}">
    <img src="${ctx.img(p.image)}" width="56" height="56" alt="" loading="lazy">
    <div>
      <a class="mini-cart__title" href="${ctx.productUrl(p)}">${esc(p.name)}</a>
      <div class="mini-cart__meta"><span class="num">${ctx.digits(item.qty)}</span> × ${ctx.price(p.price)}${item.color ? ` · ${esc(item.color)}` : ''}${item.size ? ` · ${esc(item.size)}` : ''}</div>
    </div>
    <button type="button" class="mini-cart__remove icon-btn icon-btn--ghost" data-action="cart-remove" data-key="${esc(item.key)}" aria-label="${esc(ctx.t('cart.remove'))}">${icon('cross', 'xs')}</button>
  </div>`;
}

/* ---------- Cart page row ---------- */
export function cartItemHTML(ctx, item) {
  const p = item.product;
  const t = ctx.t;
  const attrs = [];
  if (item.color) attrs.push(`<span><span class="product-card__swatch" style="background:${esc(item.colorHex || '#ccc')}"></span>${esc(item.color)}</span>`);
  if (item.size) attrs.push(`<span>${esc(t('common.size'))}: <span class="num">${esc(item.size)}</span></span>`);
  attrs.push(`<span>${esc(t('common.sku'))}: <span class="ltr">${esc(p.sku)}</span></span>`);
  return `<div class="cart-item" data-key="${esc(item.key)}">
    <a class="cart-item__media" href="${ctx.productUrl(p)}" tabindex="-1" aria-hidden="true"><img src="${ctx.img(p.image)}" width="110" height="110" alt="" loading="lazy"></a>
    <div class="cart-item__info">
      <h3 class="cart-item__title"><a href="${ctx.productUrl(p)}">${esc(p.name)}</a></h3>
      <div class="cart-item__attrs">${attrs.join('')}</div>
      <div class="cart-item__unit">${esc(t('cart.unitPrice'))}: ${ctx.price(p.price)}${p.oldPrice ? ` <s>${ctx.price(p.oldPrice)}</s>` : ''}</div>
      ${p.lowStock ? `<div class="cart-item__stock text-danger">${esc(t('common.lowStock', { n: ctx.digits(p.stock) }))}</div>` : ''}
      <div class="cart-item__actions">
        <button type="button" data-action="cart-to-wishlist" data-key="${esc(item.key)}" data-id="${p.id}">${icon('heart', 'xs')} ${esc(t('cart.moveToWishlist'))}</button>
      </div>
    </div>
    <div class="cart-item__mobile-row">
      <div class="cart-item__qty">${qtyHTML(ctx, item.qty, p.maxQty, { size: 'sm', key: item.key })}</div>
      <div class="cart-item__total"><div class="price"><span class="price__current">${ctx.price(p.price * item.qty)}</span></div></div>
    </div>
    <button type="button" class="cart-item__remove" data-action="cart-remove" data-key="${esc(item.key)}" aria-label="${esc(t('cart.remove'))}">${icon('trash2', 'sm')}</button>
  </div>`;
}

export function qtyHTML(ctx, value = 1, max = 5, { size = '', key = '', name = 'qty' } = {}) {
  const t = ctx.t;
  return `<div class="qty${size ? ` qty--${size}` : ''}" data-qty data-max="${max}"${key ? ` data-key="${esc(key)}"` : ''}>
    <button type="button" class="qty__btn" data-qty-dec aria-label="${esc(t('common.remove'))} 1"${value <= 1 ? ' disabled' : ''}>${icon('minus', 'xs')}</button>
    <input class="qty__input" type="number" name="${name}" inputmode="numeric" min="1" max="${max}" value="${value}" aria-label="${esc(t('common.qty'))}">
    <button type="button" class="qty__btn" data-qty-inc aria-label="${esc(t('common.addToCart'))} 1"${value >= max ? ' disabled' : ''}>${icon('plus', 'xs')}</button>
  </div>`;
}

/* ---------- Order summary item (checkout) ---------- */
export function summaryItemHTML(ctx, item) {
  const p = item.product;
  return `<div class="summary__item">
    <img src="${ctx.img(p.image)}" width="56" height="56" alt="" loading="lazy">
    <div><div class="summary__item-title">${esc(p.name)}</div><div class="summary__item-meta"><span class="num">${ctx.digits(item.qty)}</span> × ${ctx.price(p.price)}${item.color ? ` · ${esc(item.color)}` : ''}${item.size ? ` · ${esc(item.size)}` : ''}</div></div>
    <div class="num">${ctx.price(p.price * item.qty)}</div>
  </div>`;
}

/* ---------- Wishlist row ---------- */
export function wishItemHTML(ctx, p) {
  const t = ctx.t;
  return `<div class="wish-item" data-id="${p.id}">
    <a class="cart-item__media" href="${ctx.productUrl(p)}" tabindex="-1" aria-hidden="true"><img src="${ctx.img(p.image)}" width="100" height="100" alt="" loading="lazy"></a>
    <div class="cart-item__info">
      <div class="product-card__category">${esc(p.categoryName)}</div>
      <h3 class="cart-item__title"><a href="${ctx.productUrl(p)}">${esc(p.name)}</a></h3>
      ${ratingHTML(ctx, p.rating, p.reviewCount)}
    </div>
    <div>${priceHTML(ctx, p)}</div>
    <div>${p.inStock ? `<span class="status status--success">${esc(t('common.inStock'))}</span>` : `<span class="status status--danger">${esc(t('common.outOfStock'))}</span>`}</div>
    <div class="cluster">${p.inStock ? `<button type="button" class="btn btn--dark btn--sm" data-action="add-to-cart" data-id="${p.id}">${icon('cart-add', 'xs')} ${esc(t('common.addToCart'))}</button>` : `<button type="button" class="btn btn--outline btn--sm" data-action="notify" data-id="${p.id}">${icon('alarm', 'xs')} ${esc(t('wishlist.notify'))}</button>`}</div>
    <button type="button" class="cart-item__remove" data-action="wishlist" data-id="${p.id}" aria-label="${esc(t('common.removeFromWishlist'))}">${icon('trash2', 'sm')}</button>
  </div>`;
}

/* ---------- Search suggestion ---------- */
export function suggestItemHTML(ctx, p) {
  return `<a class="search__suggest-item" href="${ctx.productUrl(p)}" role="option">
    <img src="${ctx.img(p.image)}" width="44" height="44" alt="" loading="lazy">
    <span class="truncate">${esc(p.name)}</span>
    <span class="price price--sm"><span class="price__current">${ctx.price(p.price)}</span></span>
  </a>`;
}

/* ---------- Empty state ---------- */
export function emptyHTML(ctx, { iconName, title, text, cta, href }) {
  return `<div class="empty">
    <div class="empty__icon">${icon(iconName, 'xl')}</div>
    <h2 class="empty__title">${esc(title)}</h2>
    ${text ? `<p class="empty__text">${esc(text)}</p>` : ''}
    ${cta ? `<a class="btn btn--primary" href="${href}">${esc(cta)}</a>` : ''}
  </div>`;
}

/* ---------- Pagination ---------- */
export function paginationHTML(ctx, { page, pages, hrefFor }) {
  if (pages <= 1) return '';
  const t = ctx.t;
  const items = [];
  const link = (p, label, { current = false, disabled = false, aria } = {}) =>
    `<a class="pagination__link${disabled ? ' is-disabled' : ''}" href="${hrefFor(p)}" data-page="${p}"${current ? ' aria-current="page"' : ''}${disabled ? ' aria-disabled="true" tabindex="-1"' : ''} aria-label="${esc(aria || t('common.goToPage', { n: ctx.digits(p) }))}">${label}</a>`;
  items.push(link(Math.max(1, page - 1), icon('chevron-right', 'xs', 'icon--flip-ltr'), { disabled: page === 1, aria: t('common.prev') }));
  const range = new Set([1, pages, page, page - 1, page + 1]);
  let last = 0;
  for (let p = 1; p <= pages; p++) {
    if (!range.has(p)) continue;
    if (p - last > 1) items.push(`<span class="pagination__ellipsis" aria-hidden="true">…</span>`);
    items.push(link(p, ctx.digits(p), { current: p === page, aria: p === page ? t('common.currentPage', { n: ctx.digits(p) }) : undefined }));
    last = p;
  }
  items.push(link(Math.min(pages, page + 1), icon('chevron-left', 'xs', 'icon--flip-ltr'), { disabled: page === pages, aria: t('common.next') }));
  return `<nav class="pagination" aria-label="${esc(t('common.page'))}">${items.join('')}</nav>`;
}

/* ---------- Review ---------- */
export function reviewHTML(ctx, r) {
  const t = ctx.t;
  const list = (arr, cls, ic) => arr?.length ? `<ul class="review__pros review__pros--${cls}">${arr.map((x) => `<li>${icon(ic)} ${esc(x)}</li>`).join('')}</ul>` : '';
  return `<article class="review">
    <div>
      <div class="review__author">
        <span class="avatar avatar--sm" aria-hidden="true" style="display:grid;place-items:center;background:var(--color-primary-soft);color:var(--color-text-strong);font-weight:700">${esc(r.author.trim().charAt(0))}</span>
        <div><div class="review__name">${esc(r.author)}</div><div class="review__date">${esc(r.dateLabel)}</div></div>
      </div>
      ${r.verified ? `<div class="review-card__verified" style="margin-block-start:8px">${icon('checkmark-circle', 'xs')} ${esc(t('common.verified'))}</div>` : ''}
    </div>
    <div>
      ${ratingHTML(ctx, r.rating, null, { showCount: false, showValue: false })}
      <h3 class="review__title">${esc(r.title)}</h3>
      <p class="review__text">${esc(r.text)}</p>
      ${list(r.pros, 'plus', 'plus-circle')}${list(r.cons, 'minus', 'circle-minus')}
      <div class="review__helpful"><span>${esc(t('common.helpful'))}</span><button type="button" data-helpful="yes">${icon('thumbs-up', 'xs')} ${esc(t('product.helpfulYes', { n: ctx.digits(r.helpful?.[0] ?? 0) }))}</button><button type="button" data-helpful="no">${icon('thumbs-up', 'xs', 'u-flip-y')} ${esc(t('product.helpfulNo', { n: ctx.digits(r.helpful?.[1] ?? 0) }))}</button></div>
    </div>
  </article>`;
}

/* ---------- Toast ---------- */
export function toastHTML({ message, type = 'default', action, actionHref, closeLabel = 'close' }) {
  const icons = { success: 'checkmark-circle', error: 'warning', info: 'bubble-alert', default: 'alarm' };
  return `<div class="toast toast--${type}" role="status">
    <span class="toast__icon">${icon(icons[type] || icons.default, 'sm')}</span>
    <span class="toast__text">${esc(message)}</span>
    ${action ? `<a class="toast__action" href="${esc(actionHref || '#')}">${esc(action)}</a>` : ''}
    <button type="button" class="toast__close" data-toast-close aria-label="${esc(closeLabel)}">${icon('cross', 'xs')}</button>
  </div>`;
}
