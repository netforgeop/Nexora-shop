/**
 * Swiper initialisation by data-attribute.
 *   data-swiper="hero" | "products" | "brands" | "reviews"
 * Product carousels bind external nav buttons via data-carousel-id.
 */
import { $, $$, prefersReducedMotion } from '../core/dom.js';

const registry = new Map();

export function initSwipers(ctx, root = document) {
  if (typeof window.Swiper !== 'function') return;
  const isRTL = ctx.dir === 'rtl';
  const reduce = prefersReducedMotion();

  $$('[data-swiper]', root).forEach((el) => {
    if (el.swiper) return;
    const type = el.dataset.swiper;
    const id = el.dataset.carouselId;
    const nav = id ? { prevEl: $(`[data-carousel-prev="${id}"]`), nextEl: $(`[data-carousel-next="${id}"]`) } : null;
    const common = { a11y: { enabled: true }, watchOverflow: true, observer: true, observeParents: true, grabCursor: true, rtl: isRTL };
    let opts;
    switch (type) {
      case 'hero':
        opts = {
          ...common, loop: el.querySelectorAll('.swiper-slide').length > 1, speed: reduce ? 0 : 700, effect: 'fade', fadeEffect: { crossFade: true },
          autoplay: reduce ? false : { delay: 6000, disableOnInteraction: false, pauseOnMouseEnter: true },
          pagination: { el: $('.swiper-pagination', el), clickable: true, renderBullet: (i, cls) => `<button type="button" class="${cls}" aria-label="${ctx.t('common.page')} ${ctx.digits(i + 1)}"></button>` },
          navigation: { prevEl: $('[data-swiper-prev]', el), nextEl: $('[data-swiper-next]', el) },
          keyboard: { enabled: true, onlyInViewport: true },
        };
        break;
      case 'products': {
        const xl = Number(el.dataset.slidesXl || 4);
        const xxl = Number(el.dataset.slidesXxl || 4);
        opts = {
          ...common, slidesPerView: 2, spaceBetween: 12, speed: reduce ? 0 : 450,
          breakpoints: { 576: { slidesPerView: 2, spaceBetween: 16 }, 768: { slidesPerView: 3, spaceBetween: 16 }, 992: { slidesPerView: 3, spaceBetween: 20 }, 1200: { slidesPerView: xl, spaceBetween: 20 }, 1400: { slidesPerView: xxl, spaceBetween: 24 } },
          navigation: nav, keyboard: { enabled: true, onlyInViewport: true },
        };
        break;
      }
      case 'brands':
        opts = { ...common, slidesPerView: 'auto', spaceBetween: 8, loop: true, speed: reduce ? 0 : 5000, autoplay: reduce ? false : { delay: 0, disableOnInteraction: false }, allowTouchMove: true, freeMode: true };
        break;
      case 'reviews':
        opts = { ...common, slidesPerView: 1, spaceBetween: 16, speed: reduce ? 0 : 450, autoHeight: false, breakpoints: { 768: { slidesPerView: 2, spaceBetween: 20 }, 1200: { slidesPerView: 3, spaceBetween: 24 } }, navigation: nav, pagination: false };
        break;
      default:
        opts = { ...common };
    }
    const instance = new window.Swiper(el, opts);
    if (id) registry.set(id, instance);
    if (type === 'brands') el.style.setProperty('--swiper-wrapper-transition-timing-function', 'linear');
  });
}

export function getSwiper(id) { return registry.get(id); }
