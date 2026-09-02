/**
 * Page-level renderers that are reused at build time (static product/post
 * pages) and at runtime (product.html?slug=… fallback, quick view).
 */
import { esc } from './i18n.js';
import { icon, svgIcon, ratingHTML, priceHTML, badgesHTML, qtyHTML, reviewHTML, productCardHTML, starIcon } from './render.js';

/* ------------------------------------------------------------------ */
/* Product page                                                        */
/* ------------------------------------------------------------------ */
export function productPageHTML(ctx, p, reviews = [], { related = [] } = {}) {
  const t = ctx.t;
  const saving = p.oldPrice ? p.oldPrice - p.price : 0;
  const stockClass = !p.inStock ? 'stock--out' : p.lowStock ? 'stock--low' : 'stock--in';
  const stockText = !p.inStock ? t('common.outOfStock') : p.lowStock ? t('common.lowStock', { n: ctx.digits(p.stock) }) : t('common.inStock');

  const gallerySlides = p.images.map((img, i) => `
    <div class="swiper-slide">
      <a href="${ctx.img(img)}" data-pswp-width="1280" data-pswp-height="1280" target="_blank" rel="noopener" aria-label="${esc(t('product.zoom'))}">
        <img src="${ctx.img(img)}" width="640" height="640" alt="${esc(p.name)} – ${ctx.digits(i + 1)}" ${i === 0 ? 'fetchpriority="high"' : 'loading="lazy"'} decoding="async">
      </a>
    </div>`).join('');
  const thumbs = p.images.map((img, i) => `<div class="swiper-slide" role="button" tabindex="0" aria-label="${esc(t('product.thumbs'))} ${ctx.digits(i + 1)}"><img src="${ctx.img(img)}" width="84" height="84" alt="" loading="lazy" decoding="async"></div>`).join('');

  const colors = p.colors.length ? `
    <fieldset class="variant" data-variant="color">
      <legend class="variant__label">${esc(t('product.selectColor'))} <span data-variant-value>${esc(p.colors[0].name)}</span></legend>
      <div class="variant__options">
        ${p.colors.map((c, i) => `<label class="variant__option"><input type="radio" name="color" value="${esc(c.name)}" data-hex="${esc(c.hex)}"${i === 0 ? ' checked' : ''}><span class="swatch" style="background:${esc(c.hex)}" title="${esc(c.name)}"></span><span class="visually-hidden">${esc(c.name)}</span></label>`).join('')}
      </div>
    </fieldset>` : '';
  const sizes = p.sizes.length ? `
    <fieldset class="variant" data-variant="size">
      <legend class="variant__label" style="display:flex;inline-size:100%">${esc(t('product.selectSize'))} <span data-variant-value>${esc(p.sizes.find((s) => !s.disabled)?.label || '')}</span><button type="button" class="variant__guide" data-size-guide>${esc(t('product.sizeGuide'))}</button></legend>
      <div class="variant__options">
        ${p.sizes.map((s, i) => `<label class="variant__option"><input type="radio" name="size" value="${esc(s.label)}"${s.disabled ? ' disabled' : ''}${!s.disabled && p.sizes.findIndex((x) => !x.disabled) === i ? ' checked' : ''}><span class="size-chip">${esc(s.label)}</span></label>`).join('')}
      </div>
    </fieldset>` : '';

  const specs = p.specs.map((g) => `
    <div class="spec-group">
      <h3 class="spec-group__title">${esc(g.group)}</h3>
      <table class="spec-table"><tbody>${g.rows.map(([k, v]) => `<tr><th scope="row">${esc(k)}</th><td>${esc(v)}</td></tr>`).join('')}</tbody></table>
    </div>`).join('');

  const dist = [5, 4, 3, 2, 1].map((star) => {
    const n = reviews.filter((r) => Math.round(r.rating) === star).length;
    const pct = reviews.length ? Math.round((n / reviews.length) * 100) : 0;
    return `<div class="rating-bar"><span class="rating-bar__label">${ctx.digits(star)} ${starIcon()}</span><div class="rating-bar__track"><div class="rating-bar__fill" style="inline-size:${pct}%"></div></div><span class="rating-bar__value">${ctx.digits(pct)}%</span></div>`;
  }).join('');

  const desc = (p.description || '').split(/\n\n+/).map((para) => `<p>${esc(para)}</p>`).join('');
  const highlights = p.highlights.map((h) => `<li>${icon('check', 'xs')}<span>${esc(h)}</span></li>`).join('');

  const structured = {
    '@context': 'https://schema.org', '@type': 'Product', name: p.name, sku: p.sku, image: p.images.map((i) => `${ctx.img(i)}`),
    description: p.short, brand: { '@type': 'Brand', name: p.brandName },
    offers: { '@type': 'Offer', price: p.price, priceCurrency: ctx.currency.code || 'IRR', availability: p.inStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock', itemCondition: 'https://schema.org/NewCondition' },
    aggregateRating: { '@type': 'AggregateRating', ratingValue: p.rating, reviewCount: p.reviewCount },
  };

  return `
<script type="application/ld+json">${JSON.stringify(structured)}</script>
<section class="section section--sm" data-product-page data-id="${p.id}" data-slug="${esc(p.slug)}" data-max="${p.maxQty}">
  <div class="container">
    <div class="product-layout">
      <!-- Gallery -->
      <div class="gallery" data-gallery>
        <div class="gallery__main swiper" data-gallery-main id="gallery-main">
          <div class="swiper-wrapper">${gallerySlides}</div>
          <div class="gallery__badges">${badgesHTML(ctx, p, { max: 2 })}</div>
          <div class="gallery__tools">
            <button type="button" class="icon-btn icon-btn--circle" data-action="wishlist" data-id="${p.id}" aria-label="${esc(t('product.wishlist'))}" aria-pressed="false">${icon('heart', 'sm')}</button>
            <button type="button" class="icon-btn icon-btn--circle" data-action="compare" data-id="${p.id}" aria-label="${esc(t('product.compare'))}" aria-pressed="false">${icon('compare', 'sm')}</button>
            <button type="button" class="icon-btn icon-btn--circle" data-action="share" aria-label="${esc(t('product.share'))}">${icon('share2', 'sm')}</button>
          </div>
          <span class="gallery__zoom-hint">${icon('zoom-in', 'xs')} ${esc(t('product.zoom'))}</span>
          <button type="button" class="gallery__nav gallery__nav--prev" data-gallery-prev aria-label="${esc(t('product.prevImage'))}">${icon('chevron-right', 'xs', 'icon--flip-ltr')}</button>
          <button type="button" class="gallery__nav gallery__nav--next" data-gallery-next aria-label="${esc(t('product.nextImage'))}">${icon('chevron-left', 'xs', 'icon--flip-ltr')}</button>
        </div>
        <div class="gallery__thumbs swiper" data-gallery-thumbs aria-label="${esc(t('product.thumbs'))}"><div class="swiper-wrapper">${thumbs}</div></div>
      </div>

      <!-- Info -->
      <div class="product-info">
        <div class="product-info__brand"><span>${esc(t('product.brand'))} <a href="${ctx.brandUrl(p.brand)}">${esc(p.brandName)}</a></span><span class="sep"></span><span>${esc(t('product.sku'))} <span class="ltr">${esc(p.sku)}</span></span></div>
        <h1 class="product-info__title">${esc(p.name)}</h1>
        <div class="product-info__meta">
          ${ratingHTML(ctx, p.rating, p.reviewCount, { size: 'lg' })}
          <a href="#tab-reviews" data-tab-jump="reviews">${esc(t('product.reviewsCount', { n: ctx.num(p.reviewCount) }))}</a>
          <span class="sep"></span>
          <a href="#tab-qa" data-tab-jump="qa">${esc(t('product.questions', { n: ctx.digits(4) }))}</a>
          <span class="sep"></span>
          <span>${esc(t('common.sold', { n: ctx.num(p.sold) }))}</span>
        </div>

        <ul class="product-info__highlights" aria-label="${esc(t('product.highlights'))}">${highlights}</ul>

        <form class="variants" data-variants>${colors}${sizes}</form>

        <div class="buy-box">
          <div class="product-info__price">
            ${priceHTML(ctx, p, { size: 'lg', showDiscount: true })}
            ${saving ? `<span class="product-info__saving">${esc(t('product.youSave', { n: ctx.price(saving) }))}</span>` : ''}
          </div>
          <span class="stock ${stockClass}" data-stock>${esc(stockText)}</span>
          <div class="buy-box__row">
            ${qtyHTML(ctx, 1, p.maxQty)}
            <button type="button" class="btn btn--primary btn--lg" data-action="add-to-cart" data-id="${p.id}" data-with-variants${p.inStock ? '' : ' disabled'}>${icon('cart-add', 'sm')}<span>${esc(t('product.addToCart'))}</span></button>
            <button type="button" class="btn btn--dark btn--lg" data-action="buy-now" data-id="${p.id}" data-with-variants${p.inStock ? '' : ' disabled'}>${icon('cash-dollar', 'sm')}<span>${esc(t('product.buyNow'))}</span></button>
          </div>
          <div class="buy-box__secondary">
            <button type="button" data-action="wishlist" data-id="${p.id}" aria-pressed="false">${icon('heart', 'xs')}<span>${esc(t('product.wishlist'))}</span></button>
            <button type="button" data-action="compare" data-id="${p.id}" aria-pressed="false">${icon('compare', 'xs')}<span>${esc(t('product.compare'))}</span></button>
            <button type="button" data-action="share">${icon('share2', 'xs')}<span>${esc(t('product.share'))}</span></button>
          </div>
        </div>

        <div class="assurance">
          <div class="assurance__item">${icon('shield-check')}<div><strong>${esc(t('product.warranty'))}</strong>${esc(t('product.warrantyText'))}</div></div>
          <div class="assurance__item">${icon('truck')}<div><strong>${esc(t('product.shipping'))}</strong>${esc(t('product.shippingText'))}</div></div>
          <div class="assurance__item">${icon('undo')}<div><strong>${esc(t('product.returns'))}</strong>${esc(t('product.returnsText'))}</div></div>
          <div class="assurance__item">${icon('lock')}<div><strong>${esc(t('product.securePay'))}</strong>${esc(t('product.securePayText'))}</div></div>
        </div>
        <div class="seller-box">
          <div><div class="seller-box__name">${icon('store', 'sm')}${esc(t('product.seller'))} ${esc(t('product.sellerName'))} ${icon('checkmark-circle', 'xs')}</div><div class="seller-box__meta">${esc(t('product.sellerMeta'))}</div></div>
          <a class="btn btn--outline btn--sm" href="${ctx.url('about.html')}">${esc(t('common.details'))}</a>
        </div>
      </div>

      <!-- Sticky aside (xxl only) -->
      <aside class="product-layout__aside" aria-label="${esc(t('product.buyNow'))}">
        <div class="product-aside-box">
          <div class="text-muted small">${esc(t('product.seller'))} <strong class="text-strong">${esc(t('product.sellerName'))}</strong></div>
          <span class="stock ${stockClass}">${esc(stockText)}</span>
          ${priceHTML(ctx, p, { size: 'lg', showDiscount: true })}
          <button type="button" class="btn btn--primary btn--block" data-action="add-to-cart" data-id="${p.id}" data-with-variants${p.inStock ? '' : ' disabled'}>${icon('cart-add', 'sm')}${esc(t('product.addToCart'))}</button>
          <ul class="stack--sm small text-muted" style="display:grid;gap:8px">
            <li style="display:flex;gap:8px;align-items:center">${icon('truck', 'xs')}${esc(t('product.shippingText'))}</li>
            <li style="display:flex;gap:8px;align-items:center">${icon('undo', 'xs')}${esc(t('product.returns'))}</li>
            <li style="display:flex;gap:8px;align-items:center">${icon('shield-check', 'xs')}${esc(t('product.warranty'))}</li>
          </ul>
        </div>
      </aside>
    </div>

    <!-- Details tabs -->
    <div class="product-details tabs" data-tabs>
      <div class="tabs__list" role="tablist" aria-label="${esc(t('product.title'))}">
        <button class="tabs__tab" role="tab" id="tab-btn-description" aria-selected="true" aria-controls="tab-description" data-tab="description">${icon('file-empty', 'xs')}${esc(t('product.tabDescription'))}</button>
        <button class="tabs__tab" role="tab" id="tab-btn-specs" aria-selected="false" aria-controls="tab-specs" data-tab="specs" tabindex="-1">${icon('list', 'xs')}${esc(t('product.tabSpecs'))}</button>
        <button class="tabs__tab" role="tab" id="tab-btn-reviews" aria-selected="false" aria-controls="tab-reviews" data-tab="reviews" tabindex="-1">${icon('star', 'xs')}${esc(t('product.tabReviews'))}<span class="badge badge--soft">${ctx.num(p.reviewCount)}</span></button>
        <button class="tabs__tab" role="tab" id="tab-btn-qa" aria-selected="false" aria-controls="tab-qa" data-tab="qa" tabindex="-1">${icon('bubble-question', 'xs')}${esc(t('product.tabQa'))}</button>
        <button class="tabs__tab" role="tab" id="tab-btn-shipping" aria-selected="false" aria-controls="tab-shipping" data-tab="shipping" tabindex="-1">${icon('truck', 'xs')}${esc(t('product.tabShipping'))}</button>
      </div>

      <div class="tabs__panel" role="tabpanel" id="tab-description" aria-labelledby="tab-btn-description">
        <h2 class="visually-hidden">${esc(t('product.tabDescription'))}</h2>
        <div class="product-desc-grid">
          <div class="prose">${desc}</div>
          <div>
            <div class="card-surface card-surface--pad">
              <h3 class="h5" style="margin-block-end:var(--space-4)">${esc(t('product.highlights'))}</h3>
              <ul class="product-info__highlights" style="background:transparent;padding:0">${highlights}</ul>
              <hr class="divider">
              <div class="cluster">${p.tags.map((tag) => `<a class="chip" href="${ctx.url('search.html')}?q=${encodeURIComponent(tag)}">${icon('tag', 'xs')}${esc(tag)}</a>`).join('')}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="tabs__panel" role="tabpanel" id="tab-specs" aria-labelledby="tab-btn-specs" hidden>
        <h2 class="visually-hidden">${esc(t('product.tabSpecs'))}</h2>
        <div class="row"><div class="col-lg-9">${specs}</div></div>
      </div>

      <div class="tabs__panel" role="tabpanel" id="tab-reviews" aria-labelledby="tab-btn-reviews" hidden>
        <h2 class="visually-hidden">${esc(t('product.reviewsTitle'))}</h2>
        <div class="reviews-summary">
          <div class="reviews-summary__score">
            <span class="reviews-summary__value">${ctx.digits(p.rating.toFixed(1))}</span>
            ${ratingHTML(ctx, p.rating, null, { size: 'lg', showValue: false, showCount: false })}
            <span class="reviews-summary__count">${esc(t('product.basedOn', { n: ctx.num(p.reviewCount) }))}</span>
          </div>
          <div class="rating-bars">${dist}</div>
          <div><button type="button" class="btn btn--dark" data-review-toggle aria-expanded="false" aria-controls="review-form">${icon('pencil', 'xs')}${esc(t('product.writeReview'))}</button></div>
        </div>
        <form class="review-form" id="review-form" data-review-form hidden novalidate>
          <h3 class="review-form__title">${esc(t('product.reviewFormTitle'))}</h3>
          <div class="form-group">
            <span class="form-label">${esc(t('product.yourRating'))} <span class="req">*</span></span>
            <div class="rating-input" role="radiogroup" aria-label="${esc(t('product.yourRating'))}">
              ${[5, 4, 3, 2, 1].map((n) => `<input type="radio" id="rate-${n}" name="rating" value="${n}" required><label for="rate-${n}" title="${esc(t('product.stars', { n: ctx.digits(n) }))}">${starIcon()}<span class="visually-hidden">${esc(t('product.stars', { n: ctx.digits(n) }))}</span></label>`).join('')}
            </div>
            <span class="form-error"></span>
          </div>
          <div class="form-row">
            <div class="form-group"><label class="form-label" for="review-name">${esc(t('blog.name'))} <span class="req">*</span></label><input id="review-name" class="form-control" name="name" required minlength="2"><span class="form-error"></span></div>
            <div class="form-group"><label class="form-label" for="review-title">${esc(t('product.reviewTitle'))} <span class="req">*</span></label><input id="review-title" class="form-control" name="title" required minlength="3"><span class="form-error"></span></div>
          </div>
          <div class="form-group"><label class="form-label" for="review-text">${esc(t('product.reviewText'))} <span class="req">*</span></label><textarea id="review-text" class="form-control" name="text" required minlength="20" placeholder="${esc(t('product.reviewTextPlaceholder'))}"></textarea><span class="form-error"></span></div>
          <div class="cluster"><button type="submit" class="btn btn--primary">${esc(t('product.submitReview'))}</button><button type="button" class="btn btn--ghost" data-review-cancel>${esc(t('common.cancel'))}</button></div>
        </form>
        <div data-reviews-list>${reviews.map((r) => reviewHTML(ctx, r)).join('')}</div>
      </div>

      <div class="tabs__panel" role="tabpanel" id="tab-qa" aria-labelledby="tab-btn-qa" hidden>
        <h2 class="visually-hidden">${esc(t('product.tabQa'))}</h2>
        <div class="row">
          <div class="col-lg-8">
            ${qaHTML(ctx, p)}
            <form class="review-form" data-qa-form novalidate style="margin-block-start:var(--space-6)">
              <h3 class="review-form__title">${esc(t('product.askQuestion'))}</h3>
              <div class="form-group"><label class="visually-hidden" for="qa-text">${esc(t('product.askQuestion'))}</label><textarea id="qa-text" class="form-control" name="question" required minlength="10" placeholder="${esc(t('product.askPlaceholder'))}"></textarea><span class="form-error"></span></div>
              <div><button type="submit" class="btn btn--dark">${esc(t('product.submitQuestion'))}</button></div>
            </form>
          </div>
        </div>
      </div>

      <div class="tabs__panel" role="tabpanel" id="tab-shipping" aria-labelledby="tab-btn-shipping" hidden>
        <h2 class="visually-hidden">${esc(t('product.tabShipping'))}</h2>
        <div class="row g-4">
          <div class="col-md-6"><h3 class="h5" style="display:flex;gap:8px;align-items:center;margin-block-end:var(--space-3)">${icon('truck', 'sm')}${esc(t('product.shippingInfoTitle'))}</h3><p>${esc(t('product.shippingInfo'))}</p></div>
          <div class="col-md-6"><h3 class="h5" style="display:flex;gap:8px;align-items:center;margin-block-end:var(--space-3)">${icon('undo', 'sm')}${esc(t('product.returnsInfoTitle'))}</h3><p>${esc(t('product.returnsInfo'))}</p></div>
        </div>
      </div>
    </div>
  </div>
</section>

${related.length ? `
<section class="section bg-surface" aria-labelledby="sec-related">
  <div class="container">
    <div class="section-head">
      <div><h2 class="section-head__title" id="sec-related">${esc(t('product.relatedTitle'))}</h2><p class="section-head__sub">${esc(t('product.relatedSub'))}</p></div>
      <div class="section-head__aside"><div class="carousel-nav"><button type="button" class="carousel-nav__btn" data-carousel-prev="related" aria-label="${esc(t('common.prev'))}">${icon('chevron-right', 'xs', 'icon--flip-ltr')}</button><button type="button" class="carousel-nav__btn" data-carousel-next="related" aria-label="${esc(t('common.next'))}">${icon('chevron-left', 'xs', 'icon--flip-ltr')}</button></div></div>
    </div>
    <div class="product-carousel"><div class="swiper" data-swiper="products" data-carousel-id="related"><div class="swiper-wrapper">${related.map((r) => `<div class="swiper-slide">${productCardHTML(ctx, r)}</div>`).join('')}</div></div></div>
  </div>
</section>` : ''}

<section class="section" data-recent-section hidden aria-labelledby="sec-recent">
  <div class="container">
    <div class="section-head"><div><h2 class="section-head__title" id="sec-recent">${esc(t('product.recentTitle'))}</h2></div></div>
    <div class="product-carousel"><div class="swiper" data-swiper="products" data-carousel-id="recent"><div class="swiper-wrapper" data-recent-list></div></div></div>
  </div>
</section>

<div class="sticky-buy" data-sticky-buy>
  <div style="min-inline-size:0">
    <div class="truncate small fw-medium">${esc(p.name)}</div>
    ${priceHTML(ctx, p, { size: 'sm' })}
  </div>
  <button type="button" class="btn btn--primary" data-action="add-to-cart" data-id="${p.id}" data-with-variants${p.inStock ? '' : ' disabled'}>${icon('cart-add', 'xs')}${esc(t('product.stickyAdd'))}</button>
</div>`;
}

function qaHTML(ctx, p) {
  const t = ctx.t;
  const fa = ctx.lang === 'fa';
  const items = fa ? [
    ['آیا این محصول گارانتی دارد؟', `بله، ${p.name} با ۱۸ ماه گارانتی شرکتی و ضمانت اصالت نکسورا ارسال می‌شود.`],
    ['زمان ارسال به شهرستان چقدر است؟', 'سفارش‌های ثبت‌شده تا ساعت ۱۴، همان روز ارسال می‌شوند و معمولاً ظرف ۴۸ تا ۷۲ ساعت به دست شما می‌رسند.'],
    ['امکان پرداخت در محل وجود دارد؟', 'برای تهران بله؛ در سایر شهرها پرداخت اینترنتی فعال است.'],
    ['اگر پشیمان شدم می‌توانم مرجوع کنم؟', 'تا ۷ روز پس از تحویل، در صورت باز نشدن پلمب و سالم بودن بسته‌بندی، امکان بازگشت وجود دارد.'],
  ] : [
    ['Does this product come with a warranty?', `Yes, ${p.name} ships with an 18-month warranty and Nexora's authenticity guarantee.`],
    ['How long does shipping take?', 'Orders placed before 2 PM ship the same day and usually arrive within 48 to 72 hours.'],
    ['Is cash on delivery available?', 'Yes in the metro area; other regions support online payment.'],
    ['Can I return it if I change my mind?', 'Within 7 days of delivery, as long as the seal is intact and the packaging is undamaged.'],
  ];
  return `<h3 class="h5" style="margin-block-end:var(--space-3)">${esc(t('product.qaTitle'))}</h3>` +
    items.map(([q, a]) => `<div class="qa-item"><div class="qa-item__q" data-label="${fa ? 'س' : 'Q'}">${esc(q)}</div><div class="qa-item__a" data-label="${fa ? 'ج' : 'A'}">${esc(a)}</div></div>`).join('');
}

/* ------------------------------------------------------------------ */
/* Quick view                                                          */
/* ------------------------------------------------------------------ */
export function quickViewHTML(ctx, p) {
  const t = ctx.t;
  const stockClass = !p.inStock ? 'stock--out' : p.lowStock ? 'stock--low' : 'stock--in';
  const stockText = !p.inStock ? t('common.outOfStock') : p.lowStock ? t('common.lowStock', { n: ctx.digits(p.stock) }) : t('common.inStock');
  return `<div class="quick-view" data-product-page data-id="${p.id}" data-max="${p.maxQty}">
    <div class="quick-view__media"><img src="${ctx.img(p.image)}" width="640" height="640" alt="${esc(p.name)}"></div>
    <div class="quick-view__body">
      <div class="product-card__category">${esc(p.categoryName)} · ${esc(p.brandName)}</div>
      <h2 class="h4">${esc(p.name)}</h2>
      ${ratingHTML(ctx, p.rating, p.reviewCount)}
      <p class="quick-view__desc">${esc(p.short)}</p>
      <form class="variants" data-variants>
        ${p.colors.length ? `<fieldset class="variant" data-variant="color"><legend class="variant__label">${esc(t('product.selectColor'))} <span data-variant-value>${esc(p.colors[0].name)}</span></legend><div class="variant__options">${p.colors.map((c, i) => `<label class="variant__option"><input type="radio" name="color" value="${esc(c.name)}" data-hex="${esc(c.hex)}"${i === 0 ? ' checked' : ''}><span class="swatch" style="background:${esc(c.hex)}"></span><span class="visually-hidden">${esc(c.name)}</span></label>`).join('')}</div></fieldset>` : ''}
        ${p.sizes.length ? `<fieldset class="variant" data-variant="size"><legend class="variant__label">${esc(t('product.selectSize'))} <span data-variant-value>${esc(p.sizes.find((s) => !s.disabled)?.label || '')}</span></legend><div class="variant__options">${p.sizes.map((s, i) => `<label class="variant__option"><input type="radio" name="size" value="${esc(s.label)}"${s.disabled ? ' disabled' : ''}${!s.disabled && p.sizes.findIndex((x) => !x.disabled) === i ? ' checked' : ''}><span class="size-chip">${esc(s.label)}</span></label>`).join('')}</div></fieldset>` : ''}
      </form>
      <div class="product-info__price">${priceHTML(ctx, p, { size: 'lg', showDiscount: true })}</div>
      <span class="stock ${stockClass}">${esc(stockText)}</span>
      <div class="buy-box__row">
        ${qtyHTML(ctx, 1, p.maxQty)}
        <button type="button" class="btn btn--primary" data-action="add-to-cart" data-id="${p.id}" data-with-variants${p.inStock ? '' : ' disabled'}>${icon('cart-add', 'sm')}${esc(t('product.addToCart'))}</button>
      </div>
      <div class="buy-box__secondary">
        <button type="button" data-action="wishlist" data-id="${p.id}" aria-pressed="false">${icon('heart', 'xs')}<span>${esc(t('product.wishlist'))}</span></button>
        <button type="button" data-action="compare" data-id="${p.id}" aria-pressed="false">${icon('compare', 'xs')}<span>${esc(t('product.compare'))}</span></button>
        <a href="${ctx.productUrl(p)}">${icon('link', 'xs')}<span>${esc(t('product.fullDetails'))}</span></a>
      </div>
    </div>
  </div>`;
}

/* ------------------------------------------------------------------ */
/* Blog post page                                                      */
/* ------------------------------------------------------------------ */
export function postPageHTML(ctx, post, { posts = [] } = {}) {
  const t = ctx.t;
  const others = posts.filter((p) => p.id !== post.id);
  const idx = posts.findIndex((p) => p.id === post.id);
  const prev = posts[idx - 1];
  const next = posts[idx + 1];
  const related = others.slice(0, 3);
  const recent = others.slice(0, 4);
  const cats = {};
  for (const p of posts) cats[p.category.slug] = cats[p.category.slug] || { ...p.category, count: 0 }, cats[p.category.slug].count++;
  const tags = [...new Set(posts.flatMap((p) => p.tags))].slice(0, 14);
  const structured = { '@context': 'https://schema.org', '@type': 'BlogPosting', headline: post.title, image: ctx.img(post.image), datePublished: post.date, author: { '@type': 'Person', name: post.author }, description: post.excerpt };
  const postCard = (p) => `<article class="post-card"><a class="post-card__media" href="${ctx.url(`blog/${p.slug}.html`)}" tabindex="-1" aria-hidden="true"><img src="${ctx.img(p.image)}" width="800" height="500" alt="" loading="lazy"><span class="badge badge--discount post-card__cat">${esc(p.category.name)}</span></a><div class="post-card__body"><div class="post-card__meta"><span>${icon('calendar-full', 'xs')}${esc(p.dateLabel)}</span><span>${icon('clock3', 'xs')}${esc(t('blog.readTime', { n: ctx.digits(p.readTime) }))}</span></div><h3 class="post-card__title"><a href="${ctx.url(`blog/${p.slug}.html`)}">${esc(p.title)}</a></h3><p class="post-card__excerpt">${esc(p.excerpt)}</p></div></article>`;

  return `
<script type="application/ld+json">${JSON.stringify(structured)}</script>
<article class="section section--sm">
  <div class="container">
    <header class="article-head">
      <a class="badge badge--discount badge--lg" href="${ctx.url('blog.html')}?cat=${esc(post.category.slug)}" style="justify-self:center">${esc(post.category.name)}</a>
      <h1 class="article-head__title">${esc(post.title)}</h1>
      <div class="article-head__meta">
        <span>${icon('user', 'xs')}${esc(t('blog.by'))} ${esc(post.author)}</span>
        <span>${icon('calendar-full', 'xs')}<time datetime="${esc(post.date)}">${esc(post.dateLabel)}</time></span>
        <span>${icon('clock3', 'xs')}${esc(t('blog.readTime', { n: ctx.digits(post.readTime) }))}</span>
        <span>${icon('eye', 'xs')}${esc(t('blog.views', { n: ctx.num(post.views) }))}</span>
        <span>${icon('bubble', 'xs')}${esc(t('blog.comments', { n: ctx.digits(post.comments) }))}</span>
      </div>
    </header>
    <figure class="article-hero"><img src="${ctx.img(post.wide || post.image)}" width="1600" height="686" alt="${esc(post.title)}" fetchpriority="high" decoding="async"></figure>

    <div class="with-sidebar with-sidebar--end">
      <div class="article-body">
        ${post.toc?.length ? `<nav class="card-surface card-surface--pad" aria-label="${esc(t('blog.toc'))}" style="margin-block-end:var(--space-7)"><h2 class="h6" style="margin-block-end:var(--space-3)">${esc(t('blog.toc'))}</h2><ol class="stack--sm" style="padding-inline-start:var(--space-5);list-style:decimal">${post.toc.map(([id, label]) => `<li><a class="link" href="#${esc(id)}">${esc(label)}</a></li>`).join('')}</ol></nav>` : ''}
        <div class="prose">${post.body}</div>
        <div class="article-tags">${icon('tags', 'sm')}${post.tags.map((tag) => `<a class="chip" href="${ctx.url('blog.html')}?tag=${encodeURIComponent(tag)}">${esc(tag)}</a>`).join('')}</div>
        <div class="article-share"><span class="small text-muted">${esc(t('blog.share'))}</span><button type="button" class="icon-btn icon-btn--light icon-btn--circle" data-action="share" aria-label="${esc(t('common.share'))}">${icon('share2', 'sm')}</button><a class="icon-btn icon-btn--light icon-btn--circle" href="#" aria-label="Telegram">${svgIcon('i-telegram')}</a><a class="icon-btn icon-btn--light icon-btn--circle" href="#" aria-label="WhatsApp">${svgIcon('i-whatsapp')}</a><a class="icon-btn icon-btn--light icon-btn--circle" href="#" aria-label="X">${svgIcon('i-x')}</a><button type="button" class="icon-btn icon-btn--light icon-btn--circle" data-action="copy-link" aria-label="${esc(t('common.copyLink'))}">${icon('link', 'sm')}</button></div>
        <div class="article-author"><span class="avatar avatar--lg" style="display:grid;place-items:center;background:var(--color-gray-900);color:var(--color-primary);font-weight:700;font-size:1.5rem" aria-hidden="true">${esc(post.author.trim().charAt(0))}</span><div><div class="small text-muted">${esc(t('blog.aboutAuthor'))}</div><div class="article-author__name">${esc(post.author)}</div><p class="article-author__bio">${esc(ctx.lang === 'fa' ? 'تیم محتوای نکسورا با تمرکز بر راهنماهای خرید مبتنی بر تجربه واقعی، بررسی‌های بی‌طرفانه و ترندهای روز.' : 'The Nexora content team focuses on experience-based buying guides, unbiased reviews and current trends.')}</p></div></div>
        <nav class="article-nav" aria-label="${esc(t('blog.prevPost'))} / ${esc(t('blog.nextPost'))}">
          ${prev ? `<a class="article-nav__link" href="${ctx.url(`blog/${prev.slug}.html`)}"><span class="article-nav__label">${icon('arrow-right', 'xs', 'icon--flip-ltr')}${esc(t('blog.prevPost'))}</span><span class="article-nav__title clamp-2">${esc(prev.title)}</span></a>` : '<span></span>'}
          ${next ? `<a class="article-nav__link article-nav__link--next" href="${ctx.url(`blog/${next.slug}.html`)}"><span class="article-nav__label">${esc(t('blog.nextPost'))}${icon('arrow-left', 'xs', 'icon--flip-ltr')}</span><span class="article-nav__title clamp-2">${esc(next.title)}</span></a>` : '<span></span>'}
        </nav>

        <section style="margin-block-start:var(--space-8)" aria-labelledby="comments-title">
          <h2 class="h4" id="comments-title" style="margin-block-end:var(--space-4)">${esc(t('blog.commentsTitle'))} <span class="text-muted small">(${ctx.digits(post.comments)})</span></h2>
          ${commentsHTML(ctx)}
          <form class="review-form" data-comment-form novalidate>
            <h3 class="review-form__title">${esc(t('blog.leaveComment'))}</h3>
            <div class="form-row">
              <div class="form-group"><label class="form-label" for="c-name">${esc(t('blog.name'))} <span class="req">*</span></label><input id="c-name" class="form-control" name="name" required minlength="2"><span class="form-error"></span></div>
              <div class="form-group"><label class="form-label" for="c-email">${esc(t('blog.email'))} <span class="req">*</span></label><input id="c-email" class="form-control" name="email" type="email" required dir="ltr"><span class="form-error"></span></div>
            </div>
            <div class="form-group"><label class="form-label" for="c-text">${esc(t('blog.comment'))} <span class="req">*</span></label><textarea id="c-text" class="form-control" name="text" required minlength="10"></textarea><span class="form-error"></span></div>
            <div><button type="submit" class="btn btn--primary">${esc(t('blog.submit'))}</button></div>
          </form>
        </section>
      </div>

      <aside class="with-sidebar__aside">
        <div class="widget">
          <h2 class="widget__title">${esc(t('blog.categories'))}</h2>
          <ul class="widget__list">${Object.values(cats).map((c) => `<li><a href="${ctx.url('blog.html')}?cat=${esc(c.slug)}"><span>${esc(c.name)}</span><span class="count">${ctx.digits(c.count)}</span></a></li>`).join('')}</ul>
        </div>
        <div class="widget">
          <h2 class="widget__title">${esc(t('blog.recent'))}</h2>
          <div class="widget__posts">${recent.map((p) => `<article class="post-inline"><a class="post-inline__media" href="${ctx.url(`blog/${p.slug}.html`)}" tabindex="-1" aria-hidden="true"><img src="${ctx.img(p.image)}" width="80" height="80" alt="" loading="lazy"></a><div><h3 class="post-inline__title"><a href="${ctx.url(`blog/${p.slug}.html`)}">${esc(p.title)}</a></h3><span class="post-inline__date">${esc(p.dateLabel)}</span></div></article>`).join('')}</div>
        </div>
        <div class="widget">
          <h2 class="widget__title">${esc(t('blog.tags'))}</h2>
          <div class="tag-cloud">${tags.map((tag) => `<a href="${ctx.url('blog.html')}?tag=${encodeURIComponent(tag)}">${esc(tag)}</a>`).join('')}</div>
        </div>
        <div class="widget" style="background:var(--color-gray-800);color:var(--color-white);border:0">
          <h2 class="widget__title" style="color:#fff;border-color:rgba(255,255,255,.12)">${esc(t('blog.newsletterTitle'))}</h2>
          <form class="footer-newsletter" data-newsletter novalidate>
            <div class="form-group"><label class="visually-hidden" for="post-newsletter">${esc(t('footer.newsletterPlaceholder'))}</label><input id="post-newsletter" class="form-control" type="email" name="email" placeholder="${esc(t('footer.newsletterPlaceholder'))}" required dir="ltr"><span class="form-error"></span></div>
            <button type="submit" class="btn btn--primary btn--block">${esc(t('footer.subscribe'))}</button>
          </form>
        </div>
      </aside>
    </div>
  </div>
</article>

<section class="section bg-surface" aria-labelledby="sec-related-posts">
  <div class="container">
    <div class="section-head"><div><h2 class="section-head__title" id="sec-related-posts">${esc(t('blog.related'))}</h2></div><div class="section-head__aside"><a class="link--arrow" href="${ctx.url('blog.html')}">${esc(t('blog.all'))}${icon('arrow-left', 'xs', 'icon--flip-ltr')}</a></div></div>
    <div class="row g-4">${related.map((p) => `<div class="col-md-6 col-lg-4">${postCard(p)}</div>`).join('')}</div>
  </div>
</section>`;
}

function commentsHTML(ctx) {
  const fa = ctx.lang === 'fa';
  const items = fa ? [
    ['رضا محمدی', '۵ شهریور ۱۴۰۵', 'مقاله خیلی کاربردی بود، مخصوصاً بخش مقایسه انواع حذف نویز. ممنون از تیم نکسورا.', false],
    ['تیم نکسورا', '۵ شهریور ۱۴۰۵', 'خوشحالیم که مفید بوده. اگر سوالی درباره مدل خاصی دارید همین‌جا بپرسید.', true],
    ['مهسا کرمی', '۴ شهریور ۱۴۰۵', 'کاش یک جدول مقایسه‌ای بین چند مدل پرفروش هم اضافه می‌کردید.', false],
  ] : [
    ['Reza Mohammadi', 'Aug 28, 2026', 'Very practical article, especially the comparison of ANC types. Thanks to the Nexora team.', false],
    ['Nexora Team', 'Aug 28, 2026', 'Glad it helped. If you have questions about a specific model, ask right here.', true],
    ['Mahsa Karami', 'Aug 27, 2026', 'A comparison table between a few best-selling models would be a great addition.', false],
  ];
  return items.map(([name, date, text, reply]) => `<div class="comment${reply ? ' comment--reply' : ''}"><span class="avatar" style="display:grid;place-items:center;background:${reply ? 'var(--color-primary)' : 'var(--color-surface)'};color:var(--color-text-strong);font-weight:700" aria-hidden="true">${esc(name.charAt(0))}</span><div><div class="comment__head"><span class="comment__name">${esc(name)}${reply ? ` <span class="badge badge--discount">${esc(ctx.t('product.sellerName'))}</span>` : ''}</span><span class="comment__date">${esc(date)}</span></div><p class="comment__text">${esc(text)}</p><button type="button" class="comment__reply">${icon('undo', 'xs')}${esc(ctx.t('blog.reply'))}</button></div></div>`).join('');
}
