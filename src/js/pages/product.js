/**
 * Product page: gallery (Swiper thumbs + PhotoSwipe zoom), sticky buy bar,
 * recently viewed, and the dynamic fallback (product.html?slug=…).
 */
import { $, $$ } from '../core/dom.js';
import { productPageHTML } from '../core/render-pages.js';
import { productCardHTML } from '../core/render.js';
import * as store from '../store/state.js';
import { initSwipers } from '../components/swipers.js';
import { initTabs, initDemoForms } from '../components/misc.js';
import { syncPressedStates } from '../components/cart-ui.js';

export function initProduct(ctx) {
  const dyn = $('[data-product-dynamic]');
  if (dyn) renderDynamic(ctx, dyn);
  const page = $('[data-product-page]:not(.quick-view)');
  if (!page) return;
  const id = Number(page.dataset.id);
  store.recentPush(id, ctx.config.recentLimit || 8);

  initGallery(ctx, page);
  initStickyBuy(page);
  renderRecent(ctx, id);
  syncPressedStates(document);
}

function renderDynamic(ctx, host) {
  const sp = new URLSearchParams(location.search);
  const slug = sp.get('slug'); const id = Number(sp.get('id'));
  const product = slug ? ctx.bySlug.get(slug) : ctx.byId.get(id);
  const loading = $('[data-product-loading]', host);
  const notFound = $('[data-product-notfound]', host);
  const target = $('[data-product-target]', host);
  loading.hidden = true;
  if (!product) { notFound.hidden = false; return; }
  const reviews = ctx.catalog.reviews?.[String(product.id)] || ctx.catalog.genericReviews || [];
  const related = ctx.catalog.products.filter((p) => p.category === product.category && p.id !== product.id).slice(0, 8);
  // runtime catalog omits long description/specs to stay small → keep the short copy
  const full = { ...product, description: product.description || product.short, specs: product.specs || [] };
  target.innerHTML = productPageHTML(ctx, full, reviews, { related });
  document.title = `${product.name}${ctx.L.meta?.titleSuffix || ''}`;
  const crumb = $('.breadcrumb__item[aria-current] span'); if (crumb) crumb.textContent = product.name;
  initSwipers(ctx, target);
  initTabs(target);
  initDemoForms(ctx);
}

function initGallery(ctx, page) {
  const gallery = $('[data-gallery]', page);
  if (!gallery || typeof window.Swiper !== 'function') return;
  const thumbsEl = $('[data-gallery-thumbs]', gallery);
  const mainEl = $('[data-gallery-main]', gallery);
  const vertical = window.matchMedia('(min-width: 576px)').matches;
  const thumbs = new window.Swiper(thumbsEl, {
    direction: vertical ? 'vertical' : 'horizontal', slidesPerView: vertical ? 5 : 4.5, spaceBetween: 8, watchSlidesProgress: true, freeMode: true, rtl: ctx.dir === 'rtl', a11y: { enabled: true },
  });
  const main = new window.Swiper(mainEl, {
    slidesPerView: 1, spaceBetween: 0, speed: 350, rtl: ctx.dir === 'rtl', thumbs: { swiper: thumbs }, keyboard: { enabled: true, onlyInViewport: true },
    navigation: { prevEl: $('[data-gallery-prev]', mainEl), nextEl: $('[data-gallery-next]', mainEl) }, a11y: { enabled: true },
  });
  $$('.swiper-slide', thumbsEl).forEach((s, i) => s.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); main.slideTo(i); } }));

  // PhotoSwipe lightbox (loaded as an IIFE bundle exposing window.PhotoSwipeLightbox)
  if (window.PhotoSwipeLightbox && window.PhotoSwipe) {
    const lightbox = new window.PhotoSwipeLightbox({
      gallery: mainEl, children: 'a[data-pswp-width]', pswpModule: window.PhotoSwipe, showHideAnimationType: 'zoom',
      closeTitle: ctx.t('common.close'), zoomTitle: ctx.t('product.zoom'), arrowPrevTitle: ctx.t('product.prevImage'), arrowNextTitle: ctx.t('product.nextImage'), errorMsg: ctx.t('common.error'),
    });
    lightbox.on('change', () => { const i = lightbox.pswp?.currIndex; if (i != null) main.slideTo(i, 0); });
    lightbox.init();
  } else {
    // graceful fallback: open image in new tab (native link behaviour)
  }
}

function initStickyBuy(page) {
  const bar = $('[data-sticky-buy]');
  const anchor = $('.buy-box', page);
  if (!bar || !anchor || !('IntersectionObserver' in window)) return;
  const io = new IntersectionObserver(([e]) => { const show = !e.isIntersecting && e.boundingClientRect.top < 0; bar.classList.toggle('is-visible', show); document.body.classList.toggle('has-sticky-buy', show); }, { threshold: 0 });
  io.observe(anchor);
}

function renderRecent(ctx, currentId) {
  const section = $('[data-recent-section]');
  const list = $('[data-recent-list]');
  if (!section || !list) return;
  const ids = store.getState().recent.filter((id) => id !== currentId);
  const items = ids.map((id) => ctx.product(id)).filter(Boolean);
  if (items.length < 2) return;
  list.innerHTML = items.map((p) => `<div class="swiper-slide">${productCardHTML(ctx, p)}</div>`).join('');
  section.hidden = false;
  initSwipers(ctx, section);
  syncPressedStates(section);
}
