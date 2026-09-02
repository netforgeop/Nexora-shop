/**
 * App context: locale, catalog lookup, formatting — a single object passed to
 * every component. Reads the generated globals injected by the build
 * (window.NEXORA_I18N / window.NEXORA_CATALOG).
 */
import { createTranslator } from './i18n.js';
import { createCtx } from './render.js';
import { formatPrice } from './format.js';

let ctx = null;
let catalog = null;

export function boot() {
  const html = document.documentElement;
  const lang = html.lang || 'fa';
  const dir = html.dir || (lang === 'fa' ? 'rtl' : 'ltr');
  const root = html.dataset.root || './';
  const L = window.NEXORA_I18N || {};
  catalog = window.NEXORA_CATALOG || { products: [], categories: [], brands: [], currency: { symbol: '', rate: 1, position: 'after', decimals: 0 }, config: {} };
  const t = createTranslator(L);
  ctx = createCtx({ lang, dir, root, t, currency: catalog.currency });
  ctx.L = L;
  ctx.config = catalog.config || {};
  ctx.catalog = catalog;
  ctx.byId = new Map(catalog.products.map((p) => [p.id, p]));
  ctx.bySlug = new Map(catalog.products.map((p) => [p.slug, p]));
  ctx.product = (id) => ctx.byId.get(Number(id));
  ctx.money = (base) => formatPrice(base, lang, catalog.currency);
  ctx.page = document.body.dataset.page || '';
  return ctx;
}

export const getCtx = () => ctx;
export const getCatalog = () => catalog;
