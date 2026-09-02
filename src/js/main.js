/**
 * Nexora runtime entry point.
 * Everything initialises by data-attribute so any page can be composed freely.
 */
import { boot } from './core/app.js';
import { initToasts } from './components/toast.js';
import { initHeader } from './components/header.js';
import { initDrawers } from './components/drawer.js';
import { initSwipers } from './components/swipers.js';
import { initCartUI } from './components/cart-ui.js';
import { initSearch } from './components/search.js';
import { initCountdowns, initReveal, initTabs, initNewsletter, initPasswordTools, initDemoForms } from './components/misc.js';
import { initShop } from './pages/shop.js';
import { initProduct } from './pages/product.js';
import { initCartPage, initCheckoutPage } from './pages/cart.js';
import { initWishlistPage, initComparePage, initBlogPage, initAccountPages } from './pages/lists.js';
import { initAuth } from './pages/auth.js';

function start() {
  const ctx = boot();

  // shared chrome
  initToasts(ctx);
  initDrawers();
  initHeader(ctx);
  initCartUI(ctx);
  initSearch(ctx);
  initNewsletter(ctx);
  initPasswordTools(ctx);
  initCountdowns(ctx);
  initTabs();
  initDemoForms(ctx);

  // pages (each is a no-op when its root element is absent)
  initShop(ctx);
  initProduct(ctx);
  initCartPage(ctx);
  initCheckoutPage(ctx);
  initWishlistPage(ctx);
  initComparePage(ctx);
  initBlogPage(ctx);
  initAccountPages(ctx);
  initAuth(ctx);

  // carousels last (they measure layout) + reveal
  initSwipers(ctx);
  initReveal();

  document.documentElement.classList.add('js-ready');
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
else start();
