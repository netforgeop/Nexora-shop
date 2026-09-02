/* Nexora runtime — built from src/js (do not edit directly, run `npm run build`) */
(() => {
  // src/js/core/i18n.js
  function createTranslator(dict) {
    function lookup(key) {
      return key.split(".").reduce((obj, part) => obj != null && obj[part] !== void 0 ? obj[part] : void 0, dict);
    }
    return function t(key, params) {
      const value = lookup(key);
      let str = value === void 0 ? key : String(value);
      if (params) {
        for (const [k, v] of Object.entries(params)) str = str.split(`{${k}}`).join(v == null ? "" : String(v));
      }
      return str;
    };
  }
  function esc(value) {
    return String(value == null ? "" : value).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#39;");
  }

  // src/js/core/format.js
  var PERSIAN_DIGITS = ["\u06F0", "\u06F1", "\u06F2", "\u06F3", "\u06F4", "\u06F5", "\u06F6", "\u06F7", "\u06F8", "\u06F9"];
  function toLocaleDigits(str, lang) {
    if (lang !== "fa") return String(str);
    return String(str).replace(/\d/g, (d) => PERSIAN_DIGITS[+d]);
  }
  function formatNumber(value, lang, options = {}) {
    const n = Number(value) || 0;
    const locale = lang === "fa" ? "fa-IR" : "en-US";
    const { decimals = 0 } = options;
    try {
      return new Intl.NumberFormat(locale, { maximumFractionDigits: decimals, minimumFractionDigits: 0, useGrouping: true }).format(n);
    } catch {
      return toLocaleDigits(n.toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ","), lang);
    }
  }
  function formatPrice(base, lang, currency, opts = {}) {
    var _a;
    const { symbol = true } = opts;
    const converted = (Number(base) || 0) * (currency.rate || 1);
    const decimals = (_a = currency.decimals) != null ? _a : 0;
    const rounded = decimals === 0 ? Math.round(converted) : Number(converted.toFixed(decimals));
    const num = formatNumber(rounded, lang, { decimals });
    if (!symbol) return num;
    return currency.position === "before" ? `${currency.symbol}${num}` : `${num} ${currency.symbol}`;
  }
  function pad2(n) {
    return String(n).padStart(2, "0");
  }

  // src/js/core/render.js
  function createCtx({ lang, dir, root, t, currency }) {
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
      brandUrl: (slug) => `${root}shop.html?brand=${encodeURIComponent(slug)}`
    };
  }
  var icon = (name, size = "", extra = "") => `<span class="icon${size ? ` icon--${size}` : ""} linear-icon-${name}${extra ? ` ${extra}` : ""}" aria-hidden="true"></span>`;
  var STAR = `<svg class="rating__star" aria-hidden="true" focusable="false"><use href="#i-star"></use></svg>`;
  function ratingHTML(ctx4, rating, count, { size = "", showValue = true, showCount = true } = {}) {
    const value = Math.max(0, Math.min(5, Number(rating) || 0));
    const pct = Math.round(value / 5 * 1e3) / 10;
    const label = ctx4.t("product.rating", { rating: ctx4.digits(value.toFixed(1)) });
    const stars = STAR.repeat(5);
    return `<div class="rating${size ? ` rating--${size}` : ""}" role="img" aria-label="${esc(label)}${count != null ? ` \u2013 ${esc(ctx4.t("common.reviews", { n: ctx4.num(count) }))}` : ""}">
    <span class="rating__stars"><span class="rating__row rating__row--empty">${stars}</span><span class="rating__row rating__row--fill" style="inline-size:${pct}%">${stars}</span></span>
    ${showValue ? `<span class="rating__value" aria-hidden="true">${ctx4.digits(value.toFixed(1))}</span>` : ""}
    ${showCount && count != null ? `<span class="rating__count" aria-hidden="true">(${ctx4.num(count)})</span>` : ""}
  </div>`;
  }
  var starIcon = () => STAR;
  function priceHTML(ctx4, product, { size = "", showDiscount = false } = {}) {
    const cur = ctx4.currency;
    const symbol = `<span class="price__currency">${esc(cur.symbol)}</span>`;
    const amount = ctx4.price(product.price, { symbol: false });
    const current = cur.position === "before" ? `${symbol}${amount}` : `${amount} ${symbol}`;
    return `<div class="price${size ? ` price--${size}` : ""}">
    <span class="price__current">${current}</span>
    ${product.oldPrice ? `<s class="price__old"><span class="visually-hidden">${esc(ctx4.t("common.price"))} </span>${ctx4.price(product.oldPrice, { symbol: false })}</s>` : ""}
    ${showDiscount && product.discount ? `<span class="price__discount">${ctx4.digits(product.discount)}%</span>` : ""}
  </div>`;
  }
  function badgesHTML(ctx4, product, { max = 2 } = {}) {
    var _a, _b;
    const out = [];
    if (product.discount) out.push(`<span class="badge badge--discount">${ctx4.digits(product.discount)}% ${esc(ctx4.t("common.off"))}</span>`);
    if (!product.inStock) out.push(`<span class="badge badge--out">${esc(ctx4.t("common.outOfStock"))}</span>`);
    else if ((_a = product.flags) == null ? void 0 : _a.new) out.push(`<span class="badge badge--new">${esc(ctx4.t("common.new"))}</span>`);
    else if ((_b = product.flags) == null ? void 0 : _b.bestseller) out.push(`<span class="badge badge--hot">${esc(ctx4.t("common.bestseller"))}</span>`);
    return out.slice(0, max).join("");
  }
  function productCardHTML(ctx4, p, opts = {}) {
    const { view = "grid", lazy = true, flash = false, priority = false } = opts;
    const t = ctx4.t;
    const url = ctx4.productUrl(p);
    const swatches = p.colors.length ? `<div class="product-card__variants" aria-label="${esc(t("common.color"))}">${p.colors.slice(0, 4).map((c) => `<span class="product-card__swatch" style="background:${esc(c.hex)}" title="${esc(c.name)}"></span>`).join("")}${p.colors.length > 4 ? `<span class="product-card__swatch product-card__swatch--more">+${ctx4.digits(p.colors.length - 4)}</span>` : ""}</div>` : "";
    const soldPct = flash ? Math.min(96, Math.round(p.sold / (p.sold + p.stock * 4) * 100)) : 0;
    const loading = priority ? 'fetchpriority="high"' : lazy ? 'loading="lazy" decoding="async"' : "";
    return `<article class="product-card${view === "list" ? " product-card--list" : ""}${p.inStock ? "" : " is-out-of-stock"}" data-product-card data-id="${p.id}" data-slug="${esc(p.slug)}">
  <div class="product-card__media">
    <img class="product-card__img product-card__img--main" src="${ctx4.img(p.image)}" width="640" height="640" alt="${esc(p.name)}" ${loading}>
    <img class="product-card__img product-card__img--hover" src="${ctx4.img(p.imageHover)}" width="640" height="640" alt="" aria-hidden="true" loading="lazy" decoding="async">
    <div class="product-card__badges">${badgesHTML(ctx4, p)}</div>
    <div class="product-card__actions">
      <button type="button" class="product-card__action" data-action="wishlist" data-id="${p.id}" aria-label="${esc(t("common.addToWishlist"))}" aria-pressed="false" title="${esc(t("common.addToWishlist"))}">${icon("heart")}</button>
      <button type="button" class="product-card__action" data-action="quick-view" data-id="${p.id}" aria-label="${esc(t("common.quickView"))}" title="${esc(t("common.quickView"))}">${icon("eye")}</button>
      <button type="button" class="product-card__action" data-action="compare" data-id="${p.id}" aria-label="${esc(t("common.addToCompare"))}" aria-pressed="false" title="${esc(t("common.addToCompare"))}">${icon("compare")}</button>
    </div>
    ${p.inStock ? "" : `<span class="product-card__out">${esc(t("common.outOfStock"))}</span>`}
  </div>
  <div class="product-card__body">
    <div class="product-card__category"><a href="${ctx4.categoryUrl(p.category)}">${esc(p.categoryName)}</a></div>
    <h3 class="product-card__title"><a href="${url}">${esc(p.name)}</a></h3>
    ${ratingHTML(ctx4, p.rating, p.reviewCount)}
    ${view === "list" ? `<p class="product-card__desc">${esc(p.short)}</p>` : ""}
    ${flash ? `<div class="product-card__progress" role="progressbar" aria-valuenow="${soldPct}" aria-valuemin="0" aria-valuemax="100" aria-label="${esc(t("common.sold", { n: ctx4.num(p.sold) }))}"><div class="product-card__progress-fill" style="inline-size:${soldPct}%"></div></div><div class="product-card__progress-text">${esc(t("common.sold", { n: ctx4.num(p.sold) }))}</div>` : ""}
    <div class="product-card__footer">
      <div>${priceHTML(ctx4, p)}${swatches}</div>
      <button type="button" class="product-card__add" data-action="add-to-cart" data-id="${p.id}" aria-label="${esc(t("common.addToCart"))}"${p.inStock ? "" : " disabled"}>${icon("cart-add")}<span class="product-card__add-text">${esc(t("common.addToCart"))}</span></button>
    </div>
  </div>
</article>`;
  }
  function miniCartItemHTML(ctx4, item) {
    const p = item.product;
    return `<div class="mini-cart__item" data-key="${esc(item.key)}">
    <img src="${ctx4.img(p.image)}" width="56" height="56" alt="" loading="lazy">
    <div>
      <a class="mini-cart__title" href="${ctx4.productUrl(p)}">${esc(p.name)}</a>
      <div class="mini-cart__meta"><span class="num">${ctx4.digits(item.qty)}</span> \xD7 ${ctx4.price(p.price)}${item.color ? ` \xB7 ${esc(item.color)}` : ""}${item.size ? ` \xB7 ${esc(item.size)}` : ""}</div>
    </div>
    <button type="button" class="mini-cart__remove icon-btn icon-btn--ghost" data-action="cart-remove" data-key="${esc(item.key)}" aria-label="${esc(ctx4.t("cart.remove"))}">${icon("cross", "xs")}</button>
  </div>`;
  }
  function cartItemHTML(ctx4, item) {
    const p = item.product;
    const t = ctx4.t;
    const attrs = [];
    if (item.color) attrs.push(`<span><span class="product-card__swatch" style="background:${esc(item.colorHex || "#ccc")}"></span>${esc(item.color)}</span>`);
    if (item.size) attrs.push(`<span>${esc(t("common.size"))}: <span class="num">${esc(item.size)}</span></span>`);
    attrs.push(`<span>${esc(t("common.sku"))}: <span class="ltr">${esc(p.sku)}</span></span>`);
    return `<div class="cart-item" data-key="${esc(item.key)}">
    <a class="cart-item__media" href="${ctx4.productUrl(p)}" tabindex="-1" aria-hidden="true"><img src="${ctx4.img(p.image)}" width="110" height="110" alt="" loading="lazy"></a>
    <div class="cart-item__info">
      <h3 class="cart-item__title"><a href="${ctx4.productUrl(p)}">${esc(p.name)}</a></h3>
      <div class="cart-item__attrs">${attrs.join("")}</div>
      <div class="cart-item__unit">${esc(t("cart.unitPrice"))}: ${ctx4.price(p.price)}${p.oldPrice ? ` <s>${ctx4.price(p.oldPrice)}</s>` : ""}</div>
      ${p.lowStock ? `<div class="cart-item__stock text-danger">${esc(t("common.lowStock", { n: ctx4.digits(p.stock) }))}</div>` : ""}
      <div class="cart-item__actions">
        <button type="button" data-action="cart-to-wishlist" data-key="${esc(item.key)}" data-id="${p.id}">${icon("heart", "xs")} ${esc(t("cart.moveToWishlist"))}</button>
      </div>
    </div>
    <div class="cart-item__mobile-row">
      <div class="cart-item__qty">${qtyHTML(ctx4, item.qty, p.maxQty, { size: "sm", key: item.key })}</div>
      <div class="cart-item__total"><div class="price"><span class="price__current">${ctx4.price(p.price * item.qty)}</span></div></div>
    </div>
    <button type="button" class="cart-item__remove" data-action="cart-remove" data-key="${esc(item.key)}" aria-label="${esc(t("cart.remove"))}">${icon("trash2", "sm")}</button>
  </div>`;
  }
  function qtyHTML(ctx4, value = 1, max = 5, { size = "", key = "", name = "qty" } = {}) {
    const t = ctx4.t;
    return `<div class="qty${size ? ` qty--${size}` : ""}" data-qty data-max="${max}"${key ? ` data-key="${esc(key)}"` : ""}>
    <button type="button" class="qty__btn" data-qty-dec aria-label="${esc(t("common.remove"))} 1"${value <= 1 ? " disabled" : ""}>${icon("minus", "xs")}</button>
    <input class="qty__input" type="number" name="${name}" inputmode="numeric" min="1" max="${max}" value="${value}" aria-label="${esc(t("common.qty"))}">
    <button type="button" class="qty__btn" data-qty-inc aria-label="${esc(t("common.addToCart"))} 1"${value >= max ? " disabled" : ""}>${icon("plus", "xs")}</button>
  </div>`;
  }
  function summaryItemHTML(ctx4, item) {
    const p = item.product;
    return `<div class="summary__item">
    <img src="${ctx4.img(p.image)}" width="56" height="56" alt="" loading="lazy">
    <div><div class="summary__item-title">${esc(p.name)}</div><div class="summary__item-meta"><span class="num">${ctx4.digits(item.qty)}</span> \xD7 ${ctx4.price(p.price)}${item.color ? ` \xB7 ${esc(item.color)}` : ""}${item.size ? ` \xB7 ${esc(item.size)}` : ""}</div></div>
    <div class="num">${ctx4.price(p.price * item.qty)}</div>
  </div>`;
  }
  function wishItemHTML(ctx4, p) {
    const t = ctx4.t;
    return `<div class="wish-item" data-id="${p.id}">
    <a class="cart-item__media" href="${ctx4.productUrl(p)}" tabindex="-1" aria-hidden="true"><img src="${ctx4.img(p.image)}" width="100" height="100" alt="" loading="lazy"></a>
    <div class="cart-item__info">
      <div class="product-card__category">${esc(p.categoryName)}</div>
      <h3 class="cart-item__title"><a href="${ctx4.productUrl(p)}">${esc(p.name)}</a></h3>
      ${ratingHTML(ctx4, p.rating, p.reviewCount)}
    </div>
    <div>${priceHTML(ctx4, p)}</div>
    <div>${p.inStock ? `<span class="status status--success">${esc(t("common.inStock"))}</span>` : `<span class="status status--danger">${esc(t("common.outOfStock"))}</span>`}</div>
    <div class="cluster">${p.inStock ? `<button type="button" class="btn btn--dark btn--sm" data-action="add-to-cart" data-id="${p.id}">${icon("cart-add", "xs")} ${esc(t("common.addToCart"))}</button>` : `<button type="button" class="btn btn--outline btn--sm" data-action="notify" data-id="${p.id}">${icon("alarm", "xs")} ${esc(t("wishlist.notify"))}</button>`}</div>
    <button type="button" class="cart-item__remove" data-action="wishlist" data-id="${p.id}" aria-label="${esc(t("common.removeFromWishlist"))}">${icon("trash2", "sm")}</button>
  </div>`;
  }
  function suggestItemHTML(ctx4, p) {
    return `<a class="search__suggest-item" href="${ctx4.productUrl(p)}" role="option">
    <img src="${ctx4.img(p.image)}" width="44" height="44" alt="" loading="lazy">
    <span class="truncate">${esc(p.name)}</span>
    <span class="price price--sm"><span class="price__current">${ctx4.price(p.price)}</span></span>
  </a>`;
  }
  function paginationHTML(ctx4, { page, pages, hrefFor }) {
    if (pages <= 1) return "";
    const t = ctx4.t;
    const items = [];
    const link = (p, label, { current = false, disabled = false, aria } = {}) => `<a class="pagination__link${disabled ? " is-disabled" : ""}" href="${hrefFor(p)}" data-page="${p}"${current ? ' aria-current="page"' : ""}${disabled ? ' aria-disabled="true" tabindex="-1"' : ""} aria-label="${esc(aria || t("common.goToPage", { n: ctx4.digits(p) }))}">${label}</a>`;
    items.push(link(Math.max(1, page - 1), icon("chevron-right", "xs", "icon--flip-ltr"), { disabled: page === 1, aria: t("common.prev") }));
    const range = /* @__PURE__ */ new Set([1, pages, page, page - 1, page + 1]);
    let last = 0;
    for (let p = 1; p <= pages; p++) {
      if (!range.has(p)) continue;
      if (p - last > 1) items.push(`<span class="pagination__ellipsis" aria-hidden="true">\u2026</span>`);
      items.push(link(p, ctx4.digits(p), { current: p === page, aria: p === page ? t("common.currentPage", { n: ctx4.digits(p) }) : void 0 }));
      last = p;
    }
    items.push(link(Math.min(pages, page + 1), icon("chevron-left", "xs", "icon--flip-ltr"), { disabled: page === pages, aria: t("common.next") }));
    return `<nav class="pagination" aria-label="${esc(t("common.page"))}">${items.join("")}</nav>`;
  }
  function reviewHTML(ctx4, r) {
    var _a, _b, _c, _d;
    const t = ctx4.t;
    const list = (arr, cls, ic) => (arr == null ? void 0 : arr.length) ? `<ul class="review__pros review__pros--${cls}">${arr.map((x) => `<li>${icon(ic)} ${esc(x)}</li>`).join("")}</ul>` : "";
    return `<article class="review">
    <div>
      <div class="review__author">
        <span class="avatar avatar--sm" aria-hidden="true" style="display:grid;place-items:center;background:var(--color-primary-soft);color:var(--color-text-strong);font-weight:700">${esc(r.author.trim().charAt(0))}</span>
        <div><div class="review__name">${esc(r.author)}</div><div class="review__date">${esc(r.dateLabel)}</div></div>
      </div>
      ${r.verified ? `<div class="review-card__verified" style="margin-block-start:8px">${icon("checkmark-circle", "xs")} ${esc(t("common.verified"))}</div>` : ""}
    </div>
    <div>
      ${ratingHTML(ctx4, r.rating, null, { showCount: false, showValue: false })}
      <h3 class="review__title">${esc(r.title)}</h3>
      <p class="review__text">${esc(r.text)}</p>
      ${list(r.pros, "plus", "plus-circle")}${list(r.cons, "minus", "circle-minus")}
      <div class="review__helpful"><span>${esc(t("common.helpful"))}</span><button type="button" data-helpful="yes">${icon("thumbs-up", "xs")} ${esc(t("product.helpfulYes", { n: ctx4.digits((_b = (_a = r.helpful) == null ? void 0 : _a[0]) != null ? _b : 0) }))}</button><button type="button" data-helpful="no">${icon("thumbs-up", "xs", "u-flip-y")} ${esc(t("product.helpfulNo", { n: ctx4.digits((_d = (_c = r.helpful) == null ? void 0 : _c[1]) != null ? _d : 0) }))}</button></div>
    </div>
  </article>`;
  }
  function toastHTML({ message, type = "default", action, actionHref, closeLabel = "close" }) {
    const icons = { success: "checkmark-circle", error: "warning", info: "bubble-alert", default: "alarm" };
    return `<div class="toast toast--${type}" role="status">
    <span class="toast__icon">${icon(icons[type] || icons.default, "sm")}</span>
    <span class="toast__text">${esc(message)}</span>
    ${action ? `<a class="toast__action" href="${esc(actionHref || "#")}">${esc(action)}</a>` : ""}
    <button type="button" class="toast__close" data-toast-close aria-label="${esc(closeLabel)}">${icon("cross", "xs")}</button>
  </div>`;
  }

  // src/js/core/app.js
  var ctx = null;
  var catalog = null;
  function boot() {
    const html = document.documentElement;
    const lang = html.lang || "fa";
    const dir = html.dir || (lang === "fa" ? "rtl" : "ltr");
    const root = html.dataset.root || "./";
    const L = window.NEXORA_I18N || {};
    catalog = window.NEXORA_CATALOG || { products: [], categories: [], brands: [], currency: { symbol: "", rate: 1, position: "after", decimals: 0 }, config: {} };
    const t = createTranslator(L);
    ctx = createCtx({ lang, dir, root, t, currency: catalog.currency });
    ctx.L = L;
    ctx.config = catalog.config || {};
    ctx.catalog = catalog;
    ctx.byId = new Map(catalog.products.map((p) => [p.id, p]));
    ctx.bySlug = new Map(catalog.products.map((p) => [p.slug, p]));
    ctx.product = (id) => ctx.byId.get(Number(id));
    ctx.money = (base) => formatPrice(base, lang, catalog.currency);
    ctx.page = document.body.dataset.page || "";
    return ctx;
  }

  // src/js/core/dom.js
  var $ = (sel, root = document) => root.querySelector(sel);
  var $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
  function on(root, event, selector, handler, options) {
    root.addEventListener(event, (e) => {
      const target = e.target.closest(selector);
      if (target && root.contains(target)) handler(e, target);
    }, options);
  }
  function el(tag, attrs = {}, children = []) {
    const node = document.createElement(tag);
    for (const [k, v] of Object.entries(attrs)) {
      if (k === "class") node.className = v;
      else if (k === "text") node.textContent = v;
      else if (k === "html") node.innerHTML = v;
      else if (k.startsWith("on")) node.addEventListener(k.slice(2), v);
      else if (v !== false && v != null) node.setAttribute(k, v === true ? "" : v);
    }
    for (const c of [].concat(children)) if (c != null) node.append(c);
    return node;
  }
  function debounce(fn, ms = 200) {
    let t;
    return (...args) => {
      clearTimeout(t);
      t = setTimeout(() => fn(...args), ms);
    };
  }
  function throttle(fn, ms = 100) {
    let last = 0;
    let timer;
    return (...args) => {
      const now = Date.now();
      const remaining = ms - (now - last);
      if (remaining <= 0) {
        last = now;
        fn(...args);
      } else {
        clearTimeout(timer);
        timer = setTimeout(() => {
          last = Date.now();
          fn(...args);
        }, remaining);
      }
    };
  }
  var prefersReducedMotion = () => window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var mq = (q) => window.matchMedia(q);
  function trapFocus(container) {
    const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
    const handler = (e) => {
      if (e.key !== "Tab") return;
      const nodes = $$(FOCUSABLE, container).filter((n) => n.offsetParent !== null || n === document.activeElement);
      if (!nodes.length) return;
      const first = nodes[0];
      const last = nodes[nodes.length - 1];
      if (e.shiftKey && document.activeElement === first) {
        e.preventDefault();
        last.focus();
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();
        first.focus();
      }
    };
    container.addEventListener("keydown", handler);
    return () => container.removeEventListener("keydown", handler);
  }
  function lockScroll(lock) {
    document.body.classList.toggle("is-locked", lock);
  }
  function announce(text) {
    let live = document.getElementById("nx-live");
    if (!live) {
      live = el("div", { id: "nx-live", class: "visually-hidden", "aria-live": "polite", "aria-atomic": "true" });
      document.body.append(live);
    }
    live.textContent = "";
    requestAnimationFrame(() => {
      live.textContent = text;
    });
  }
  function scrollToEl(node, offset = 90) {
    const top = node.getBoundingClientRect().top + window.scrollY - offset;
    window.scrollTo({ top, behavior: prefersReducedMotion() ? "auto" : "smooth" });
  }

  // src/js/components/toast.js
  var region;
  var ctx2;
  function initToasts(appCtx) {
    ctx2 = appCtx;
    region = document.getElementById("toast-region");
    if (!region) {
      region = el("div", { id: "toast-region", class: "toast-region", "aria-live": "polite" });
      document.body.append(region);
    }
    region.addEventListener("click", (e) => {
      const close2 = e.target.closest("[data-toast-close]");
      if (close2) dismiss(close2.closest(".toast"));
      const undo = e.target.closest("[data-toast-undo]");
      if (undo) {
        undo.dispatchEvent(new CustomEvent("toast:undo", { bubbles: true }));
        dismiss(undo.closest(".toast"));
      }
    });
  }
  function toast(message, { type = "default", action, actionHref, onUndo, duration = 4e3 } = {}) {
    if (!region) return null;
    const wrap = document.createElement("div");
    wrap.innerHTML = toastHTML({ message, type, action, actionHref, closeLabel: (ctx2 == null ? void 0 : ctx2.t("common.close")) || "Close" });
    const node = wrap.firstElementChild;
    if (onUndo) {
      const btn = el("button", { type: "button", class: "toast__action", "data-toast-undo": "", text: (ctx2 == null ? void 0 : ctx2.t("toast.undo")) || "Undo" });
      node.insertBefore(btn, node.querySelector(".toast__close"));
      node.addEventListener("toast:undo", onUndo, { once: true });
    }
    region.append(node);
    const live = () => Array.from(region.children).filter((n) => !n.classList.contains("is-leaving"));
    while (live().length > 4) dismiss(live()[0]);
    const timer = setTimeout(() => dismiss(node), duration);
    node.addEventListener("mouseenter", () => clearTimeout(timer), { once: true });
    return node;
  }
  function dismiss(node) {
    if (!node || node.classList.contains("is-leaving")) return;
    node.classList.add("is-leaving");
    setTimeout(() => node.remove(), 260);
  }

  // src/js/components/header.js
  function initHeader(ctx4) {
    var _a;
    const header = $("[data-header]");
    if (!header) return;
    const ann = $("[data-announcement]");
    if (ann) {
      try {
        if (localStorage.getItem("nx:announcement") === "dismissed") ann.classList.add("is-dismissed");
      } catch {
      }
      (_a = $("[data-announcement-close]", ann)) == null ? void 0 : _a.addEventListener("click", () => {
        ann.classList.add("is-dismissed");
        try {
          localStorage.setItem("nx:announcement", "dismissed");
        } catch {
        }
      });
    }
    if (!header.hasAttribute("data-no-sticky")) {
      const main = $(".header-main", header);
      let stuck = false;
      let lastY = window.scrollY;
      const threshold = () => main ? main.offsetTop + main.offsetHeight + 40 : 200;
      const update = () => {
        const y = window.scrollY;
        const shouldStick = y > threshold();
        if (shouldStick !== stuck) {
          stuck = shouldStick;
          if (stuck) header.style.setProperty("--stuck-offset", `${main.offsetHeight}px`);
          header.classList.toggle("is-stuck", stuck);
          if (!stuck) header.classList.remove("is-hidden");
        }
        if (stuck && mq("(max-width: 991.98px)").matches) {
          header.classList.toggle("is-hidden", y > lastY && y - lastY > 4);
        }
        lastY = y;
      };
      window.addEventListener("scroll", throttle(update, 80), { passive: true });
      update();
    }
    const catMenu = $("[data-cat-menu]");
    if (catMenu) {
      const trigger = $("[data-cat-menu-trigger]", catMenu);
      const pinned = catMenu.classList.contains("is-pinned");
      const setOpen = (open2) => {
        catMenu.classList.toggle("is-open", open2);
        trigger.setAttribute("aria-expanded", String(open2));
      };
      trigger.addEventListener("click", () => setOpen(!catMenu.classList.contains("is-open")));
      if (!pinned) {
        let hoverTimer;
        catMenu.addEventListener("mouseenter", () => {
          if (mq("(hover: hover)").matches) {
            clearTimeout(hoverTimer);
            setOpen(true);
          }
        });
        catMenu.addEventListener("mouseleave", () => {
          if (mq("(hover: hover)").matches) hoverTimer = setTimeout(() => setOpen(false), 180);
        });
      }
      document.addEventListener("click", (e) => {
        if (!catMenu.contains(e.target) && !pinned) setOpen(false);
      });
      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && catMenu.classList.contains("is-open") && !pinned) {
          setOpen(false);
          trigger.focus();
        }
      });
      catMenu.addEventListener("keydown", (e) => {
        if (!["ArrowDown", "ArrowUp"].includes(e.key)) return;
        const links = $$(".cat-menu__link", catMenu);
        const idx = links.indexOf(document.activeElement);
        if (idx === -1) return;
        e.preventDefault();
        links[(idx + (e.key === "ArrowDown" ? 1 : -1) + links.length) % links.length].focus();
      });
      if (pinned) {
        const unpin = () => {
          if (window.scrollY > 120) {
            catMenu.classList.remove("is-pinned");
            setOpen(false);
            window.removeEventListener("scroll", unpin);
          }
        };
        window.addEventListener("scroll", unpin, { passive: true });
      }
    }
    $$("[data-nav-dropdown]").forEach((item) => {
      const link = $(".nav__link", item);
      const setOpen = (open2) => {
        item.classList.toggle("is-open", open2);
        link.setAttribute("aria-expanded", String(open2));
      };
      link.addEventListener("click", (e) => {
        if (!mq("(hover: hover)").matches && !item.classList.contains("is-open")) {
          e.preventDefault();
          setOpen(true);
        }
      });
      link.addEventListener("keydown", (e) => {
        var _a2;
        if (e.key === "ArrowDown" || e.key === " ") {
          e.preventDefault();
          setOpen(true);
          (_a2 = $(".dropdown__link", item)) == null ? void 0 : _a2.focus();
        }
      });
      item.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
          setOpen(false);
          link.focus();
        }
      });
      item.addEventListener("focusout", (e) => {
        if (!item.contains(e.relatedTarget)) setOpen(false);
      });
      document.addEventListener("click", (e) => {
        if (!item.contains(e.target)) setOpen(false);
      });
    });
    const wrap = $("[data-mini-cart-wrap]");
    if (wrap) {
      const toggle = $("[data-mini-cart-toggle]", wrap);
      toggle.addEventListener("keydown", (e) => {
        var _a2;
        if (e.key === "ArrowDown") {
          e.preventDefault();
          wrap.classList.add("is-open");
          toggle.setAttribute("aria-expanded", "true");
          (_a2 = $("a, button", $("[data-mini-cart]", wrap))) == null ? void 0 : _a2.focus();
        }
      });
      wrap.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
          wrap.classList.remove("is-open");
          toggle.setAttribute("aria-expanded", "false");
          toggle.focus();
        }
      });
      wrap.addEventListener("focusout", (e) => {
        if (!wrap.contains(e.relatedTarget)) {
          wrap.classList.remove("is-open");
          toggle.setAttribute("aria-expanded", "false");
        }
      });
    }
    $$("[data-lang-switch]").forEach((a) => {
      a.addEventListener("click", () => {
        const url = new URL(a.href, location.href);
        url.search = location.search;
        url.hash = location.hash;
        a.href = url.toString();
        try {
          localStorage.setItem("nx:lang", a.getAttribute("hreflang"));
        } catch {
        }
      });
    });
    const btt = document.getElementById("back-to-top");
    if (btt) {
      window.addEventListener("scroll", throttle(() => btt.classList.toggle("is-visible", window.scrollY > 600), 120), { passive: true });
      btt.addEventListener("click", () => window.scrollTo({ top: 0, behavior: "smooth" }));
    }
    const prog = document.getElementById("page-progress");
    if (prog) {
      prog.style.inlineSize = "70%";
      window.addEventListener("load", () => {
        prog.classList.add("is-done");
        setTimeout(() => prog.remove(), 600);
      }, { once: true });
      setTimeout(() => {
        prog.classList.add("is-done");
      }, 2500);
    }
  }

  // src/js/components/drawer.js
  var overlay;
  var openDrawer = null;
  var releaseTrap = null;
  var lastFocus = null;
  function initDrawers() {
    overlay = $("[data-overlay]");
    if (overlay) overlay.hidden = false;
    on(document, "click", "[data-drawer-open]", (e, btn) => {
      e.preventDefault();
      open(btn.getAttribute("data-drawer-open"), btn);
    });
    on(document, "click", "[data-drawer-close]", (e, btn) => {
      const drawer = btn.closest("[data-drawer]");
      if (drawer) close();
    });
    overlay == null ? void 0 : overlay.addEventListener("click", () => close());
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && openDrawer) close();
    });
    on(document, "click", "[data-mobile-tab]", (e, tab) => {
      const drawer = tab.closest("[data-drawer]");
      $$("[data-mobile-tab]", drawer).forEach((t) => {
        const sel = t === tab;
        t.setAttribute("aria-selected", String(sel));
        t.tabIndex = sel ? 0 : -1;
      });
      $$("[data-mobile-panel]", drawer).forEach((p) => {
        p.hidden = p.dataset.mobilePanel !== tab.dataset.mobileTab;
      });
    });
    on(document, "click", "[data-mobile-sub-toggle]", (e, btn) => {
      const sub = document.getElementById(btn.getAttribute("aria-controls"));
      const open2 = btn.getAttribute("aria-expanded") === "true";
      btn.setAttribute("aria-expanded", String(!open2));
      sub == null ? void 0 : sub.classList.toggle("is-open", !open2);
    });
    on(document, "click", "[data-modal-close]", (e, btn) => {
      var _a;
      (_a = btn.closest("dialog")) == null ? void 0 : _a.close();
    });
    $$("dialog.modal").forEach((d) => {
      d.addEventListener("click", (e) => {
        if (e.target === d) d.close();
      });
      d.addEventListener("close", () => lockScroll(false));
    });
    window.matchMedia("(min-width: 992px)").addEventListener("change", (e) => {
      if (e.matches && (openDrawer == null ? void 0 : openDrawer.id) === "drawer-menu") close();
    });
  }
  function open(id, trigger) {
    const drawer = document.getElementById(id);
    if (!drawer || openDrawer === drawer) return;
    if (openDrawer) close(true);
    lastFocus = trigger || document.activeElement;
    drawer.inert = false;
    drawer.setAttribute("aria-hidden", "false");
    drawer.classList.add("is-open");
    overlay == null ? void 0 : overlay.classList.add("is-visible");
    lockScroll(true);
    openDrawer = drawer;
    trigger == null ? void 0 : trigger.setAttribute("aria-expanded", "true");
    releaseTrap = trapFocus(drawer);
    const first = $("[data-drawer-close], a, button, input", drawer);
    setTimeout(() => first == null ? void 0 : first.focus(), 60);
    drawer.dispatchEvent(new CustomEvent("drawer:open", { bubbles: true }));
  }
  function close(silent = false) {
    if (!openDrawer) return;
    const drawer = openDrawer;
    drawer.classList.remove("is-open");
    drawer.setAttribute("aria-hidden", "true");
    drawer.inert = true;
    overlay == null ? void 0 : overlay.classList.remove("is-visible");
    lockScroll(false);
    releaseTrap == null ? void 0 : releaseTrap();
    releaseTrap = null;
    $$(`[data-drawer-open="${drawer.id}"]`).forEach((b) => b.setAttribute("aria-expanded", "false"));
    openDrawer = null;
    if (!silent && lastFocus && document.contains(lastFocus)) lastFocus.focus();
    drawer.dispatchEvent(new CustomEvent("drawer:close", { bubbles: true }));
  }
  function openModal(dialog) {
    if (!dialog) return;
    if (typeof dialog.showModal === "function") {
      if (!dialog.open) dialog.showModal();
    } else dialog.setAttribute("open", "");
    lockScroll(true);
  }

  // src/js/components/swipers.js
  var registry = /* @__PURE__ */ new Map();
  function initSwipers(ctx4, root = document) {
    if (typeof window.Swiper !== "function") return;
    const isRTL = ctx4.dir === "rtl";
    const reduce = prefersReducedMotion();
    $$("[data-swiper]", root).forEach((el2) => {
      if (el2.swiper) return;
      const type = el2.dataset.swiper;
      const id = el2.dataset.carouselId;
      const nav = id ? { prevEl: $(`[data-carousel-prev="${id}"]`), nextEl: $(`[data-carousel-next="${id}"]`) } : null;
      const common = { a11y: { enabled: true }, watchOverflow: true, observer: true, observeParents: true, grabCursor: true, rtl: isRTL };
      let opts;
      switch (type) {
        case "hero":
          opts = {
            ...common,
            loop: el2.querySelectorAll(".swiper-slide").length > 1,
            speed: reduce ? 0 : 700,
            effect: "fade",
            fadeEffect: { crossFade: true },
            autoplay: reduce ? false : { delay: 6e3, disableOnInteraction: false, pauseOnMouseEnter: true },
            pagination: { el: $(".swiper-pagination", el2), clickable: true, renderBullet: (i, cls) => `<button type="button" class="${cls}" aria-label="${ctx4.t("common.page")} ${ctx4.digits(i + 1)}"></button>` },
            navigation: { prevEl: $("[data-swiper-prev]", el2), nextEl: $("[data-swiper-next]", el2) },
            keyboard: { enabled: true, onlyInViewport: true }
          };
          break;
        case "products": {
          const xl = Number(el2.dataset.slidesXl || 4);
          const xxl = Number(el2.dataset.slidesXxl || 4);
          opts = {
            ...common,
            slidesPerView: 2,
            spaceBetween: 12,
            speed: reduce ? 0 : 450,
            breakpoints: { 576: { slidesPerView: 2, spaceBetween: 16 }, 768: { slidesPerView: 3, spaceBetween: 16 }, 992: { slidesPerView: 3, spaceBetween: 20 }, 1200: { slidesPerView: xl, spaceBetween: 20 }, 1400: { slidesPerView: xxl, spaceBetween: 24 } },
            navigation: nav,
            keyboard: { enabled: true, onlyInViewport: true }
          };
          break;
        }
        case "brands":
          opts = { ...common, slidesPerView: "auto", spaceBetween: 8, loop: true, speed: reduce ? 0 : 5e3, autoplay: reduce ? false : { delay: 0, disableOnInteraction: false }, allowTouchMove: true, freeMode: true };
          break;
        case "reviews":
          opts = { ...common, slidesPerView: 1, spaceBetween: 16, speed: reduce ? 0 : 450, autoHeight: false, breakpoints: { 768: { slidesPerView: 2, spaceBetween: 20 }, 1200: { slidesPerView: 3, spaceBetween: 24 } }, navigation: nav, pagination: false };
          break;
        default:
          opts = { ...common };
      }
      const instance = new window.Swiper(el2, opts);
      if (id) registry.set(id, instance);
      if (type === "brands") el2.style.setProperty("--swiper-wrapper-transition-timing-function", "linear");
    });
  }

  // src/js/store/state.js
  var NS = "nexora:v1";
  var listeners = /* @__PURE__ */ new Set();
  var defaults = () => ({
    cart: [],
    // { key, id, qty, color, colorHex, size }
    wishlist: [],
    // [id]
    compare: [],
    // [id]
    recent: [],
    // [id]
    coupon: null,
    // 'NEXORA10'
    user: null,
    // { name, email }
    addresses: null
    // null = use demo defaults
  });
  var state = load();
  function load() {
    try {
      const raw = localStorage.getItem(NS);
      return raw ? { ...defaults(), ...JSON.parse(raw) } : defaults();
    } catch {
      return defaults();
    }
  }
  function persist() {
    try {
      localStorage.setItem(NS, JSON.stringify(state));
    } catch {
    }
  }
  function emit(type, payload) {
    for (const fn of listeners) fn({ type, payload, state });
  }
  function subscribe(fn) {
    listeners.add(fn);
    return () => listeners.delete(fn);
  }
  function getState() {
    return state;
  }
  window.addEventListener("storage", (e) => {
    if (e.key === NS) {
      state = load();
      emit("sync");
    }
  });
  var cartKey = (id, color, size) => [id, color || "", size || ""].join("|");
  function cartAdd({ id, qty = 1, color = "", colorHex = "", size = "", max = 5 }) {
    const key = cartKey(id, color, size);
    const existing = state.cart.find((i) => i.key === key);
    let clamped = false;
    if (existing) {
      const next = existing.qty + qty;
      clamped = next > max;
      existing.qty = Math.min(max, next);
    } else {
      state.cart.push({ key, id, qty: Math.min(max, qty), color, colorHex, size });
    }
    persist();
    emit("cart:add", { id, key, clamped });
    return { key, clamped };
  }
  function cartSetQty(key, qty, max = 5) {
    const item = state.cart.find((i) => i.key === key);
    if (!item) return;
    const q = Math.max(1, Math.min(max, Number(qty) || 1));
    if (item.qty === q) return;
    item.qty = q;
    persist();
    emit("cart:update", { key, qty: q });
  }
  function cartRemove(key) {
    const idx = state.cart.findIndex((i) => i.key === key);
    if (idx === -1) return null;
    const [removed] = state.cart.splice(idx, 1);
    persist();
    emit("cart:remove", removed);
    return removed;
  }
  function cartRestore(item) {
    if (!item || state.cart.some((i) => i.key === item.key)) return;
    state.cart.push(item);
    persist();
    emit("cart:add", { id: item.id, key: item.key });
  }
  function cartClear() {
    state.cart = [];
    state.coupon = null;
    persist();
    emit("cart:clear");
  }
  var cartCount = () => state.cart.reduce((a, i) => a + i.qty, 0);
  function setCoupon(code) {
    state.coupon = code || null;
    persist();
    emit("coupon", state.coupon);
  }
  function wishlistToggle(id) {
    const idx = state.wishlist.indexOf(id);
    const added = idx === -1;
    if (added) state.wishlist.unshift(id);
    else state.wishlist.splice(idx, 1);
    persist();
    emit("wishlist", { id, added });
    return added;
  }
  var wishlistHas = (id) => state.wishlist.includes(id);
  function wishlistClear() {
    state.wishlist = [];
    persist();
    emit("wishlist", { cleared: true });
  }
  function compareToggle(id, limit = 4) {
    const idx = state.compare.indexOf(id);
    if (idx > -1) {
      state.compare.splice(idx, 1);
      persist();
      emit("compare", { id, added: false });
      return { added: false };
    }
    if (state.compare.length >= limit) return { added: false, limit: true };
    state.compare.push(id);
    persist();
    emit("compare", { id, added: true });
    return { added: true };
  }
  var compareHas = (id) => state.compare.includes(id);
  function compareClear() {
    state.compare = [];
    persist();
    emit("compare", { cleared: true });
  }
  function recentPush(id, limit = 8) {
    state.recent = [id, ...state.recent.filter((x) => x !== id)].slice(0, limit);
    persist();
  }
  function login(user) {
    state.user = user;
    persist();
    emit("auth", user);
  }
  function logout() {
    state.user = null;
    persist();
    emit("auth", null);
  }
  var isLoggedIn = () => !!state.user;

  // src/js/core/render-pages.js
  function productPageHTML(ctx4, p, reviews = [], { related = [] } = {}) {
    var _a;
    const t = ctx4.t;
    const saving = p.oldPrice ? p.oldPrice - p.price : 0;
    const stockClass = !p.inStock ? "stock--out" : p.lowStock ? "stock--low" : "stock--in";
    const stockText = !p.inStock ? t("common.outOfStock") : p.lowStock ? t("common.lowStock", { n: ctx4.digits(p.stock) }) : t("common.inStock");
    const gallerySlides = p.images.map((img, i) => `
    <div class="swiper-slide">
      <a href="${ctx4.img(img)}" data-pswp-width="1280" data-pswp-height="1280" target="_blank" rel="noopener" aria-label="${esc(t("product.zoom"))}">
        <img src="${ctx4.img(img)}" width="640" height="640" alt="${esc(p.name)} \u2013 ${ctx4.digits(i + 1)}" ${i === 0 ? 'fetchpriority="high"' : 'loading="lazy"'} decoding="async">
      </a>
    </div>`).join("");
    const thumbs = p.images.map((img, i) => `<div class="swiper-slide" role="button" tabindex="0" aria-label="${esc(t("product.thumbs"))} ${ctx4.digits(i + 1)}"><img src="${ctx4.img(img)}" width="84" height="84" alt="" loading="lazy" decoding="async"></div>`).join("");
    const colors = p.colors.length ? `
    <fieldset class="variant" data-variant="color">
      <legend class="variant__label">${esc(t("product.selectColor"))} <span data-variant-value>${esc(p.colors[0].name)}</span></legend>
      <div class="variant__options">
        ${p.colors.map((c, i) => `<label class="variant__option"><input type="radio" name="color" value="${esc(c.name)}" data-hex="${esc(c.hex)}"${i === 0 ? " checked" : ""}><span class="swatch" style="background:${esc(c.hex)}" title="${esc(c.name)}"></span><span class="visually-hidden">${esc(c.name)}</span></label>`).join("")}
      </div>
    </fieldset>` : "";
    const sizes = p.sizes.length ? `
    <fieldset class="variant" data-variant="size">
      <legend class="variant__label" style="display:flex;inline-size:100%">${esc(t("product.selectSize"))} <span data-variant-value>${esc(((_a = p.sizes.find((s) => !s.disabled)) == null ? void 0 : _a.label) || "")}</span><button type="button" class="variant__guide" data-size-guide>${esc(t("product.sizeGuide"))}</button></legend>
      <div class="variant__options">
        ${p.sizes.map((s, i) => `<label class="variant__option"><input type="radio" name="size" value="${esc(s.label)}"${s.disabled ? " disabled" : ""}${!s.disabled && p.sizes.findIndex((x) => !x.disabled) === i ? " checked" : ""}><span class="size-chip">${esc(s.label)}</span></label>`).join("")}
      </div>
    </fieldset>` : "";
    const specs = p.specs.map((g) => `
    <div class="spec-group">
      <h3 class="spec-group__title">${esc(g.group)}</h3>
      <table class="spec-table"><tbody>${g.rows.map(([k, v]) => `<tr><th scope="row">${esc(k)}</th><td>${esc(v)}</td></tr>`).join("")}</tbody></table>
    </div>`).join("");
    const dist = [5, 4, 3, 2, 1].map((star) => {
      const n = reviews.filter((r) => Math.round(r.rating) === star).length;
      const pct = reviews.length ? Math.round(n / reviews.length * 100) : 0;
      return `<div class="rating-bar"><span class="rating-bar__label">${ctx4.digits(star)} ${starIcon()}</span><div class="rating-bar__track"><div class="rating-bar__fill" style="inline-size:${pct}%"></div></div><span class="rating-bar__value">${ctx4.digits(pct)}%</span></div>`;
    }).join("");
    const desc = (p.description || "").split(/\n\n+/).map((para) => `<p>${esc(para)}</p>`).join("");
    const highlights = p.highlights.map((h) => `<li>${icon("check", "xs")}<span>${esc(h)}</span></li>`).join("");
    const structured = {
      "@context": "https://schema.org",
      "@type": "Product",
      name: p.name,
      sku: p.sku,
      image: p.images.map((i) => `${ctx4.img(i)}`),
      description: p.short,
      brand: { "@type": "Brand", name: p.brandName },
      offers: { "@type": "Offer", price: p.price, priceCurrency: ctx4.currency.code || "IRR", availability: p.inStock ? "https://schema.org/InStock" : "https://schema.org/OutOfStock", itemCondition: "https://schema.org/NewCondition" },
      aggregateRating: { "@type": "AggregateRating", ratingValue: p.rating, reviewCount: p.reviewCount }
    };
    return `
<script type="application/ld+json">${JSON.stringify(structured)}<\/script>
<section class="section section--sm" data-product-page data-id="${p.id}" data-slug="${esc(p.slug)}" data-max="${p.maxQty}">
  <div class="container">
    <div class="product-layout">
      <!-- Gallery -->
      <div class="gallery" data-gallery>
        <div class="gallery__main swiper" data-gallery-main id="gallery-main">
          <div class="swiper-wrapper">${gallerySlides}</div>
          <div class="gallery__badges">${badgesHTML(ctx4, p, { max: 2 })}</div>
          <div class="gallery__tools">
            <button type="button" class="icon-btn icon-btn--circle" data-action="wishlist" data-id="${p.id}" aria-label="${esc(t("product.wishlist"))}" aria-pressed="false">${icon("heart", "sm")}</button>
            <button type="button" class="icon-btn icon-btn--circle" data-action="compare" data-id="${p.id}" aria-label="${esc(t("product.compare"))}" aria-pressed="false">${icon("compare", "sm")}</button>
            <button type="button" class="icon-btn icon-btn--circle" data-action="share" aria-label="${esc(t("product.share"))}">${icon("share2", "sm")}</button>
          </div>
          <span class="gallery__zoom-hint">${icon("zoom-in", "xs")} ${esc(t("product.zoom"))}</span>
          <button type="button" class="gallery__nav gallery__nav--prev" data-gallery-prev aria-label="${esc(t("product.prevImage"))}">${icon("chevron-right", "xs", "icon--flip-ltr")}</button>
          <button type="button" class="gallery__nav gallery__nav--next" data-gallery-next aria-label="${esc(t("product.nextImage"))}">${icon("chevron-left", "xs", "icon--flip-ltr")}</button>
        </div>
        <div class="gallery__thumbs swiper" data-gallery-thumbs aria-label="${esc(t("product.thumbs"))}"><div class="swiper-wrapper">${thumbs}</div></div>
      </div>

      <!-- Info -->
      <div class="product-info">
        <div class="product-info__brand"><span>${esc(t("product.brand"))} <a href="${ctx4.brandUrl(p.brand)}">${esc(p.brandName)}</a></span><span class="sep"></span><span>${esc(t("product.sku"))} <span class="ltr">${esc(p.sku)}</span></span></div>
        <h1 class="product-info__title">${esc(p.name)}</h1>
        <div class="product-info__meta">
          ${ratingHTML(ctx4, p.rating, p.reviewCount, { size: "lg" })}
          <a href="#tab-reviews" data-tab-jump="reviews">${esc(t("product.reviewsCount", { n: ctx4.num(p.reviewCount) }))}</a>
          <span class="sep"></span>
          <a href="#tab-qa" data-tab-jump="qa">${esc(t("product.questions", { n: ctx4.digits(4) }))}</a>
          <span class="sep"></span>
          <span>${esc(t("common.sold", { n: ctx4.num(p.sold) }))}</span>
        </div>

        <ul class="product-info__highlights" aria-label="${esc(t("product.highlights"))}">${highlights}</ul>

        <form class="variants" data-variants>${colors}${sizes}</form>

        <div class="buy-box">
          <div class="product-info__price">
            ${priceHTML(ctx4, p, { size: "lg", showDiscount: true })}
            ${saving ? `<span class="product-info__saving">${esc(t("product.youSave", { n: ctx4.price(saving) }))}</span>` : ""}
          </div>
          <span class="stock ${stockClass}" data-stock>${esc(stockText)}</span>
          <div class="buy-box__row">
            ${qtyHTML(ctx4, 1, p.maxQty)}
            <button type="button" class="btn btn--primary btn--lg" data-action="add-to-cart" data-id="${p.id}" data-with-variants${p.inStock ? "" : " disabled"}>${icon("cart-add", "sm")}<span>${esc(t("product.addToCart"))}</span></button>
            <button type="button" class="btn btn--dark btn--lg" data-action="buy-now" data-id="${p.id}" data-with-variants${p.inStock ? "" : " disabled"}>${icon("cash-dollar", "sm")}<span>${esc(t("product.buyNow"))}</span></button>
          </div>
          <div class="buy-box__secondary">
            <button type="button" data-action="wishlist" data-id="${p.id}" aria-pressed="false">${icon("heart", "xs")}<span>${esc(t("product.wishlist"))}</span></button>
            <button type="button" data-action="compare" data-id="${p.id}" aria-pressed="false">${icon("compare", "xs")}<span>${esc(t("product.compare"))}</span></button>
            <button type="button" data-action="share">${icon("share2", "xs")}<span>${esc(t("product.share"))}</span></button>
          </div>
        </div>

        <div class="assurance">
          <div class="assurance__item">${icon("shield-check")}<div><strong>${esc(t("product.warranty"))}</strong>${esc(t("product.warrantyText"))}</div></div>
          <div class="assurance__item">${icon("truck")}<div><strong>${esc(t("product.shipping"))}</strong>${esc(t("product.shippingText"))}</div></div>
          <div class="assurance__item">${icon("undo")}<div><strong>${esc(t("product.returns"))}</strong>${esc(t("product.returnsText"))}</div></div>
          <div class="assurance__item">${icon("lock")}<div><strong>${esc(t("product.securePay"))}</strong>${esc(t("product.securePayText"))}</div></div>
        </div>
        <div class="seller-box">
          <div><div class="seller-box__name">${icon("store", "sm")}${esc(t("product.seller"))} ${esc(t("product.sellerName"))} ${icon("checkmark-circle", "xs")}</div><div class="seller-box__meta">${esc(t("product.sellerMeta"))}</div></div>
          <a class="btn btn--outline btn--sm" href="${ctx4.url("about.html")}">${esc(t("common.details"))}</a>
        </div>
      </div>

      <!-- Sticky aside (xxl only) -->
      <aside class="product-layout__aside" aria-label="${esc(t("product.buyNow"))}">
        <div class="product-aside-box">
          <div class="text-muted small">${esc(t("product.seller"))} <strong class="text-strong">${esc(t("product.sellerName"))}</strong></div>
          <span class="stock ${stockClass}">${esc(stockText)}</span>
          ${priceHTML(ctx4, p, { size: "lg", showDiscount: true })}
          <button type="button" class="btn btn--primary btn--block" data-action="add-to-cart" data-id="${p.id}" data-with-variants${p.inStock ? "" : " disabled"}>${icon("cart-add", "sm")}${esc(t("product.addToCart"))}</button>
          <ul class="stack--sm small text-muted" style="display:grid;gap:8px">
            <li style="display:flex;gap:8px;align-items:center">${icon("truck", "xs")}${esc(t("product.shippingText"))}</li>
            <li style="display:flex;gap:8px;align-items:center">${icon("undo", "xs")}${esc(t("product.returns"))}</li>
            <li style="display:flex;gap:8px;align-items:center">${icon("shield-check", "xs")}${esc(t("product.warranty"))}</li>
          </ul>
        </div>
      </aside>
    </div>

    <!-- Details tabs -->
    <div class="product-details tabs" data-tabs>
      <div class="tabs__list" role="tablist" aria-label="${esc(t("product.title"))}">
        <button class="tabs__tab" role="tab" id="tab-btn-description" aria-selected="true" aria-controls="tab-description" data-tab="description">${icon("file-empty", "xs")}${esc(t("product.tabDescription"))}</button>
        <button class="tabs__tab" role="tab" id="tab-btn-specs" aria-selected="false" aria-controls="tab-specs" data-tab="specs" tabindex="-1">${icon("list", "xs")}${esc(t("product.tabSpecs"))}</button>
        <button class="tabs__tab" role="tab" id="tab-btn-reviews" aria-selected="false" aria-controls="tab-reviews" data-tab="reviews" tabindex="-1">${icon("star", "xs")}${esc(t("product.tabReviews"))}<span class="badge badge--soft">${ctx4.num(p.reviewCount)}</span></button>
        <button class="tabs__tab" role="tab" id="tab-btn-qa" aria-selected="false" aria-controls="tab-qa" data-tab="qa" tabindex="-1">${icon("bubble-question", "xs")}${esc(t("product.tabQa"))}</button>
        <button class="tabs__tab" role="tab" id="tab-btn-shipping" aria-selected="false" aria-controls="tab-shipping" data-tab="shipping" tabindex="-1">${icon("truck", "xs")}${esc(t("product.tabShipping"))}</button>
      </div>

      <div class="tabs__panel" role="tabpanel" id="tab-description" aria-labelledby="tab-btn-description">
        <h2 class="visually-hidden">${esc(t("product.tabDescription"))}</h2>
        <div class="product-desc-grid">
          <div class="prose">${desc}</div>
          <div>
            <div class="card-surface card-surface--pad">
              <h3 class="h5" style="margin-block-end:var(--space-4)">${esc(t("product.highlights"))}</h3>
              <ul class="product-info__highlights" style="background:transparent;padding:0">${highlights}</ul>
              <hr class="divider">
              <div class="cluster">${p.tags.map((tag) => `<a class="chip" href="${ctx4.url("search.html")}?q=${encodeURIComponent(tag)}">${icon("tag", "xs")}${esc(tag)}</a>`).join("")}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="tabs__panel" role="tabpanel" id="tab-specs" aria-labelledby="tab-btn-specs" hidden>
        <h2 class="visually-hidden">${esc(t("product.tabSpecs"))}</h2>
        <div class="row"><div class="col-lg-9">${specs}</div></div>
      </div>

      <div class="tabs__panel" role="tabpanel" id="tab-reviews" aria-labelledby="tab-btn-reviews" hidden>
        <h2 class="visually-hidden">${esc(t("product.reviewsTitle"))}</h2>
        <div class="reviews-summary">
          <div class="reviews-summary__score">
            <span class="reviews-summary__value">${ctx4.digits(p.rating.toFixed(1))}</span>
            ${ratingHTML(ctx4, p.rating, null, { size: "lg", showValue: false, showCount: false })}
            <span class="reviews-summary__count">${esc(t("product.basedOn", { n: ctx4.num(p.reviewCount) }))}</span>
          </div>
          <div class="rating-bars">${dist}</div>
          <div><button type="button" class="btn btn--dark" data-review-toggle aria-expanded="false" aria-controls="review-form">${icon("pencil", "xs")}${esc(t("product.writeReview"))}</button></div>
        </div>
        <form class="review-form" id="review-form" data-review-form hidden novalidate>
          <h3 class="review-form__title">${esc(t("product.reviewFormTitle"))}</h3>
          <div class="form-group">
            <span class="form-label">${esc(t("product.yourRating"))} <span class="req">*</span></span>
            <div class="rating-input" role="radiogroup" aria-label="${esc(t("product.yourRating"))}">
              ${[5, 4, 3, 2, 1].map((n) => `<input type="radio" id="rate-${n}" name="rating" value="${n}" required><label for="rate-${n}" title="${esc(t("product.stars", { n: ctx4.digits(n) }))}">${starIcon()}<span class="visually-hidden">${esc(t("product.stars", { n: ctx4.digits(n) }))}</span></label>`).join("")}
            </div>
            <span class="form-error"></span>
          </div>
          <div class="form-row">
            <div class="form-group"><label class="form-label" for="review-name">${esc(t("blog.name"))} <span class="req">*</span></label><input id="review-name" class="form-control" name="name" required minlength="2"><span class="form-error"></span></div>
            <div class="form-group"><label class="form-label" for="review-title">${esc(t("product.reviewTitle"))} <span class="req">*</span></label><input id="review-title" class="form-control" name="title" required minlength="3"><span class="form-error"></span></div>
          </div>
          <div class="form-group"><label class="form-label" for="review-text">${esc(t("product.reviewText"))} <span class="req">*</span></label><textarea id="review-text" class="form-control" name="text" required minlength="20" placeholder="${esc(t("product.reviewTextPlaceholder"))}"></textarea><span class="form-error"></span></div>
          <div class="cluster"><button type="submit" class="btn btn--primary">${esc(t("product.submitReview"))}</button><button type="button" class="btn btn--ghost" data-review-cancel>${esc(t("common.cancel"))}</button></div>
        </form>
        <div data-reviews-list>${reviews.map((r) => reviewHTML(ctx4, r)).join("")}</div>
      </div>

      <div class="tabs__panel" role="tabpanel" id="tab-qa" aria-labelledby="tab-btn-qa" hidden>
        <h2 class="visually-hidden">${esc(t("product.tabQa"))}</h2>
        <div class="row">
          <div class="col-lg-8">
            ${qaHTML(ctx4, p)}
            <form class="review-form" data-qa-form novalidate style="margin-block-start:var(--space-6)">
              <h3 class="review-form__title">${esc(t("product.askQuestion"))}</h3>
              <div class="form-group"><label class="visually-hidden" for="qa-text">${esc(t("product.askQuestion"))}</label><textarea id="qa-text" class="form-control" name="question" required minlength="10" placeholder="${esc(t("product.askPlaceholder"))}"></textarea><span class="form-error"></span></div>
              <div><button type="submit" class="btn btn--dark">${esc(t("product.submitQuestion"))}</button></div>
            </form>
          </div>
        </div>
      </div>

      <div class="tabs__panel" role="tabpanel" id="tab-shipping" aria-labelledby="tab-btn-shipping" hidden>
        <h2 class="visually-hidden">${esc(t("product.tabShipping"))}</h2>
        <div class="row g-4">
          <div class="col-md-6"><h3 class="h5" style="display:flex;gap:8px;align-items:center;margin-block-end:var(--space-3)">${icon("truck", "sm")}${esc(t("product.shippingInfoTitle"))}</h3><p>${esc(t("product.shippingInfo"))}</p></div>
          <div class="col-md-6"><h3 class="h5" style="display:flex;gap:8px;align-items:center;margin-block-end:var(--space-3)">${icon("undo", "sm")}${esc(t("product.returnsInfoTitle"))}</h3><p>${esc(t("product.returnsInfo"))}</p></div>
        </div>
      </div>
    </div>
  </div>
</section>

${related.length ? `
<section class="section bg-surface" aria-labelledby="sec-related">
  <div class="container">
    <div class="section-head">
      <div><h2 class="section-head__title" id="sec-related">${esc(t("product.relatedTitle"))}</h2><p class="section-head__sub">${esc(t("product.relatedSub"))}</p></div>
      <div class="section-head__aside"><div class="carousel-nav"><button type="button" class="carousel-nav__btn" data-carousel-prev="related" aria-label="${esc(t("common.prev"))}">${icon("chevron-right", "xs", "icon--flip-ltr")}</button><button type="button" class="carousel-nav__btn" data-carousel-next="related" aria-label="${esc(t("common.next"))}">${icon("chevron-left", "xs", "icon--flip-ltr")}</button></div></div>
    </div>
    <div class="product-carousel"><div class="swiper" data-swiper="products" data-carousel-id="related"><div class="swiper-wrapper">${related.map((r) => `<div class="swiper-slide">${productCardHTML(ctx4, r)}</div>`).join("")}</div></div></div>
  </div>
</section>` : ""}

<section class="section" data-recent-section hidden aria-labelledby="sec-recent">
  <div class="container">
    <div class="section-head"><div><h2 class="section-head__title" id="sec-recent">${esc(t("product.recentTitle"))}</h2></div></div>
    <div class="product-carousel"><div class="swiper" data-swiper="products" data-carousel-id="recent"><div class="swiper-wrapper" data-recent-list></div></div></div>
  </div>
</section>

<div class="sticky-buy" data-sticky-buy>
  <div style="min-inline-size:0">
    <div class="truncate small fw-medium">${esc(p.name)}</div>
    ${priceHTML(ctx4, p, { size: "sm" })}
  </div>
  <button type="button" class="btn btn--primary" data-action="add-to-cart" data-id="${p.id}" data-with-variants${p.inStock ? "" : " disabled"}>${icon("cart-add", "xs")}${esc(t("product.stickyAdd"))}</button>
</div>`;
  }
  function qaHTML(ctx4, p) {
    const t = ctx4.t;
    const fa = ctx4.lang === "fa";
    const items = fa ? [
      ["\u0622\u06CC\u0627 \u0627\u06CC\u0646 \u0645\u062D\u0635\u0648\u0644 \u06AF\u0627\u0631\u0627\u0646\u062A\u06CC \u062F\u0627\u0631\u062F\u061F", `\u0628\u0644\u0647\u060C ${p.name} \u0628\u0627 \u06F1\u06F8 \u0645\u0627\u0647 \u06AF\u0627\u0631\u0627\u0646\u062A\u06CC \u0634\u0631\u06A9\u062A\u06CC \u0648 \u0636\u0645\u0627\u0646\u062A \u0627\u0635\u0627\u0644\u062A \u0646\u06A9\u0633\u0648\u0631\u0627 \u0627\u0631\u0633\u0627\u0644 \u0645\u06CC\u200C\u0634\u0648\u062F.`],
      ["\u0632\u0645\u0627\u0646 \u0627\u0631\u0633\u0627\u0644 \u0628\u0647 \u0634\u0647\u0631\u0633\u062A\u0627\u0646 \u0686\u0642\u062F\u0631 \u0627\u0633\u062A\u061F", "\u0633\u0641\u0627\u0631\u0634\u200C\u0647\u0627\u06CC \u062B\u0628\u062A\u200C\u0634\u062F\u0647 \u062A\u0627 \u0633\u0627\u0639\u062A \u06F1\u06F4\u060C \u0647\u0645\u0627\u0646 \u0631\u0648\u0632 \u0627\u0631\u0633\u0627\u0644 \u0645\u06CC\u200C\u0634\u0648\u0646\u062F \u0648 \u0645\u0639\u0645\u0648\u0644\u0627\u064B \u0638\u0631\u0641 \u06F4\u06F8 \u062A\u0627 \u06F7\u06F2 \u0633\u0627\u0639\u062A \u0628\u0647 \u062F\u0633\u062A \u0634\u0645\u0627 \u0645\u06CC\u200C\u0631\u0633\u0646\u062F."],
      ["\u0627\u0645\u06A9\u0627\u0646 \u067E\u0631\u062F\u0627\u062E\u062A \u062F\u0631 \u0645\u062D\u0644 \u0648\u062C\u0648\u062F \u062F\u0627\u0631\u062F\u061F", "\u0628\u0631\u0627\u06CC \u062A\u0647\u0631\u0627\u0646 \u0628\u0644\u0647\u061B \u062F\u0631 \u0633\u0627\u06CC\u0631 \u0634\u0647\u0631\u0647\u0627 \u067E\u0631\u062F\u0627\u062E\u062A \u0627\u06CC\u0646\u062A\u0631\u0646\u062A\u06CC \u0641\u0639\u0627\u0644 \u0627\u0633\u062A."],
      ["\u0627\u06AF\u0631 \u067E\u0634\u06CC\u0645\u0627\u0646 \u0634\u062F\u0645 \u0645\u06CC\u200C\u062A\u0648\u0627\u0646\u0645 \u0645\u0631\u062C\u0648\u0639 \u06A9\u0646\u0645\u061F", "\u062A\u0627 \u06F7 \u0631\u0648\u0632 \u067E\u0633 \u0627\u0632 \u062A\u062D\u0648\u06CC\u0644\u060C \u062F\u0631 \u0635\u0648\u0631\u062A \u0628\u0627\u0632 \u0646\u0634\u062F\u0646 \u067E\u0644\u0645\u0628 \u0648 \u0633\u0627\u0644\u0645 \u0628\u0648\u062F\u0646 \u0628\u0633\u062A\u0647\u200C\u0628\u0646\u062F\u06CC\u060C \u0627\u0645\u06A9\u0627\u0646 \u0628\u0627\u0632\u06AF\u0634\u062A \u0648\u062C\u0648\u062F \u062F\u0627\u0631\u062F."]
    ] : [
      ["Does this product come with a warranty?", `Yes, ${p.name} ships with an 18-month warranty and Nexora's authenticity guarantee.`],
      ["How long does shipping take?", "Orders placed before 2 PM ship the same day and usually arrive within 48 to 72 hours."],
      ["Is cash on delivery available?", "Yes in the metro area; other regions support online payment."],
      ["Can I return it if I change my mind?", "Within 7 days of delivery, as long as the seal is intact and the packaging is undamaged."]
    ];
    return `<h3 class="h5" style="margin-block-end:var(--space-3)">${esc(t("product.qaTitle"))}</h3>` + items.map(([q, a]) => `<div class="qa-item"><div class="qa-item__q" data-label="${fa ? "\u0633" : "Q"}">${esc(q)}</div><div class="qa-item__a" data-label="${fa ? "\u062C" : "A"}">${esc(a)}</div></div>`).join("");
  }
  function quickViewHTML(ctx4, p) {
    var _a;
    const t = ctx4.t;
    const stockClass = !p.inStock ? "stock--out" : p.lowStock ? "stock--low" : "stock--in";
    const stockText = !p.inStock ? t("common.outOfStock") : p.lowStock ? t("common.lowStock", { n: ctx4.digits(p.stock) }) : t("common.inStock");
    return `<div class="quick-view" data-product-page data-id="${p.id}" data-max="${p.maxQty}">
    <div class="quick-view__media"><img src="${ctx4.img(p.image)}" width="640" height="640" alt="${esc(p.name)}"></div>
    <div class="quick-view__body">
      <div class="product-card__category">${esc(p.categoryName)} \xB7 ${esc(p.brandName)}</div>
      <h2 class="h4">${esc(p.name)}</h2>
      ${ratingHTML(ctx4, p.rating, p.reviewCount)}
      <p class="quick-view__desc">${esc(p.short)}</p>
      <form class="variants" data-variants>
        ${p.colors.length ? `<fieldset class="variant" data-variant="color"><legend class="variant__label">${esc(t("product.selectColor"))} <span data-variant-value>${esc(p.colors[0].name)}</span></legend><div class="variant__options">${p.colors.map((c, i) => `<label class="variant__option"><input type="radio" name="color" value="${esc(c.name)}" data-hex="${esc(c.hex)}"${i === 0 ? " checked" : ""}><span class="swatch" style="background:${esc(c.hex)}"></span><span class="visually-hidden">${esc(c.name)}</span></label>`).join("")}</div></fieldset>` : ""}
        ${p.sizes.length ? `<fieldset class="variant" data-variant="size"><legend class="variant__label">${esc(t("product.selectSize"))} <span data-variant-value>${esc(((_a = p.sizes.find((s) => !s.disabled)) == null ? void 0 : _a.label) || "")}</span></legend><div class="variant__options">${p.sizes.map((s, i) => `<label class="variant__option"><input type="radio" name="size" value="${esc(s.label)}"${s.disabled ? " disabled" : ""}${!s.disabled && p.sizes.findIndex((x) => !x.disabled) === i ? " checked" : ""}><span class="size-chip">${esc(s.label)}</span></label>`).join("")}</div></fieldset>` : ""}
      </form>
      <div class="product-info__price">${priceHTML(ctx4, p, { size: "lg", showDiscount: true })}</div>
      <span class="stock ${stockClass}">${esc(stockText)}</span>
      <div class="buy-box__row">
        ${qtyHTML(ctx4, 1, p.maxQty)}
        <button type="button" class="btn btn--primary" data-action="add-to-cart" data-id="${p.id}" data-with-variants${p.inStock ? "" : " disabled"}>${icon("cart-add", "sm")}${esc(t("product.addToCart"))}</button>
      </div>
      <div class="buy-box__secondary">
        <button type="button" data-action="wishlist" data-id="${p.id}" aria-pressed="false">${icon("heart", "xs")}<span>${esc(t("product.wishlist"))}</span></button>
        <button type="button" data-action="compare" data-id="${p.id}" aria-pressed="false">${icon("compare", "xs")}<span>${esc(t("product.compare"))}</span></button>
        <a href="${ctx4.productUrl(p)}">${icon("link", "xs")}<span>${esc(t("product.fullDetails"))}</span></a>
      </div>
    </div>
  </div>`;
  }

  // src/js/components/cart-ui.js
  var ctx3;
  function cartLines() {
    return getState().cart.map((i) => ({ ...i, product: ctx3.product(i.id) })).filter((i) => i.product);
  }
  function cartTotals() {
    const lines = cartLines();
    const subtotalOld = lines.reduce((a, l) => a + (l.product.oldPrice || l.product.price) * l.qty, 0);
    const subtotal = lines.reduce((a, l) => a + l.product.price * l.qty, 0);
    const discount = subtotalOld - subtotal;
    const coupons = ctx3.config.coupons || {};
    const code = getState().coupon;
    const coupon = code && coupons[code] ? coupons[code] : null;
    const couponValue = coupon ? coupon.type === "percent" ? Math.round(subtotal * coupon.value / 100) : coupon.value : 0;
    const afterCoupon = Math.max(0, subtotal - couponValue);
    const freeShipping = ctx3.config.freeShipping || 0;
    const taxRate = ctx3.config.taxRate || 0;
    return { lines, count: lines.reduce((a, l) => a + l.qty, 0), subtotal, subtotalOld, discount, coupon: code && coupon ? code : null, couponValue, afterCoupon, freeShipping, freeShippingLeft: Math.max(0, freeShipping - afterCoupon), taxRate };
  }
  function initCartUI(appCtx) {
    var _a;
    ctx3 = appCtx;
    renderBadges();
    renderMiniCart();
    renderCompareBar();
    syncPressedStates(document);
    subscribe(({ type, payload }) => {
      renderBadges();
      if (type.startsWith("cart") || type === "sync" || type === "coupon") renderMiniCart();
      if (type === "compare" || type === "sync") renderCompareBar();
      syncPressedStates(document);
      if (type === "auth" || type === "sync") renderAuth();
    });
    renderAuth();
    on(document, "click", "[data-action]", (e, btn) => {
      const action = btn.dataset.action;
      const id = Number(btn.dataset.id);
      const product = id ? ctx3.product(id) : null;
      switch (action) {
        case "add-to-cart": {
          e.preventDefault();
          addToCart(btn, product);
          break;
        }
        case "buy-now": {
          e.preventDefault();
          if (addToCart(btn, product, { silent: true })) location.href = ctx3.url("checkout.html");
          break;
        }
        case "wishlist": {
          e.preventDefault();
          if (!product) return;
          const added = wishlistToggle(id);
          toast(ctx3.t(added ? "toast.addedToWishlist" : "toast.removedFromWishlist"), { type: added ? "success" : "default", action: added ? ctx3.t("header.wishlist") : void 0, actionHref: ctx3.url("wishlist.html") });
          break;
        }
        case "compare": {
          e.preventDefault();
          if (!product) return;
          const r = compareToggle(id, ctx3.config.compareLimit || 4);
          if (r.limit) toast(ctx3.t("toast.compareLimit"), { type: "error" });
          else toast(ctx3.t(r.added ? "toast.addedToCompare" : "toast.removedFromCompare"), { type: r.added ? "success" : "default", action: r.added ? ctx3.t("common.compareNow") : void 0, actionHref: ctx3.url("compare.html") });
          break;
        }
        case "quick-view": {
          e.preventDefault();
          openQuickView(product);
          break;
        }
        case "cart-remove": {
          e.preventDefault();
          const removed = cartRemove(btn.dataset.key);
          if (removed) {
            const p = ctx3.product(removed.id);
            toast(ctx3.t("toast.removedFromCart", { name: (p == null ? void 0 : p.name) || "" }), { onUndo: () => cartRestore(removed) });
          }
          break;
        }
        case "cart-to-wishlist": {
          e.preventDefault();
          cartRemove(btn.dataset.key);
          if (!wishlistHas(id)) wishlistToggle(id);
          toast(ctx3.t("toast.addedToWishlist"), { type: "success" });
          break;
        }
        case "share": {
          e.preventDefault();
          share();
          break;
        }
        case "copy-link": {
          e.preventDefault();
          copyLink();
          break;
        }
        case "notify": {
          e.preventDefault();
          toast(ctx3.t("toast.saved"), { type: "success" });
          break;
        }
        default:
          break;
      }
    });
    on(document, "click", "[data-qty] button", (e, btn) => {
      const wrap = btn.closest("[data-qty]");
      const input = $("input", wrap);
      const max = Number(wrap.dataset.max || input.max || 5);
      let v = Number(input.value) || 1;
      v = btn.hasAttribute("data-qty-inc") ? v + 1 : v - 1;
      if (v > max) {
        toast(ctx3.t("toast.maxQty", { n: ctx3.digits(max) }), { type: "info" });
        v = max;
      }
      v = Math.max(1, v);
      input.value = v;
      updateQtyButtons(wrap);
      input.dispatchEvent(new Event("change", { bubbles: true }));
    });
    on(document, "change", "[data-qty] input", (e, input) => {
      const wrap = input.closest("[data-qty]");
      const max = Number(wrap.dataset.max || input.max || 5);
      let v = Math.round(Number(input.value) || 1);
      if (v > max) {
        toast(ctx3.t("toast.maxQty", { n: ctx3.digits(max) }), { type: "info" });
        v = max;
      }
      input.value = Math.max(1, v);
      updateQtyButtons(wrap);
      if (wrap.dataset.key) cartSetQty(wrap.dataset.key, input.value, max);
    });
    on(document, "change", "[data-variant] input", (e, input) => {
      const fs = input.closest("[data-variant]");
      const label = $("[data-variant-value]", fs);
      if (label) label.textContent = input.value;
    });
    (_a = $("[data-compare-clear]")) == null ? void 0 : _a.addEventListener("click", () => compareClear());
    on(document, "click", "[data-logout]", (e) => {
      e.preventDefault();
      if (confirm(ctx3.t("account.logoutConfirm"))) {
        logout();
        toast(ctx3.t("account.loggedOut"), { type: "success" });
        setTimeout(() => {
          location.href = ctx3.url("index.html");
        }, 700);
      }
    });
  }
  function updateQtyButtons(wrap) {
    const input = $("input", wrap);
    const max = Number(wrap.dataset.max || input.max || 5);
    const v = Number(input.value) || 1;
    const dec = $("[data-qty-dec]", wrap);
    const inc = $("[data-qty-inc]", wrap);
    if (dec) dec.disabled = v <= 1;
    if (inc) inc.disabled = v >= max;
  }
  function readVariants(btn) {
    const scope = btn.closest("[data-product-page]") || document;
    const form = $("[data-variants]", scope);
    const color = form ? $('input[name="color"]:checked', form) : null;
    const size = form ? $('input[name="size"]:checked', form) : null;
    const qtyInput = $("[data-qty] input", scope);
    return { color: (color == null ? void 0 : color.value) || "", colorHex: (color == null ? void 0 : color.dataset.hex) || "", size: (size == null ? void 0 : size.value) || "", qty: Number(qtyInput == null ? void 0 : qtyInput.value) || 1 };
  }
  function addToCart(btn, product, { silent = false } = {}) {
    var _a, _b, _c, _d;
    if (!product) return false;
    if (!product.inStock) {
      toast(ctx3.t("toast.outOfStock"), { type: "error" });
      return false;
    }
    const withVariants = btn.hasAttribute("data-with-variants");
    const v = withVariants ? readVariants(btn) : { color: ((_b = (_a = product.colors) == null ? void 0 : _a[0]) == null ? void 0 : _b.name) || "", colorHex: ((_d = (_c = product.colors) == null ? void 0 : _c[0]) == null ? void 0 : _d.hex) || "", size: "", qty: 1 };
    const { clamped } = cartAdd({ id: product.id, qty: v.qty, color: v.color, colorHex: v.colorHex, size: v.size, max: product.maxQty });
    if (clamped) toast(ctx3.t("toast.maxQty", { n: ctx3.digits(product.maxQty) }), { type: "info" });
    else if (!silent) toast(ctx3.t("toast.addedToCart", { name: product.name }), { type: "success", action: ctx3.t("toast.viewCart"), actionHref: ctx3.url("cart.html") });
    btn.classList.add("is-added");
    const label = $(".product-card__add-text, span:not(.icon)", btn);
    const prevText = label == null ? void 0 : label.textContent;
    if (label) label.textContent = ctx3.t("common.added");
    setTimeout(() => {
      btn.classList.remove("is-added");
      if (label && prevText) label.textContent = prevText;
    }, 1400);
    announce(ctx3.t("toast.addedToCart", { name: product.name }));
    return true;
  }
  function renderBadges() {
    const counts = { cart: cartCount(), wishlist: getState().wishlist.length, compare: getState().compare.length };
    $$("[data-count]").forEach((el2) => {
      var _a;
      const n = (_a = counts[el2.dataset.count]) != null ? _a : 0;
      el2.textContent = ctx3.digits(n);
      el2.setAttribute("data-count", el2.dataset.count);
      el2.hidden = n === 0 && el2.classList.contains("icon-btn__badge");
      if (el2.classList.contains("badge")) el2.hidden = false;
    });
    const total = $("[data-cart-total]");
    if (total) {
      const t = cartTotals();
      total.textContent = t.count ? ctx3.money(t.afterCoupon) : ctx3.t("header.cartEmptyShort");
    }
  }
  function renderMiniCart() {
    const list = $("[data-mini-cart-list]");
    if (!list) return;
    const t = cartTotals();
    const empty = $("[data-mini-cart-empty]");
    const foot = $("[data-mini-cart-foot]");
    const count = $("[data-mini-cart-count]");
    list.innerHTML = t.lines.map((l) => miniCartItemHTML(ctx3, l)).join("");
    if (empty) empty.hidden = t.count > 0;
    if (foot) foot.hidden = t.count === 0;
    if (count) count.textContent = t.count ? `${ctx3.digits(t.count)} ${ctx3.t("header.items")}` : "";
    const sub = $("[data-mini-cart-subtotal]");
    if (sub) sub.textContent = ctx3.money(t.afterCoupon);
  }
  function syncPressedStates(root) {
    $$('[data-action="wishlist"][data-id]', root).forEach((b) => {
      const active = wishlistHas(Number(b.dataset.id));
      b.classList.toggle("is-active", active);
      b.setAttribute("aria-pressed", String(active));
      b.setAttribute("aria-label", ctx3.t(active ? "common.removeFromWishlist" : "common.addToWishlist"));
      b.title = b.getAttribute("aria-label");
    });
    $$('[data-action="compare"][data-id]', root).forEach((b) => {
      const active = compareHas(Number(b.dataset.id));
      b.classList.toggle("is-active", active);
      b.setAttribute("aria-pressed", String(active));
      b.setAttribute("aria-label", ctx3.t(active ? "common.removeFromCompare" : "common.addToCompare"));
      b.title = b.getAttribute("aria-label");
    });
  }
  function renderCompareBar() {
    const bar = $("[data-compare-bar]");
    if (!bar) return;
    const ids = getState().compare;
    const hideOn = ["compare", "checkout", "login", "register", "forgot"];
    bar.classList.toggle("is-visible", ids.length > 0 && !hideOn.includes(ctx3.page));
    $("[data-compare-thumbs]", bar).innerHTML = ids.map((id) => ctx3.product(id)).filter(Boolean).map((p) => `<img src="${ctx3.img(p.image)}" width="32" height="32" alt="${p.name}">`).join("");
    $("[data-compare-label]", bar).textContent = ctx3.t("compare.bar", { n: ctx3.digits(ids.length) });
  }
  function openQuickView(product) {
    var _a;
    if (!product) return;
    const dialog = $("[data-quick-view]");
    const body = $("[data-quick-view-body]", dialog);
    if (!dialog || !body) {
      location.href = ctx3.productUrl(product);
      return;
    }
    body.innerHTML = quickViewHTML(ctx3, product);
    syncPressedStates(body);
    openModal(dialog);
    (_a = $(".modal__close", dialog)) == null ? void 0 : _a.focus();
  }
  function renderAuth() {
    const user = getState().user;
    $$("[data-auth-link]").forEach((a) => {
      a.href = ctx3.url(user ? "account.html" : "login.html");
    });
    $$("[data-auth-label]").forEach((el2) => {
      el2.textContent = user ? user.name : ctx3.t("header.login");
    });
    if (user) {
      $$("[data-user-name]").forEach((el2) => {
        el2.textContent = user.name;
      });
      $$("[data-user-email]").forEach((el2) => {
        el2.textContent = user.email || el2.textContent;
      });
      $$("[data-user-initial]").forEach((el2) => {
        el2.textContent = user.name.trim().charAt(0);
      });
    }
  }
  async function share() {
    const data = { title: document.title, url: location.href };
    try {
      if (navigator.share) {
        await navigator.share(data);
        return;
      }
    } catch {
      return;
    }
    copyLink();
  }
  async function copyLink() {
    try {
      await navigator.clipboard.writeText(location.href);
      toast(ctx3.t("common.linkCopied"), { type: "success" });
    } catch {
      toast(location.href, { type: "info", duration: 6e3 });
    }
  }

  // src/js/core/catalog.js
  var sorters = {
    newest: (a, b) => b.createdAt > a.createdAt ? 1 : b.createdAt < a.createdAt ? -1 : b.id - a.id,
    popular: (a, b) => b.views - a.views,
    best: (a, b) => b.sold - a.sold,
    rating: (a, b) => b.rating - a.rating || b.reviewCount - a.reviewCount,
    priceAsc: (a, b) => a.price - b.price,
    priceDesc: (a, b) => b.price - a.price,
    discount: (a, b) => b.discount - a.discount
  };
  function filterProducts(products, f = {}) {
    const q = (f.q || "").trim().toLowerCase();
    const terms = q ? q.split(/\s+/) : [];
    return products.filter((p) => {
      var _a, _b, _c;
      if (((_a = f.cats) == null ? void 0 : _a.length) && !f.cats.includes(p.category) && !f.cats.includes(p.subcategory)) return false;
      if (((_b = f.brands) == null ? void 0 : _b.length) && !f.brands.includes(p.brand)) return false;
      if (f.min != null && p.price < f.min) return false;
      if (f.max != null && p.price > f.max) return false;
      if (f.rating && p.rating < f.rating) return false;
      if (((_c = f.colors) == null ? void 0 : _c.length) && !p.colors.some((c) => f.colors.includes(c.hex))) return false;
      if (f.inStock && !p.inStock) return false;
      if (f.discount && !p.discount) return false;
      if (terms.length) {
        const hay = `${p.name} ${p.brandName} ${p.categoryName} ${p.subcategoryName} ${p.tags.join(" ")} ${p.sku}`.toLowerCase();
        if (!terms.every((t) => hay.includes(t))) return false;
      }
      return true;
    });
  }
  function searchProducts(products, q, limit = 6) {
    const query = (q || "").trim().toLowerCase();
    if (!query) return [];
    const scored = products.map((p) => {
      const name = p.name.toLowerCase();
      let score = 0;
      if (name.startsWith(query)) score += 6;
      if (name.includes(query)) score += 4;
      if (p.brandName.toLowerCase().includes(query)) score += 3;
      if (p.categoryName.toLowerCase().includes(query) || p.subcategoryName.toLowerCase().includes(query)) score += 2;
      if (p.tags.some((t) => t.toLowerCase().includes(query))) score += 2;
      return [score, p];
    }).filter(([s]) => s > 0).sort((a, b) => b[0] - a[0] || b[1].sold - a[1].sold);
    return scored.slice(0, limit).map(([, p]) => p);
  }

  // src/js/components/search.js
  var POPULAR = { fa: ["\u0647\u062F\u0641\u0648\u0646", "\u06AF\u0648\u0634\u06CC", "\u06A9\u0641\u0634", "\u0686\u0631\u0645", "\u0633\u0631\u0645", "\u0633\u0627\u0639\u062A"], en: ["headphones", "phone", "sneakers", "leather", "serum", "watch"] };
  function initSearch(ctx4) {
    $$("[data-search]").forEach((box) => {
      const input = $("[data-search-input]", box);
      const panel = $("[data-search-suggest]", box);
      const form = $("form", box);
      if (!input || !panel) return;
      let active = -1;
      const close2 = () => {
        box.classList.remove("is-open");
        input.setAttribute("aria-expanded", "false");
        active = -1;
      };
      const open2 = () => {
        box.classList.add("is-open");
        input.setAttribute("aria-expanded", "true");
      };
      const renderPopular = () => {
        const chips = POPULAR[ctx4.lang] || POPULAR.en;
        panel.innerHTML = `<div class="search__suggest-title">${esc(ctx4.t("header.popularSearches"))}</div><div class="search__suggest-chips">${chips.map((c) => `<a class="chip" href="${ctx4.url("search.html")}?q=${encodeURIComponent(c)}" role="option">${esc(c)}</a>`).join("")}</div>`;
      };
      const render = () => {
        const q2 = input.value.trim();
        if (q2.length < 2) {
          renderPopular();
          open2();
          return;
        }
        const results = searchProducts(ctx4.catalog.products, q2, 6);
        if (!results.length) {
          panel.innerHTML = `<div class="search__suggest-empty">${esc(ctx4.t("header.noSuggestion"))}</div>`;
        } else {
          panel.innerHTML = `<div class="search__suggest-title">${esc(ctx4.t("header.suggestedProducts"))}</div><div class="search__suggest-list">${results.map((p) => suggestItemHTML(ctx4, p)).join("")}</div><a class="search__suggest-item fw-medium" href="${ctx4.url("search.html")}?q=${encodeURIComponent(q2)}" role="option" style="justify-content:center;color:var(--color-primary-hover)">${esc(ctx4.t("shop.searchResultsFor"))} \xAB${esc(q2)}\xBB</a>`;
        }
        open2();
      };
      input.addEventListener("input", debounce(render, 150));
      input.addEventListener("focus", render);
      input.addEventListener("keydown", (e) => {
        const items = $$('[role="option"]', panel);
        if (e.key === "Escape") {
          close2();
          return;
        }
        if (!items.length) return;
        if (e.key === "ArrowDown") {
          e.preventDefault();
          active = (active + 1) % items.length;
          items[active].focus();
        }
      });
      panel.addEventListener("keydown", (e) => {
        var _a;
        const items = $$('[role="option"]', panel);
        const idx = items.indexOf(document.activeElement);
        if (e.key === "ArrowDown") {
          e.preventDefault();
          (_a = items[(idx + 1) % items.length]) == null ? void 0 : _a.focus();
        } else if (e.key === "ArrowUp") {
          e.preventDefault();
          if (idx <= 0) input.focus();
          else items[idx - 1].focus();
        } else if (e.key === "Escape") {
          close2();
          input.focus();
        }
      });
      document.addEventListener("click", (e) => {
        if (!box.contains(e.target)) close2();
      });
      box.addEventListener("focusout", (e) => {
        if (!box.contains(e.relatedTarget)) setTimeout(() => {
          if (!box.contains(document.activeElement)) close2();
        }, 0);
      });
      form == null ? void 0 : form.addEventListener("submit", (e) => {
        if (!input.value.trim()) e.preventDefault();
      });
      const q = new URLSearchParams(location.search).get("q");
      if (q && !input.value) input.value = q;
    });
  }

  // src/js/components/misc.js
  function initCountdowns(ctx4) {
    $$("[data-countdown]").forEach((el2) => {
      const hours = Number(el2.dataset.countdownHours || 24);
      const key = "nx:countdown-end";
      let end = Number(sessionStorage.getItem(key));
      if (!end || end < Date.now()) {
        end = Date.now() + hours * 3600 * 1e3;
        sessionStorage.setItem(key, String(end));
      }
      const d = $('[data-cd="d"]', el2);
      const h = $('[data-cd="h"]', el2);
      const m = $('[data-cd="m"]', el2);
      const s = $('[data-cd="s"]', el2);
      const tick = () => {
        let diff = Math.max(0, end - Date.now());
        const days = Math.floor(diff / 864e5);
        diff -= days * 864e5;
        const hrs = Math.floor(diff / 36e5);
        diff -= hrs * 36e5;
        const mins = Math.floor(diff / 6e4);
        diff -= mins * 6e4;
        const secs = Math.floor(diff / 1e3);
        if (d) d.textContent = ctx4.digits(pad2(days));
        if (h) h.textContent = ctx4.digits(pad2(d ? hrs : hrs + days * 24));
        if (m) m.textContent = ctx4.digits(pad2(mins));
        if (s) s.textContent = ctx4.digits(pad2(secs));
        if (end - Date.now() <= 0) {
          el2.classList.add("is-expired");
          clearInterval(timer);
        }
      };
      tick();
      const timer = setInterval(tick, 1e3);
    });
  }
  function initReveal() {
    const nodes = $$("[data-reveal]");
    if (!nodes.length) return;
    if (prefersReducedMotion() || !("IntersectionObserver" in window)) {
      nodes.forEach((n) => n.classList.add("is-visible"));
      return;
    }
    const io = new IntersectionObserver((entries) => {
      entries.forEach((e) => {
        if (e.isIntersecting) {
          e.target.classList.add("is-visible");
          io.unobserve(e.target);
        }
      });
    }, { rootMargin: "0px 0px -8% 0px", threshold: 0.05 });
    nodes.forEach((n) => io.observe(n));
  }
  function initTabs(root = document) {
    $$("[data-tabs]", root).forEach((tabs) => {
      const list = $('[role="tablist"]', tabs);
      const btns = $$('[role="tab"]', list);
      const panels = btns.map((b) => document.getElementById(b.getAttribute("aria-controls"))).filter(Boolean);
      const activate = (btn, focus = false) => {
        btns.forEach((b) => {
          const sel = b === btn;
          b.setAttribute("aria-selected", String(sel));
          b.tabIndex = sel ? 0 : -1;
        });
        panels.forEach((p) => {
          p.hidden = p.id !== btn.getAttribute("aria-controls");
        });
        if (focus) btn.focus();
        tabs.dispatchEvent(new CustomEvent("tabs:change", { bubbles: true, detail: { tab: btn.dataset.tab } }));
      };
      btns.forEach((b) => {
        b.addEventListener("click", () => activate(b));
        b.addEventListener("keydown", (e) => {
          const i = btns.indexOf(b);
          const rtl = document.documentElement.dir === "rtl";
          const nextKey = rtl ? "ArrowLeft" : "ArrowRight";
          const prevKey = rtl ? "ArrowRight" : "ArrowLeft";
          if (e.key === nextKey) {
            e.preventDefault();
            activate(btns[(i + 1) % btns.length], true);
          } else if (e.key === prevKey) {
            e.preventDefault();
            activate(btns[(i - 1 + btns.length) % btns.length], true);
          } else if (e.key === "Home") {
            e.preventDefault();
            activate(btns[0], true);
          } else if (e.key === "End") {
            e.preventDefault();
            activate(btns[btns.length - 1], true);
          }
        });
      });
      const jump = (name) => {
        const b = btns.find((x) => x.dataset.tab === name);
        if (b) {
          activate(b);
          tabs.scrollIntoView({ behavior: prefersReducedMotion() ? "auto" : "smooth", block: "start" });
        }
      };
      if (location.hash.startsWith("#tab-")) jump(location.hash.slice(5));
      on(document, "click", "[data-tab-jump]", (e, a) => {
        e.preventDefault();
        jump(a.dataset.tabJump);
      });
    });
  }
  function initNewsletter(ctx4) {
    $$("[data-newsletter]").forEach((form) => {
      form.addEventListener("submit", (e) => {
        var _a;
        e.preventDefault();
        const input = $('input[type="email"]', form);
        const err = $(".form-error", form);
        const ok = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(input.value.trim());
        (_a = form.querySelector(".form-group")) == null ? void 0 : _a.classList.toggle("is-invalid", !ok);
        input.setAttribute("aria-invalid", String(!ok));
        if (err) err.textContent = ok ? "" : ctx4.t("footer.newsletterError");
        if (!ok) {
          input.focus();
          return;
        }
        const btn = $('button[type="submit"]', form);
        btn.classList.add("is-loading");
        setTimeout(() => {
          btn.classList.remove("is-loading");
          toast(ctx4.t("footer.newsletterSuccess"), { type: "success" });
          form.reset();
        }, 700);
      });
    });
  }
  var RULES = {
    phone: (v) => /^(\+?\d[\d\s-]{8,14}|0?9\d{9})$/.test(v.replace(/[۰-۹]/g, (d) => "\u06F0\u06F1\u06F2\u06F3\u06F4\u06F5\u06F6\u06F7\u06F8\u06F9".indexOf(d))),
    email: (v) => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v),
    identifier: (v) => RULES.email(v) || RULES.phone(v),
    postal: (v) => /^\d{5}(-?\d{4,5})?$/.test(v.replace(/[۰-۹]/g, (d) => "\u06F0\u06F1\u06F2\u06F3\u06F4\u06F5\u06F6\u06F7\u06F8\u06F9".indexOf(d))),
    password: (v) => v.length >= 8 && /[a-zA-Z]/.test(v) && /\d/.test(v),
    terms: (v, field) => field.checked,
    passwordMatch: (v, field) => {
      var _a;
      return v === (((_a = document.getElementById(field.dataset.match)) == null ? void 0 : _a.value) || "");
    }
  };
  function validateForm(form, ctx4) {
    let firstInvalid = null;
    $$("input, select, textarea", form).forEach((field) => {
      if (field.closest("[hidden]") || field.disabled) return;
      const group = field.closest(".form-group");
      const err = group ? $(".form-error", group) : null;
      const value = (field.value || "").trim();
      let msg = "";
      const required = field.required || field.hasAttribute("data-required");
      if (required && !value && field.type !== "checkbox" && field.type !== "radio") msg = ctx4.t("checkout.errors.required");
      else if (field.type === "checkbox" && field.dataset.validate === "terms" && !field.checked) msg = ctx4.t("checkout.errors.terms");
      else if (value && field.dataset.minlength && value.length < Number(field.dataset.minlength)) msg = ctx4.t("checkout.errors.minLength", { n: ctx4.digits(field.dataset.minlength) });
      else if (value && field.dataset.validate && RULES[field.dataset.validate] && !RULES[field.dataset.validate](value, field)) msg = ctx4.t(`checkout.errors.${field.dataset.validate === "identifier" ? "email" : field.dataset.validate}`);
      if (field.type === "radio" && field.required && !$(`input[name="${field.name}"]:checked`, form)) msg = ctx4.t("checkout.errors.required");
      if (group) group.classList.toggle("is-invalid", !!msg);
      if (field.type !== "radio") field.setAttribute("aria-invalid", msg ? "true" : "false");
      if (err) err.textContent = msg;
      if (msg && !firstInvalid) firstInvalid = field;
    });
    if (firstInvalid) {
      firstInvalid.focus();
      toast(ctx4.t("toast.formError"), { type: "error" });
      return false;
    }
    return true;
  }
  function liveValidation(form, ctx4) {
    form.addEventListener("input", (e) => {
      const field = e.target;
      const group = field.closest(".form-group");
      if (group == null ? void 0 : group.classList.contains("is-invalid")) {
        const value = field.value.trim();
        let ok = true;
        if ((field.required || field.hasAttribute("data-required")) && !value) ok = false;
        else if (field.dataset.minlength && value.length < Number(field.dataset.minlength)) ok = false;
        else if (field.dataset.validate && RULES[field.dataset.validate] && !RULES[field.dataset.validate](value, field)) ok = false;
        if (ok) {
          group.classList.remove("is-invalid");
          field.setAttribute("aria-invalid", "false");
          const err = $(".form-error", group);
          if (err) err.textContent = "";
        }
      }
    });
  }
  function initPasswordTools(ctx4) {
    on(document, "click", "[data-toggle-password]", (e, btn) => {
      const input = btn.parentElement.querySelector("input");
      const show = input.type === "password";
      input.type = show ? "text" : "password";
      btn.setAttribute("aria-pressed", String(show));
      btn.setAttribute("aria-label", ctx4.t(show ? "auth.hidePassword" : "auth.showPassword"));
      btn.innerHTML = `<span class="icon icon--sm linear-icon-${show ? "eye-crossed" : "eye"}" aria-hidden="true"></span>`;
    });
    $$("[data-password-strength]").forEach((meter) => {
      var _a;
      const input = (_a = meter.closest(".form-group")) == null ? void 0 : _a.querySelector('input[type="password"], input[name="password"]');
      const text = $("[data-strength-text]", meter);
      input == null ? void 0 : input.addEventListener("input", () => {
        const v = input.value;
        let score = 0;
        if (v.length >= 8) score++;
        if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
        if (/\d/.test(v)) score++;
        if (/[^A-Za-z0-9]/.test(v) && v.length >= 10) score++;
        if (!v) score = 0;
        meter.dataset.level = String(score);
        if (text) text.textContent = ctx4.t(`auth.strength${score}`);
      });
    });
  }
  function initDemoForms(ctx4) {
    const selectors = ["[data-contact-form]", "[data-review-form]", "[data-comment-form]", "[data-profile-form]", "[data-password-form]", "[data-qa-form]"];
    $$(selectors.join(",")).forEach((form) => {
      liveValidation(form, ctx4);
      form.addEventListener("submit", (e) => {
        e.preventDefault();
        if (!validateForm(form, ctx4)) return;
        const btn = $('button[type="submit"]', form);
        btn == null ? void 0 : btn.classList.add("is-loading");
        setTimeout(() => {
          var _a;
          btn == null ? void 0 : btn.classList.remove("is-loading");
          const key = form.hasAttribute("data-review-form") ? "product.reviewSubmitted" : form.hasAttribute("data-comment-form") ? "blog.commentSubmitted" : "toast.saved";
          toast(ctx4.t(key), { type: "success", duration: 5e3 });
          if (!form.hasAttribute("data-profile-form")) form.reset();
          if (form.hasAttribute("data-review-form")) {
            form.hidden = true;
            (_a = $("[data-review-toggle]")) == null ? void 0 : _a.setAttribute("aria-expanded", "false");
          }
        }, 800);
      });
    });
    on(document, "click", "[data-review-toggle]", (e, btn) => {
      var _a;
      const form = document.getElementById(btn.getAttribute("aria-controls"));
      if (!form) return;
      const open2 = form.hidden;
      form.hidden = !open2;
      btn.setAttribute("aria-expanded", String(open2));
      if (open2) (_a = $("input, textarea", form)) == null ? void 0 : _a.focus();
    });
    on(document, "click", "[data-review-cancel]", (e, btn) => {
      var _a;
      const form = btn.closest("form");
      form.hidden = true;
      (_a = $("[data-review-toggle]")) == null ? void 0 : _a.setAttribute("aria-expanded", "false");
    });
    on(document, "click", "[data-helpful]", (e, btn) => {
      btn.disabled = true;
      btn.style.borderColor = "var(--color-primary)";
      toast(ctx4.t("toast.saved"), { type: "success" });
    });
    on(document, "click", "[data-social-login]", () => toast(ctx4.t("common.loading"), { type: "info" }));
    on(document, "click", "[data-delete-account]", () => {
      if (confirm(ctx4.t("account.deleteAccountText"))) toast(ctx4.t("toast.saved"), { type: "success" });
    });
    on(document, "click", "[data-invoice], [data-cancel-order], [data-return-order], [data-size-guide]", () => toast(ctx4.t("toast.saved"), { type: "info" }));
  }

  // src/js/pages/shop.js
  function initShop(ctx4) {
    var _a, _b, _c;
    const page = $("[data-shop]");
    if (!page) return;
    const isSearch = page.dataset.mode === "search";
    const perPage = Number(page.dataset.perPage || 12);
    const grid = $("[data-shop-grid]", page);
    const results = $("[data-shop-results]", page);
    const emptyBox = $("[data-shop-empty]", page);
    const countEl = $("[data-shop-count]", page);
    const pagEl = $("[data-shop-pagination]", page);
    const activeEl = $("[data-active-filters]", page);
    const filtersForm = $("[data-filters]", page);
    const filtersHost = $("[data-filters-host]", page);
    const drawerBody = $("[data-filters-drawer-body]");
    const catalog2 = ctx4.catalog;
    const products = catalog2.products;
    const priceMin = catalog2.priceMin;
    const priceMax = catalog2.priceMax;
    const state2 = { q: "", cats: [], brands: [], min: null, max: null, rating: 0, colors: [], inStock: false, discount: false, sort: "popular", page: 1, view: "grid" };
    try {
      state2.view = localStorage.getItem("nx:view") || "grid";
    } catch {
    }
    function readURL() {
      const sp = new URLSearchParams(location.search);
      state2.q = sp.get("q") || "";
      state2.cats = sp.getAll("cat").flatMap((v) => v.split(",")).filter(Boolean);
      state2.brands = sp.getAll("brand").flatMap((v) => v.split(",")).filter(Boolean);
      state2.min = sp.get("min") ? Number(sp.get("min")) : null;
      state2.max = sp.get("max") ? Number(sp.get("max")) : null;
      state2.rating = Number(sp.get("rating")) || 0;
      state2.colors = sp.getAll("color").flatMap((v) => v.split(",")).filter(Boolean);
      state2.inStock = sp.get("instock") === "1";
      state2.discount = sp.get("discount") === "1";
      state2.sort = sp.get("sort") || (isSearch ? "popular" : "popular");
      state2.page = Number(sp.get("page")) || 1;
    }
    function writeURL(replace = false) {
      const sp = new URLSearchParams();
      if (state2.q) sp.set("q", state2.q);
      if (state2.cats.length) sp.set("cat", state2.cats.join(","));
      if (state2.brands.length) sp.set("brand", state2.brands.join(","));
      if (state2.min != null && state2.min > priceMin) sp.set("min", state2.min);
      if (state2.max != null && state2.max < priceMax) sp.set("max", state2.max);
      if (state2.rating) sp.set("rating", state2.rating);
      if (state2.colors.length) sp.set("color", state2.colors.join(","));
      if (state2.inStock) sp.set("instock", "1");
      if (state2.discount) sp.set("discount", "1");
      if (state2.sort !== "popular") sp.set("sort", state2.sort);
      if (state2.page > 1) sp.set("page", state2.page);
      const url = `${location.pathname}${sp.toString() ? `?${sp}` : ""}`;
      history[replace ? "replaceState" : "pushState"](null, "", url);
    }
    function syncForm() {
      var _a2, _b2;
      if (!filtersForm) return;
      $$('input[name="cat"]', filtersForm).forEach((i) => {
        i.checked = state2.cats.includes(i.value);
      });
      $$('input[name="brand"]', filtersForm).forEach((i) => {
        i.checked = state2.brands.includes(i.value);
      });
      $$('input[name="color"]', filtersForm).forEach((i) => {
        i.checked = state2.colors.includes(i.value);
      });
      $$('input[name="rating"]', filtersForm).forEach((i) => {
        i.checked = Number(i.value || 0) === state2.rating;
      });
      const inStock = $('input[name="instock"]', filtersForm);
      if (inStock) inStock.checked = state2.inStock;
      const disc = $('input[name="discount"]', filtersForm);
      if (disc) disc.checked = state2.discount;
      const rMin = $("[data-range-min]", filtersForm);
      const rMax = $("[data-range-max]", filtersForm);
      if (rMin && rMax) {
        rMin.value = (_a2 = state2.min) != null ? _a2 : priceMin;
        rMax.value = (_b2 = state2.max) != null ? _b2 : priceMax;
        updateRange();
      }
      $$("[data-sort]", page).forEach((b) => b.classList.toggle("is-active", b.dataset.sort === state2.sort));
      const sel = $("[data-sort-select]", page);
      if (sel) sel.value = state2.sort;
      $$("[data-view]", page).forEach((b) => b.setAttribute("aria-pressed", String(b.dataset.view === state2.view)));
      $$("[data-cat-chip]", page).forEach((c) => c.classList.toggle("is-active", state2.cats.length === 1 ? c.dataset.catChip === state2.cats[0] : c.dataset.catChip === "" && !state2.cats.length));
    }
    function updateRange() {
      const rMin = $("[data-range-min]", filtersForm);
      const rMax = $("[data-range-max]", filtersForm);
      if (!rMin) return;
      let a = Number(rMin.value);
      let b = Number(rMax.value);
      if (a > b) {
        [a, b] = [b, a];
        rMin.value = a;
        rMax.value = b;
      }
      const pct = (v) => (v - priceMin) / (priceMax - priceMin || 1) * 100;
      const fill = $("[data-range-fill]", filtersForm);
      if (fill) {
        fill.style.insetInlineStart = `${pct(a)}%`;
        fill.style.insetInlineEnd = `${100 - pct(b)}%`;
      }
      $("[data-range-min-label]", filtersForm).textContent = ctx4.money(a);
      $("[data-range-max-label]", filtersForm).textContent = ctx4.money(b);
      const iMin = $('[data-price-input="min"]', filtersForm);
      const iMax = $('[data-price-input="max"]', filtersForm);
      if (iMin && document.activeElement !== iMin) iMin.value = ctx4.price(a, { symbol: false });
      if (iMax && document.activeElement !== iMax) iMax.value = ctx4.price(b, { symbol: false });
    }
    let renderTimer;
    function render({ scroll = false } = {}) {
      results.classList.add("is-loading");
      results.setAttribute("aria-busy", "true");
      clearTimeout(renderTimer);
      renderTimer = setTimeout(() => {
        const filtered = filterProducts(products, state2).sort(sorters[state2.sort] || sorters.popular);
        const total = filtered.length;
        const pages = Math.max(1, Math.ceil(total / perPage));
        if (state2.page > pages) state2.page = pages;
        const from = (state2.page - 1) * perPage;
        const slice = filtered.slice(from, from + perPage);
        grid.classList.toggle("product-grid--list", state2.view === "list");
        grid.innerHTML = slice.map((p, i) => productCardHTML(ctx4, p, { view: state2.view, priority: i < 4 && state2.page === 1 })).join("");
        syncPressedStates(grid);
        grid.hidden = total === 0;
        if (emptyBox) {
          emptyBox.hidden = total > 0;
          if (isSearch && !state2.q && !hasFilters()) {
            const h = $(".empty__title", emptyBox);
            const p = $(".empty__text", emptyBox);
            if (h) h.textContent = ctx4.t("search.noQuery");
            if (p) p.textContent = ctx4.t("search.noQueryText");
          } else if (isSearch) {
            const h = $(".empty__title", emptyBox);
            const p = $(".empty__text", emptyBox);
            if (h) h.textContent = ctx4.t("search.noResults", { q: state2.q });
            if (p) p.textContent = ctx4.t("search.noResultsText");
          }
        }
        if (countEl) countEl.innerHTML = total ? ctx4.t("common.showing", { from: `<strong>${ctx4.digits(from + 1)}</strong>`, to: `<strong>${ctx4.digits(Math.min(from + perPage, total))}</strong>`, total: `<strong>${ctx4.digits(total)}</strong>` }) : ctx4.t("shop.results", { n: ctx4.digits(0) });
        if (pagEl) pagEl.innerHTML = paginationHTML(ctx4, { page: state2.page, pages, hrefFor: (p) => {
          const sp = new URLSearchParams(location.search);
          sp.set("page", p);
          return `?${sp}`;
        } });
        renderActive();
        renderTitles(total);
        results.classList.remove("is-loading");
        results.setAttribute("aria-busy", "false");
        if (scroll) scrollToEl(page.querySelector(".toolbar") || page, 120);
      }, 120);
    }
    function hasFilters() {
      return state2.cats.length || state2.brands.length || state2.colors.length || state2.rating || state2.inStock || state2.discount || state2.min != null && state2.min > priceMin || state2.max != null && state2.max < priceMax;
    }
    function renderActive() {
      var _a2, _b2;
      if (!activeEl) return;
      const chips = [];
      const chip = (label, key, value) => `<button type="button" class="chip is-active" data-remove-filter="${key}" data-value="${esc(value)}">${esc(label)}<span class="chip__remove icon icon--xs linear-icon-cross" aria-hidden="true"></span><span class="visually-hidden">${esc(ctx4.t("common.remove"))}</span></button>`;
      const catName = (slug) => {
        for (const c of catalog2.categories) {
          if (c.slug === slug) return c.name;
          const ch = c.children.find((x) => x.slug === slug);
          if (ch) return ch.name;
        }
        return slug;
      };
      state2.cats.forEach((c) => chips.push(chip(catName(c), "cat", c)));
      state2.brands.forEach((b) => {
        var _a3;
        return chips.push(chip(((_a3 = catalog2.brands.find((x) => x.slug === b)) == null ? void 0 : _a3.name) || b, "brand", b));
      });
      if (state2.min != null && state2.min > priceMin || state2.max != null && state2.max < priceMax) chips.push(chip(`${ctx4.money((_a2 = state2.min) != null ? _a2 : priceMin)} \u2013 ${ctx4.money((_b2 = state2.max) != null ? _b2 : priceMax)}`, "price", ""));
      if (state2.rating) chips.push(chip(ctx4.t("shop.ratingLabel", { n: ctx4.digits(state2.rating) }), "rating", ""));
      state2.colors.forEach((c) => chips.push(`<button type="button" class="chip is-active" data-remove-filter="color" data-value="${esc(c)}"><span class="product-card__swatch" style="background:${esc(c)}"></span>${esc(ctx4.t("common.color"))}<span class="chip__remove icon icon--xs linear-icon-cross" aria-hidden="true"></span></button>`));
      if (state2.inStock) chips.push(chip(ctx4.t("shop.onlyInStock"), "instock", ""));
      if (state2.discount) chips.push(chip(ctx4.t("shop.onlyDiscount"), "discount", ""));
      activeEl.innerHTML = chips.length ? `<span class="small text-muted">${esc(ctx4.t("shop.activeFilters"))}</span>${chips.join("")}<button type="button" class="btn btn--link btn--sm" data-filters-clear>${esc(ctx4.t("common.clearAll"))}</button>` : "";
      const n = chips.length;
      $$("[data-filters-count]").forEach((b) => {
        b.textContent = ctx4.digits(n);
        b.hidden = n === 0;
      });
    }
    function renderTitles(total) {
      var _a2;
      const title = $("[data-shop-title]");
      const sub = $("[data-shop-subtitle]");
      const q = $("[data-search-query]");
      if (title && state2.cats.length === 1) {
        const c = catalog2.categories.find((x) => x.slug === state2.cats[0]) || catalog2.categories.flatMap((x) => x.children).find((x) => x.slug === state2.cats[0]);
        if (c) {
          title.textContent = c.name;
          if (sub) sub.textContent = ctx4.t("shop.results", { n: ctx4.digits(total) });
          document.title = `${c.name}${((_a2 = ctx4.L.meta) == null ? void 0 : _a2.titleSuffix) || ""}`;
        }
      } else if (title && state2.brands.length === 1) {
        const b = catalog2.brands.find((x) => x.slug === state2.brands[0]);
        if (b) {
          title.textContent = b.name;
          if (sub) sub.textContent = ctx4.t("shop.results", { n: ctx4.digits(total) });
        }
      } else if (title) {
        title.textContent = ctx4.t("shop.bannerTitle");
        if (sub) sub.textContent = ctx4.t("shop.bannerText");
      }
      if (q) q.innerHTML = state2.q ? `${esc(ctx4.t("search.resultsFor", { q: state2.q }))} \u2014 <strong>${esc(ctx4.t("search.count", { n: ctx4.digits(total) }))}</strong>` : "";
      const popular = $("[data-search-popular]");
      if (popular) popular.hidden = !!state2.q;
    }
    function apply({ resetPage = true, scroll = false } = {}) {
      if (resetPage) state2.page = 1;
      writeURL();
      render({ scroll });
    }
    if (filtersForm) {
      filtersForm.addEventListener("change", (e) => {
        const f = e.target;
        if (f.name === "cat") state2.cats = $$('input[name="cat"]:checked', filtersForm).map((i) => i.value);
        else if (f.name === "brand") state2.brands = $$('input[name="brand"]:checked', filtersForm).map((i) => i.value);
        else if (f.name === "color") state2.colors = $$('input[name="color"]:checked', filtersForm).map((i) => i.value);
        else if (f.name === "rating") state2.rating = Number(f.value) || 0;
        else if (f.name === "instock") state2.inStock = f.checked;
        else if (f.name === "discount") state2.discount = f.checked;
        else if (f.name === "min" || f.name === "max") {
          updateRange();
          state2.min = Number($("[data-range-min]", filtersForm).value);
          state2.max = Number($("[data-range-max]", filtersForm).value);
        } else return;
        apply();
      });
      filtersForm.addEventListener("input", (e) => {
        if (e.target.name === "min" || e.target.name === "max") updateRange();
      });
      filtersForm.addEventListener("submit", (e) => e.preventDefault());
      $$("[data-price-input]", filtersForm).forEach((inp) => {
        inp.addEventListener("change", () => {
          const raw = Number(String(inp.value).replace(/[۰-۹]/g, (d) => "\u06F0\u06F1\u06F2\u06F3\u06F4\u06F5\u06F6\u06F7\u06F8\u06F9".indexOf(d)).replace(/[^\d.]/g, ""));
          if (!raw) return;
          const base = ctx4.currency.rate && ctx4.currency.rate !== 1 ? raw / ctx4.currency.rate : raw;
          const v = Math.max(priceMin, Math.min(priceMax, Math.round(base)));
          if (inp.dataset.priceInput === "min") {
            state2.min = v;
            $("[data-range-min]", filtersForm).value = v;
          } else {
            state2.max = v;
            $("[data-range-max]", filtersForm).value = v;
          }
          updateRange();
          apply();
        });
      });
      (_a = $("[data-brand-search]", filtersForm)) == null ? void 0 : _a.addEventListener("input", debounce((e) => {
        const q = e.target.value.trim().toLowerCase();
        $$("[data-brand-item]", filtersForm).forEach((l) => {
          l.hidden = q && !l.dataset.brandItem.toLowerCase().includes(q);
        });
      }, 100));
    }
    on(document, "click", "[data-filters-clear]", (e) => {
      e.preventDefault();
      Object.assign(state2, { cats: [], brands: [], min: null, max: null, rating: 0, colors: [], inStock: false, discount: false });
      syncForm();
      apply();
    });
    on(page, "click", "[data-remove-filter]", (e, btn) => {
      const k = btn.dataset.removeFilter;
      const v = btn.dataset.value;
      if (k === "cat") state2.cats = state2.cats.filter((x) => x !== v);
      else if (k === "brand") state2.brands = state2.brands.filter((x) => x !== v);
      else if (k === "color") state2.colors = state2.colors.filter((x) => x !== v);
      else if (k === "price") {
        state2.min = null;
        state2.max = null;
      } else if (k === "rating") state2.rating = 0;
      else if (k === "instock") state2.inStock = false;
      else if (k === "discount") state2.discount = false;
      syncForm();
      apply();
    });
    on(page, "click", "[data-sort]", (e, btn) => {
      state2.sort = btn.dataset.sort;
      syncForm();
      apply();
    });
    (_b = $("[data-sort-select]", page)) == null ? void 0 : _b.addEventListener("change", (e) => {
      state2.sort = e.target.value;
      syncForm();
      apply();
    });
    on(page, "click", "[data-view]", (e, btn) => {
      state2.view = btn.dataset.view;
      try {
        localStorage.setItem("nx:view", state2.view);
      } catch {
      }
      syncForm();
      render();
    });
    on(page, "click", "[data-cat-chip]", (e, a) => {
      e.preventDefault();
      state2.cats = a.dataset.catChip ? [a.dataset.catChip] : [];
      syncForm();
      apply();
    });
    on(page, "click", ".pagination__link[data-page]", (e, a) => {
      e.preventDefault();
      if (a.classList.contains("is-disabled")) return;
      state2.page = Number(a.dataset.page);
      writeURL();
      render({ scroll: true });
    });
    on(page, "click", ".empty .btn", (e, a) => {
      var _a2;
      if (a.getAttribute("href") === "#") {
        e.preventDefault();
        (_a2 = $("[data-filters-clear]")) == null ? void 0 : _a2.click();
      }
    });
    const searchForm = $("[data-search-page] form", page);
    searchForm == null ? void 0 : searchForm.addEventListener("submit", (e) => {
      e.preventDefault();
      state2.q = $("[data-search-input]", searchForm).value.trim();
      apply();
    });
    window.addEventListener("popstate", () => {
      readURL();
      syncForm();
      render();
    });
    const mql = mq("(max-width: 991.98px)");
    const relocate = () => {
      if (!filtersForm || !drawerBody || !filtersHost) return;
      if (mql.matches) {
        if (filtersForm.parentElement !== drawerBody) drawerBody.append(filtersForm);
      } else if (filtersForm.parentElement !== filtersHost) filtersHost.append(filtersForm);
    };
    mql.addEventListener("change", relocate);
    relocate();
    (_c = document.getElementById("drawer-filters")) == null ? void 0 : _c.addEventListener("drawer:open", () => {
      $("[data-filters-apply-label]").textContent = ctx4.t("shop.applyFilters");
    });
    readURL();
    syncForm();
    render();
  }

  // src/js/pages/product.js
  function initProduct(ctx4) {
    const dyn = $("[data-product-dynamic]");
    if (dyn) renderDynamic(ctx4, dyn);
    const page = $("[data-product-page]:not(.quick-view)");
    if (!page) return;
    const id = Number(page.dataset.id);
    recentPush(id, ctx4.config.recentLimit || 8);
    initGallery(ctx4, page);
    initStickyBuy(page);
    renderRecent(ctx4, id);
    syncPressedStates(document);
  }
  function renderDynamic(ctx4, host) {
    var _a, _b;
    const sp = new URLSearchParams(location.search);
    const slug = sp.get("slug");
    const id = Number(sp.get("id"));
    const product = slug ? ctx4.bySlug.get(slug) : ctx4.byId.get(id);
    const loading = $("[data-product-loading]", host);
    const notFound = $("[data-product-notfound]", host);
    const target = $("[data-product-target]", host);
    loading.hidden = true;
    if (!product) {
      notFound.hidden = false;
      return;
    }
    const reviews = ((_a = ctx4.catalog.reviews) == null ? void 0 : _a[String(product.id)]) || ctx4.catalog.genericReviews || [];
    const related = ctx4.catalog.products.filter((p) => p.category === product.category && p.id !== product.id).slice(0, 8);
    const full = { ...product, description: product.description || product.short, specs: product.specs || [] };
    target.innerHTML = productPageHTML(ctx4, full, reviews, { related });
    document.title = `${product.name}${((_b = ctx4.L.meta) == null ? void 0 : _b.titleSuffix) || ""}`;
    const crumb = $(".breadcrumb__item[aria-current] span");
    if (crumb) crumb.textContent = product.name;
    initSwipers(ctx4, target);
    initTabs(target);
    initDemoForms(ctx4);
  }
  function initGallery(ctx4, page) {
    const gallery = $("[data-gallery]", page);
    if (!gallery || typeof window.Swiper !== "function") return;
    const thumbsEl = $("[data-gallery-thumbs]", gallery);
    const mainEl = $("[data-gallery-main]", gallery);
    const vertical = window.matchMedia("(min-width: 576px)").matches;
    const thumbs = new window.Swiper(thumbsEl, {
      direction: vertical ? "vertical" : "horizontal",
      slidesPerView: vertical ? 5 : 4.5,
      spaceBetween: 8,
      watchSlidesProgress: true,
      freeMode: true,
      rtl: ctx4.dir === "rtl",
      a11y: { enabled: true }
    });
    const main = new window.Swiper(mainEl, {
      slidesPerView: 1,
      spaceBetween: 0,
      speed: 350,
      rtl: ctx4.dir === "rtl",
      thumbs: { swiper: thumbs },
      keyboard: { enabled: true, onlyInViewport: true },
      navigation: { prevEl: $("[data-gallery-prev]", mainEl), nextEl: $("[data-gallery-next]", mainEl) },
      a11y: { enabled: true }
    });
    $$(".swiper-slide", thumbsEl).forEach((s, i) => s.addEventListener("keydown", (e) => {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        main.slideTo(i);
      }
    }));
    if (window.PhotoSwipeLightbox && window.PhotoSwipe) {
      const lightbox = new window.PhotoSwipeLightbox({
        gallery: mainEl,
        children: "a[data-pswp-width]",
        pswpModule: window.PhotoSwipe,
        showHideAnimationType: "zoom",
        closeTitle: ctx4.t("common.close"),
        zoomTitle: ctx4.t("product.zoom"),
        arrowPrevTitle: ctx4.t("product.prevImage"),
        arrowNextTitle: ctx4.t("product.nextImage"),
        errorMsg: ctx4.t("common.error")
      });
      lightbox.on("change", () => {
        var _a;
        const i = (_a = lightbox.pswp) == null ? void 0 : _a.currIndex;
        if (i != null) main.slideTo(i, 0);
      });
      lightbox.init();
    } else {
    }
  }
  function initStickyBuy(page) {
    const bar = $("[data-sticky-buy]");
    const anchor = $(".buy-box", page);
    if (!bar || !anchor || !("IntersectionObserver" in window)) return;
    const io = new IntersectionObserver(([e]) => {
      const show = !e.isIntersecting && e.boundingClientRect.top < 0;
      bar.classList.toggle("is-visible", show);
      document.body.classList.toggle("has-sticky-buy", show);
    }, { threshold: 0 });
    io.observe(anchor);
  }
  function renderRecent(ctx4, currentId) {
    const section = $("[data-recent-section]");
    const list = $("[data-recent-list]");
    if (!section || !list) return;
    const ids = getState().recent.filter((id) => id !== currentId);
    const items = ids.map((id) => ctx4.product(id)).filter(Boolean);
    if (items.length < 2) return;
    list.innerHTML = items.map((p) => `<div class="swiper-slide">${productCardHTML(ctx4, p)}</div>`).join("");
    section.hidden = false;
    initSwipers(ctx4, section);
    syncPressedStates(section);
  }

  // src/js/pages/cart.js
  function renderSummary(ctx4, root, { shippingKey = null, shippingRates = {} } = {}) {
    var _a;
    const t = cartTotals();
    const set = (sel, val) => {
      const el2 = $(sel, root);
      if (el2) el2.textContent = val;
    };
    const rate = shippingKey ? (_a = shippingRates[shippingKey]) != null ? _a : 0 : 0;
    const shipping = t.afterCoupon >= t.freeShipping ? 0 : shippingKey ? rate : null;
    const tax = Math.round(t.afterCoupon * t.taxRate);
    const total = t.afterCoupon + (shipping || 0) + tax;
    set("[data-summary-count]", `${ctx4.digits(t.count)} ${ctx4.t("header.items")}`);
    set("[data-summary-subtotal]", ctx4.money(t.subtotalOld));
    const dRow = $("[data-summary-discount-row]", root);
    if (dRow) {
      dRow.hidden = t.discount <= 0;
      set("[data-summary-discount]", `\u2212 ${ctx4.money(t.discount)}`);
    }
    const cRow = $("[data-summary-coupon-row]", root);
    if (cRow) {
      cRow.hidden = !t.coupon;
      set("[data-summary-coupon-code]", t.coupon || "");
      set("[data-summary-coupon]", `\u2212 ${ctx4.money(t.couponValue)}`);
    }
    set("[data-summary-shipping]", shipping === 0 ? ctx4.t("cart.shippingFree") : shipping == null ? ctx4.t("cart.shippingCalc") : ctx4.money(shipping));
    set("[data-summary-tax]", ctx4.money(tax));
    set("[data-summary-total]", ctx4.money(total));
    const prog = $("[data-shipping-progress]", root);
    if (prog) {
      const pct = t.freeShipping ? Math.min(100, Math.round(t.afterCoupon / t.freeShipping * 100)) : 100;
      $("[data-shipping-progress-fill]", prog).style.inlineSize = `${pct}%`;
      $("[data-shipping-progress-text]", prog).textContent = t.freeShippingLeft > 0 ? ctx4.t("cart.freeShippingLeft", { n: ctx4.money(t.freeShippingLeft) }) : ctx4.t("cart.freeShippingUnlocked");
    }
    return { ...t, shipping, tax, total };
  }
  function initCartPage(ctx4) {
    var _a;
    const page = $("[data-cart-page]");
    if (!page) return;
    const itemsEl = $("[data-cart-items]", page);
    const filled = $("[data-cart-filled]", page);
    const empty = $("[data-cart-empty]", page);
    const clearBtn = $("[data-cart-clear]", page);
    function render() {
      const t = renderSummary(ctx4, page);
      filled.hidden = t.count === 0;
      empty.hidden = t.count > 0;
      clearBtn.hidden = t.count === 0;
      $("[data-cart-count-label]", page).textContent = t.count ? `(${ctx4.t("cart.count", { n: ctx4.digits(t.count) })})` : "";
      itemsEl.innerHTML = t.lines.map((l) => cartItemHTML(ctx4, l)).join("");
      const sav = $("[data-cart-savings]", page);
      if (sav) {
        sav.hidden = t.discount + t.couponValue <= 0;
        $("[data-cart-savings-text]", sav).textContent = ctx4.t("cart.savings", { n: ctx4.money(t.discount + t.couponValue) });
      }
      const applied = $("[data-coupon-applied]", page);
      if (applied) applied.innerHTML = t.coupon ? `<span class="coupon__applied"><span class="icon icon--xs linear-icon-checkmark-circle" aria-hidden="true"></span>${t.coupon}</span>` : "";
    }
    render();
    subscribe(() => render());
    clearBtn.addEventListener("click", () => {
      if (confirm(ctx4.t("cart.clearConfirm"))) {
        cartClear();
        toast(ctx4.t("toast.cartCleared"));
      }
    });
    (_a = $("[data-coupon-form]", page)) == null ? void 0 : _a.addEventListener("submit", (e) => {
      e.preventDefault();
      const input = $("input", e.target);
      const code = input.value.trim().toUpperCase();
      const coupons = ctx4.config.coupons || {};
      if (!code) return;
      if (coupons[code]) {
        setCoupon(code);
        toast(ctx4.t("toast.couponApplied"), { type: "success" });
        input.value = "";
      } else {
        toast(ctx4.t("toast.couponInvalid"), { type: "error" });
        input.setAttribute("aria-invalid", "true");
        input.focus();
      }
    });
    on(page, "click", "[data-coupon-remove]", () => {
      setCoupon(null);
      toast(ctx4.t("toast.couponRemoved"));
    });
  }
  function initCheckoutPage(ctx4) {
    const page = $("[data-checkout-page]");
    if (!page) return;
    const form = $("[data-checkout-form]", page);
    const filled = $("[data-checkout-filled]", page);
    const empty = $("[data-checkout-empty]", page);
    const success = $("[data-checkout-success]", page);
    const rates = ctx4.config.shippingRates || {};
    const shippingKey = () => {
      var _a;
      return ((_a = $('input[name="shipping"]:checked', form)) == null ? void 0 : _a.value) || "post";
    };
    Object.entries(rates).forEach(([k, v]) => {
      const el2 = $(`[data-ship-price="${k}"]`, page);
      if (el2) el2.textContent = ctx4.money(v);
    });
    function render() {
      const t = renderSummary(ctx4, page, { shippingKey: shippingKey(), shippingRates: rates });
      const isEmpty = t.count === 0 && success.hidden;
      filled.hidden = isEmpty;
      empty.hidden = !isEmpty;
      $("[data-checkout-items]", page).innerHTML = t.lines.map((l) => summaryItemHTML(ctx4, l)).join("");
      if (t.afterCoupon >= t.freeShipping) $$("[data-ship-price]", page).forEach((el2) => {
        if (el2.dataset.shipPrice !== "courier") {
          el2.textContent = ctx4.t("common.free");
          el2.classList.add("option-card__price--free");
        }
      });
      return t;
    }
    render();
    subscribe(() => {
      if (success.hidden) render();
    });
    const newAddr = $("[data-new-address]", form);
    $$('input[name="addressMode"]', form).forEach((r) => r.addEventListener("change", () => {
      var _a;
      newAddr.hidden = r.value !== "new" || !r.checked;
      if (!newAddr.hidden) (_a = $("input", newAddr)) == null ? void 0 : _a.focus();
    }));
    $$('input[name="shipping"]', form).forEach((r) => r.addEventListener("change", render));
    liveValidation(form, ctx4);
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      if (!validateForm(form, ctx4)) return;
      const btn = $("[data-place-order]", form);
      btn.classList.add("is-loading");
      btn.disabled = true;
      const label = btn.textContent;
      setTimeout(() => {
        const code = `NX-${Date.now().toString().slice(-6)}`;
        $("[data-order-code]", page).textContent = code;
        success.hidden = false;
        filled.hidden = true;
        $$(".steps__item", page).forEach((s) => {
          s.classList.add("is-done");
          s.classList.remove("is-active");
        });
        $$(".steps__line", page).forEach((s) => s.classList.add("is-done"));
        cartClear();
        window.scrollTo({ top: 0, behavior: "smooth" });
        btn.classList.remove("is-loading");
        btn.disabled = false;
        btn.textContent = label;
      }, 1400);
    });
  }

  // src/js/pages/lists.js
  function initWishlistPage(ctx4) {
    var _a, _b;
    const page = $("[data-wishlist-page]");
    if (!page) return;
    const items = $("[data-wishlist-items]", page);
    const filled = $("[data-wishlist-filled]", page);
    const empty = $("[data-wishlist-empty]", page);
    const actions = $("[data-wishlist-actions]", page);
    const count = $("[data-wishlist-count]", page);
    function render() {
      const list = getState().wishlist.map((id) => ctx4.product(id)).filter(Boolean);
      filled.hidden = !list.length;
      empty.hidden = !!list.length;
      if (actions) actions.hidden = !list.length;
      if (count) count.textContent = list.length ? `(${ctx4.t("wishlist.count", { n: ctx4.digits(list.length) })})` : "";
      items.innerHTML = list.map((p) => wishItemHTML(ctx4, p)).join("");
    }
    render();
    subscribe(({ type }) => {
      if (type === "wishlist" || type === "sync") render();
    });
    (_a = $("[data-wishlist-clear]", page)) == null ? void 0 : _a.addEventListener("click", () => {
      if (confirm(ctx4.t("cart.clearConfirm"))) wishlistClear();
    });
    (_b = $("[data-wishlist-add-all]", page)) == null ? void 0 : _b.addEventListener("click", (e) => {
      const list = getState().wishlist.map((id) => ctx4.product(id)).filter((p) => p && p.inStock);
      list.forEach((p) => {
        var _a2, _b2, _c, _d;
        return cartAdd({ id: p.id, qty: 1, color: ((_b2 = (_a2 = p.colors) == null ? void 0 : _a2[0]) == null ? void 0 : _b2.name) || "", colorHex: ((_d = (_c = p.colors) == null ? void 0 : _c[0]) == null ? void 0 : _d.hex) || "", max: p.maxQty });
      });
      toast(ctx4.t("toast.addedToCart", { name: `${ctx4.digits(list.length)} ${ctx4.t("common.product")}` }), { type: "success", action: ctx4.t("toast.viewCart"), actionHref: ctx4.url("cart.html") });
    });
  }
  function initComparePage(ctx4) {
    const page = $("[data-compare-page]");
    if (!page) return;
    const table = $("[data-compare-table]", page);
    const filled = $("[data-compare-filled]", page);
    const empty = $("[data-compare-empty]", page);
    const clear = $("[data-compare-clear-page]", page);
    const t = ctx4.t;
    function render() {
      const list = getState().compare.map((id) => ctx4.product(id)).filter(Boolean);
      filled.hidden = !list.length;
      empty.hidden = !!list.length;
      clear.hidden = !list.length;
      if (!list.length) return;
      const row = (label, cells) => `<tr><th scope="row">${esc(label)}</th>${cells.map((c) => `<td>${c}</td>`).join("")}</tr>`;
      const specKeys = [];
      list.forEach((p) => (p.highlights || []).forEach((h, i) => {
        if (!specKeys[i]) specKeys[i] = true;
      }));
      table.innerHTML = `
      <tbody>
        ${row(t("compare.image"), list.map((p) => `<div style="position:relative"><a href="${ctx4.productUrl(p)}"><img src="${ctx4.img(p.image)}" width="200" height="200" alt="${esc(p.name)}"></a><button type="button" class="icon-btn icon-btn--light icon-btn--circle" data-action="compare" data-id="${p.id}" aria-label="${esc(t("common.removeFromCompare"))}" style="position:absolute;inset-block-start:4px;inset-inline-end:4px">${icon("cross", "xs")}</button></div>`))}
        ${row(t("compare.name"), list.map((p) => `<a class="fw-medium text-strong" href="${ctx4.productUrl(p)}">${esc(p.name)}</a>`))}
        ${row(t("compare.price"), list.map((p) => priceHTML(ctx4, p, { showDiscount: true })))}
        ${row(t("compare.rating"), list.map((p) => ratingHTML(ctx4, p.rating, p.reviewCount)))}
        ${row(t("compare.brand"), list.map((p) => `<span class="ltr">${esc(p.brandName)}</span>`))}
        ${row(t("common.category"), list.map((p) => esc(p.categoryName)))}
        ${row(t("compare.availability"), list.map((p) => p.inStock ? `<span class="status status--success">${esc(t("common.inStock"))}</span>` : `<span class="status status--danger">${esc(t("common.outOfStock"))}</span>`))}
        ${row(t("compare.colors"), list.map((p) => p.colors.length ? `<div class="cluster" style="gap:6px">${p.colors.map((c) => `<span class="swatch" style="inline-size:22px;block-size:22px;background:${esc(c.hex)}" title="${esc(c.name)}"></span>`).join("")}</div>` : "\u2014"))}
        ${row(t("product.highlights"), list.map((p) => `<ul class="review__pros review__pros--plus" style="margin:0">${(p.highlights || []).map((h) => `<li>${icon("check")} ${esc(h)}</li>`).join("")}</ul>`))}
        ${row(t("compare.actions"), list.map((p) => `<button type="button" class="btn btn--dark btn--sm btn--block" data-action="add-to-cart" data-id="${p.id}"${p.inStock ? "" : " disabled"}>${icon("cart-add", "xs")}${esc(t("common.addToCart"))}</button>`))}
      </tbody>`;
    }
    render();
    subscribe(({ type }) => {
      if (type === "compare" || type === "sync") render();
    });
    clear.addEventListener("click", () => compareClear());
  }
  function initBlogPage(ctx4) {
    var _a, _b;
    const page = $("[data-blog-page]");
    if (!page) return;
    const posts = $$("[data-post]", page);
    const empty = $("[data-blog-empty]", page);
    const sp = new URLSearchParams(location.search);
    const state2 = { cat: sp.get("cat") || "", tag: sp.get("tag") || "", q: "" };
    const apply = () => {
      let n = 0;
      posts.forEach((p) => {
        const ok = (!state2.cat || p.dataset.cat === state2.cat) && (!state2.tag || p.dataset.tags.split(",").includes(state2.tag)) && (!state2.q || p.dataset.title.toLowerCase().includes(state2.q));
        p.hidden = !ok;
        if (ok) n++;
      });
      if (empty) empty.hidden = n > 0;
      $$("[data-blog-cat]", page).forEach((b) => b.setAttribute("aria-selected", String(b.dataset.blogCat === state2.cat)));
    };
    on(page, "click", "[data-blog-cat]", (e, b) => {
      state2.cat = b.dataset.blogCat;
      state2.tag = "";
      apply();
    });
    (_a = $("[data-blog-search]", page)) == null ? void 0 : _a.addEventListener("input", (e) => {
      state2.q = e.target.value.trim().toLowerCase();
      apply();
    });
    (_b = $("[data-blog-search]", page)) == null ? void 0 : _b.addEventListener("submit", (e) => e.preventDefault());
    apply();
  }
  function initAccountPages(ctx4) {
    const filter = $("[data-order-filter]");
    if (filter) {
      on(filter, "click", "[data-order-status]", (e, b) => {
        const s = b.dataset.orderStatus;
        $$("[data-order-status]", filter).forEach((x) => x.setAttribute("aria-selected", String(x === b)));
        let n = 0;
        $$("[data-order-row]").forEach((r) => {
          const ok = !s || r.dataset.orderRow === s;
          r.hidden = !ok;
          if (ok) n++;
        });
        const empty = $("[data-orders-empty]");
        if (empty) empty.hidden = n > 0;
      });
    }
    on(document, "click", "[data-reorder]", (e, b) => {
      const codes = { "NX-240871": [2001] };
      (codes[b.dataset.reorder] || [1001]).forEach((id) => {
        const p = ctx4.product(id);
        if (p) addToCart(b, p, { silent: true });
      });
      toast(ctx4.t("toast.addedToCart", { name: b.dataset.reorder }), { type: "success", action: ctx4.t("toast.viewCart"), actionHref: ctx4.url("cart.html") });
    });
    const addrPage = $("[data-addresses-page]");
    if (addrPage) {
      const modal = $("[data-address-modal]");
      const form = $("[data-address-form]", modal);
      const list = $("[data-address-list]", addrPage);
      let editing = null;
      liveValidation(form, ctx4);
      on(addrPage, "click", "[data-address-add]", () => {
        editing = null;
        form.reset();
        $("[data-address-modal-title]", modal).textContent = ctx4.t("account.addAddress");
        openModal(modal);
        $("input", form).focus();
      });
      on(addrPage, "click", "[data-address-edit]", (e, b) => {
        editing = b.closest("[data-address]");
        $("[data-address-modal-title]", modal).textContent = ctx4.t("account.editAddress");
        form.title.value = $("[data-address-title]", editing).textContent.trim();
        form.receiver.value = $("[data-address-receiver]", editing).textContent.trim();
        form.phone.value = $("[data-address-phone]", editing).textContent.trim();
        form.address.value = $("[data-address-text]", editing).textContent.trim();
        form.city.value = form.city.value || (ctx4.lang === "fa" ? "\u062A\u0647\u0631\u0627\u0646" : "San Jose");
        form.postal.value = form.postal.value || "1968934511";
        openModal(modal);
        $("input", form).focus();
      });
      on(addrPage, "click", "[data-address-delete]", (e, b) => {
        if (confirm(ctx4.t("account.deleteAddressConfirm"))) {
          b.closest("[data-address]").remove();
          toast(ctx4.t("toast.saved"), { type: "success" });
        }
      });
      on(addrPage, "click", "[data-address-default]", (e, b) => {
        $$("[data-address]", list).forEach((c) => {
          var _a;
          c.classList.remove("is-default");
          (_a = $(".badge", c)) == null ? void 0 : _a.remove();
        });
        const card = b.closest("[data-address]");
        card.classList.add("is-default");
        $(".address-card__title", card).insertAdjacentHTML("beforeend", ` <span class="badge badge--discount">${esc(ctx4.t("account.default"))}</span>`);
        toast(ctx4.t("toast.saved"), { type: "success" });
      });
      form.addEventListener("submit", (e) => {
        e.preventDefault();
        if (!validateForm(form, ctx4)) return;
        const data = Object.fromEntries(new FormData(form).entries());
        const html = `<div class="address-card__title">${icon("map-marker", "xs")}<span data-address-title>${esc(data.title)}</span>${data.default ? ` <span class="badge badge--discount">${esc(ctx4.t("account.default"))}</span>` : ""}</div><p class="address-card__text" data-address-text>${esc(data.city)}\u060C ${esc(data.address)}</p><div class="address-card__meta"><span data-address-receiver>${esc(data.receiver)}</span> \xB7 <span class="ltr" data-address-phone>${esc(data.phone)}</span> \xB7 <span class="ltr">${esc(data.postal)}</span></div><div class="address-card__actions"><button type="button" data-address-edit>${icon("pencil", "xs")}${esc(ctx4.t("common.edit"))}</button>${data.default ? "" : `<button type="button" data-address-default>${icon("check", "xs")}${esc(ctx4.t("account.setDefault"))}</button>`}<button type="button" data-address-delete>${icon("trash2", "xs")}${esc(ctx4.t("common.delete"))}</button></div>`;
        if (data.default) $$("[data-address]", list).forEach((c) => {
          var _a;
          c.classList.remove("is-default");
          (_a = $(".badge", c)) == null ? void 0 : _a.remove();
        });
        if (editing) {
          editing.innerHTML = html;
          editing.classList.toggle("is-default", !!data.default);
        } else {
          const card = document.createElement("article");
          card.className = `address-card${data.default ? " is-default" : ""}`;
          card.setAttribute("data-address", "");
          card.innerHTML = html;
          list.insertBefore(card, $("[data-address-add]", list));
        }
        modal.close();
        toast(ctx4.t("toast.saved"), { type: "success" });
      });
    }
  }

  // src/js/pages/auth.js
  function initAuth(ctx4) {
    $$("[data-auth-form]").forEach((form) => {
      liveValidation(form, ctx4);
      form.addEventListener("submit", (e) => {
        e.preventDefault();
        if (!validateForm(form, ctx4)) return;
        const kind = form.dataset.authForm;
        const btn = $('button[type="submit"]', form);
        btn.classList.add("is-loading");
        setTimeout(() => {
          btn.classList.remove("is-loading");
          if (kind === "login") {
            const id = form.identifier.value.trim();
            login({ name: ctx4.t("account.userName"), email: id.includes("@") ? id : ctx4.t("account.userEmail") });
            toast(ctx4.t("auth.loginSuccess"), { type: "success" });
            setTimeout(() => {
              location.href = redirectTarget(ctx4);
            }, 900);
          } else if (kind === "register") {
            login({ name: form.name.value.trim(), email: form.email.value.trim() || ctx4.t("account.userEmail") });
            toast(ctx4.t("auth.registerSuccess"), { type: "success" });
            setTimeout(() => {
              location.href = ctx4.url("account.html");
            }, 900);
          } else if (kind === "forgot") {
            const target = form.identifier.value.trim();
            form.hidden = true;
            const otp2 = $('[data-forgot-step="2"]');
            otp2.hidden = false;
            $("[data-otp-sub]", otp2).textContent = ctx4.t("auth.otpSub", { target });
            toast(ctx4.t("auth.codeSent"), { type: "success" });
            $("input", otp2).focus();
            startResendTimer(ctx4, $("[data-otp-resend]", otp2));
          } else if (kind === "otp") {
            const code = $$("[data-otp] input", form).map((i) => i.value).join("");
            if (code.length < 5) {
              $(".form-error", form).textContent = ctx4.t("checkout.errors.required");
              $(".form-error", form).classList.add("is-visible");
              return;
            }
            form.hidden = true;
            $('[data-forgot-step="3"]').hidden = false;
            login({ name: ctx4.t("account.userName"), email: ctx4.t("account.userEmail") });
          }
        }, 800);
      });
    });
    const otp = $("[data-otp]");
    if (otp) {
      const inputs = $$("input", otp);
      inputs.forEach((inp, i) => {
        inp.addEventListener("input", () => {
          inp.value = inp.value.replace(/\D/g, "").slice(-1);
          if (inp.value && inputs[i + 1]) inputs[i + 1].focus();
        });
        inp.addEventListener("keydown", (e) => {
          if (e.key === "Backspace" && !inp.value && inputs[i - 1]) inputs[i - 1].focus();
        });
        inp.addEventListener("paste", (e) => {
          const txt = (e.clipboardData.getData("text") || "").replace(/\D/g, "");
          if (!txt) return;
          e.preventDefault();
          txt.split("").slice(0, inputs.length).forEach((c, j) => {
            inputs[j].value = c;
          });
          inputs[Math.min(txt.length, inputs.length) - 1].focus();
        });
      });
    }
    if (ctx4.page === "login" && isLoggedIn() && new URLSearchParams(location.search).get("force") !== "1") {
    }
  }
  function redirectTarget(ctx4) {
    const next = new URLSearchParams(location.search).get("next");
    return next && /^[\w\-./?=&%]+$/.test(next) ? next : ctx4.url("account.html");
  }
  function startResendTimer(ctx4, btn) {
    if (!btn) return;
    let n = 60;
    btn.disabled = true;
    const tick = () => {
      btn.textContent = n > 0 ? ctx4.t("auth.resendIn", { n: ctx4.digits(n) }) : ctx4.t("auth.resend");
      if (n-- > 0) setTimeout(tick, 1e3);
      else btn.disabled = false;
    };
    tick();
    btn.addEventListener("click", () => {
      toast(ctx4.t("auth.codeSent"), { type: "success" });
      n = 60;
      btn.disabled = true;
      tick();
    });
  }

  // src/js/main.js
  function start() {
    const ctx4 = boot();
    initToasts(ctx4);
    initDrawers();
    initHeader(ctx4);
    initCartUI(ctx4);
    initSearch(ctx4);
    initNewsletter(ctx4);
    initPasswordTools(ctx4);
    initCountdowns(ctx4);
    initTabs();
    initDemoForms(ctx4);
    initShop(ctx4);
    initProduct(ctx4);
    initCartPage(ctx4);
    initCheckoutPage(ctx4);
    initWishlistPage(ctx4);
    initComparePage(ctx4);
    initBlogPage(ctx4);
    initAccountPages(ctx4);
    initAuth(ctx4);
    initSwipers(ctx4);
    initReveal();
    document.documentElement.classList.add("js-ready");
  }
  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", start);
  else start();
})();
