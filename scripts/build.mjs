#!/usr/bin/env node
/**
 * Nexora static site build
 * -----------------------------------------------------------------------
 *  src/templates/**  (layout + partials + pages)  ─┐
 *  src/data/*.json   (products, categories, …)     ├─►  *.html (fa at /, en at /en/)
 *  src/locales/*.json (UI strings)                 │    assets/js/app.js (esbuild bundle)
 *  src/js/**         (runtime modules)            ─┘    assets/js/{i18n,catalog}.<lang>.js
 *
 *  node scripts/build.mjs           build once
 *  node scripts/build.mjs --watch   rebuild on change
 */
import { promises as fs } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import * as esbuild from 'esbuild';
import { compile, raw } from './template.mjs';
import { createTranslator, esc } from '../src/js/core/i18n.js';
import { formatPrice, formatNumber, toLocaleDigits, formatDate } from '../src/js/core/format.js';
import { localizeProduct, localizeCategory, sorters } from '../src/js/core/catalog.js';
import * as R from '../src/js/core/render.js';
import { productPageHTML, postPageHTML } from '../src/js/core/render-pages.js';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const ROOT = path.resolve(__dirname, '..');
const SRC = path.join(ROOT, 'src');
const WATCH = process.argv.includes('--watch');

const readJSON = async (p) => JSON.parse(await fs.readFile(p, 'utf8'));
const readText = (p) => fs.readFile(p, 'utf8');
const write = async (rel, content) => {
  const abs = path.join(ROOT, rel);
  await fs.mkdir(path.dirname(abs), { recursive: true });
  await fs.writeFile(abs, content);
};

/* ------------------------------------------------------------------ */
/* Page manifest                                                       */
/* ------------------------------------------------------------------ */
const PAGES = [
  { id: 'home', file: 'index.html', template: 'home', title: (L) => L.home.title, nav: 'home', bodyClass: 'page-home' },
  { id: 'shop', file: 'shop.html', template: 'shop', title: (L) => L.shop.title, nav: 'shop', crumbs: (L) => [[L.nav.shop, null]] },
  { id: 'search', file: 'search.html', template: 'search', title: (L) => L.search.title, crumbs: (L) => [[L.search.title, null]] },
  { id: 'product', file: 'product.html', template: 'product-dynamic', title: (L) => L.product.title, nav: 'shop', crumbs: (L) => [[L.nav.shop, 'shop.html'], [L.product.title, null]], vendor: ['photoswipe'] },
  { id: 'cart', file: 'cart.html', template: 'cart', title: (L) => L.cart.title, crumbs: (L) => [[L.cart.title, null]] },
  { id: 'checkout', file: 'checkout.html', template: 'checkout', title: (L) => L.checkout.title, crumbs: (L) => [[L.cart.title, 'cart.html'], [L.checkout.title, null]] },
  { id: 'wishlist', file: 'wishlist.html', template: 'wishlist', title: (L) => L.wishlist.title, crumbs: (L) => [[L.wishlist.title, null]] },
  { id: 'compare', file: 'compare.html', template: 'compare', title: (L) => L.compare.title, crumbs: (L) => [[L.compare.title, null]] },
  { id: 'login', file: 'login.html', template: 'login', title: (L) => L.auth.loginTitle, bodyClass: 'page-auth', minimal: true },
  { id: 'register', file: 'register.html', template: 'register', title: (L) => L.auth.registerTitle, bodyClass: 'page-auth', minimal: true },
  { id: 'forgot', file: 'forgot-password.html', template: 'forgot', title: (L) => L.auth.forgotTitle, bodyClass: 'page-auth', minimal: true },
  { id: 'account', file: 'account.html', template: 'account', title: (L) => L.account.dashboard, account: 'dashboard', crumbs: (L) => [[L.account.title, 'account.html'], [L.account.dashboard, null]] },
  { id: 'account-orders', file: 'account-orders.html', template: 'account-orders', title: (L) => L.account.orders, account: 'orders', crumbs: (L) => [[L.account.title, 'account.html'], [L.account.orders, null]] },
  { id: 'account-order', file: 'account-order.html', template: 'account-order', title: (L) => L.account.orderDetail, account: 'orders', crumbs: (L) => [[L.account.title, 'account.html'], [L.account.orders, 'account-orders.html'], [L.account.orderDetail, null]] },
  { id: 'account-wishlist', file: 'account-wishlist.html', template: 'account-wishlist', title: (L) => L.account.wishlist, account: 'wishlist', crumbs: (L) => [[L.account.title, 'account.html'], [L.account.wishlist, null]] },
  { id: 'account-addresses', file: 'account-addresses.html', template: 'account-addresses', title: (L) => L.account.addresses, account: 'addresses', crumbs: (L) => [[L.account.title, 'account.html'], [L.account.addresses, null]] },
  { id: 'account-profile', file: 'account-profile.html', template: 'account-profile', title: (L) => L.account.profile, account: 'profile', crumbs: (L) => [[L.account.title, 'account.html'], [L.account.profile, null]] },
  { id: 'blog', file: 'blog.html', template: 'blog', title: (L) => L.blog.heading, nav: 'blog', crumbs: (L) => [[L.nav.blog, null]] },
  { id: 'contact', file: 'contact.html', template: 'contact', title: (L) => L.nav.contact, nav: 'contact', crumbs: (L) => [[L.nav.contact, null]] },
  { id: 'about', file: 'about.html', template: 'about', title: (L) => L.nav.about, crumbs: (L) => [[L.nav.about, null]] },
  { id: 'faq', file: 'faq.html', template: 'faq', title: (L) => L.nav.faq, crumbs: (L) => [[L.nav.faq, null]] },
  { id: 'terms', file: 'terms.html', template: 'terms', title: (L) => L.nav.terms, crumbs: (L) => [[L.nav.terms, null]] },
  { id: '404', file: '404.html', template: '404', title: (L) => L.notFound.title, bodyClass: 'page-404' },
];

/* ------------------------------------------------------------------ */
/* Load sources                                                        */
/* ------------------------------------------------------------------ */
async function loadSources() {
  const [config, categories, brands, products, reviews, posts, fa, en] = await Promise.all([
    readJSON(path.join(SRC, 'config.json')),
    readJSON(path.join(SRC, 'data/categories.json')),
    readJSON(path.join(SRC, 'data/brands.json')),
    readJSON(path.join(SRC, 'data/products.json')),
    readJSON(path.join(SRC, 'data/reviews.json')),
    readJSON(path.join(SRC, 'data/posts.json')),
    readJSON(path.join(SRC, 'locales/fa.json')),
    readJSON(path.join(SRC, 'locales/en.json')),
  ]);
  const templates = {};
  const partials = {};
  const layoutSrc = await readText(path.join(SRC, 'templates/layouts/base.html'));
  for (const f of await fs.readdir(path.join(SRC, 'templates/partials'))) {
    partials[f.replace(/\.html$/, '')] = await readText(path.join(SRC, 'templates/partials', f));
  }
  for (const f of await fs.readdir(path.join(SRC, 'templates/pages'))) {
    templates[f.replace(/\.html$/, '')] = await readText(path.join(SRC, 'templates/pages', f));
  }
  const sprite = await readText(path.join(ROOT, 'assets/icons/sprite.svg'));
  return { config, categories, brands, products, reviews, posts, locales: { fa, en }, layoutSrc, templates, partials, sprite };
}

/* ------------------------------------------------------------------ */
/* Per-language model                                                  */
/* ------------------------------------------------------------------ */
function buildLocaleModel(lang, S) {
  const L = S.locales[lang];
  const t = createTranslator(L);
  const currency = S.config.locales[lang].currency;
  const categories = S.categories.map((c) => localizeCategory(c, lang));
  const products = S.products.map((p) => localizeProduct(p, lang, { categories: S.categories, brands: S.brands }));
  const byId = new Map(products.map((p) => [p.id, p]));
  const pick = (v) => (v && typeof v === 'object' && !Array.isArray(v) && lang in v ? v[lang] : v);

  const localizeReview = (r) => ({
    author: pick(r.author), rating: r.rating, date: r.date, dateLabel: formatDate(r.date, lang), verified: r.verified,
    title: pick(r.title), text: pick(r.text), pros: pick(r.pros) || [], cons: pick(r.cons) || [], helpful: r.helpful,
  });
  const reviewsFor = (id) => (S.reviews.byProduct[String(id)] || S.reviews.generic.slice(0, 3)).map(localizeReview);
  const testimonials = S.reviews.testimonials.map((r) => ({ name: pick(r.name), role: pick(r.role), rating: r.rating, text: pick(r.text), product: byId.get(r.product) }));

  const posts = S.posts.map((p) => ({
    id: p.id, slug: p.slug, title: pick(p.title), excerpt: pick(p.excerpt), category: { slug: p.category.slug, name: pick(p.category.name) },
    image: p.image, wide: p.wide || p.image, author: pick(p.author), date: p.date, dateLabel: pick(p.dateLabel) || formatDate(p.date, lang),
    readTime: p.readTime, views: p.views, comments: p.comments, tags: pick(p.tags) || [], featured: !!p.featured, body: pick(p.body), toc: pick(p.toc) || [],
  }));

  const lists = {
    featured: products.filter((p) => p.flags.featured).slice(0, 8),
    flash: products.filter((p) => p.flags.flash).sort(sorters.discount).slice(0, 8),
    newest: [...products].sort(sorters.newest).slice(0, 8),
    best: [...products].sort(sorters.best).slice(0, 8),
    topRated: [...products].sort(sorters.rating).slice(0, 4),
    trending: [...products].sort(sorters.popular).slice(0, 4),
    topDiscount: [...products].sort(sorters.discount).slice(0, 4),
    hero: products.filter((p) => p.flags.hero),
  };

  // Category product counts
  const counts = {};
  for (const p of products) { counts[p.category] = (counts[p.category] || 0) + 1; counts[p.subcategory] = (counts[p.subcategory] || 0) + 1; }
  for (const c of categories) { c.count = counts[c.slug] || 0; for (const ch of c.children) ch.count = counts[ch.slug] || 0; }
  const brandCounts = {};
  for (const p of products) brandCounts[p.brand] = (brandCounts[p.brand] || 0) + 1;
  const brands = S.brands.map((b) => ({ ...b, count: brandCounts[b.slug] || 0 }));

  for (const c of categories) {
    c.topProducts = products.filter((p) => p.category === c.slug).sort(sorters.best).slice(0, 3);
    c.brands = brands.filter((b) => b.category === c.slug);
  }
  const priceMin = Math.min(...products.map((p) => p.price));
  const priceMax = Math.max(...products.map((p) => p.price));

  const blogCats = {};
  for (const p of posts) { blogCats[p.category.slug] = blogCats[p.category.slug] || { ...p.category, count: 0 }; blogCats[p.category.slug].count++; }
  const blog = {
    featuredPost: posts.find((p) => p.featured) || posts[0],
    blogCategories: Object.values(blogCats),
    recentPosts: posts.slice(0, 4),
    blogTags: [...new Set(posts.flatMap((p) => p.tags))].slice(0, 14),
    posts: posts.map((p) => ({ ...p, tagsJoined: p.tags.join(',') })),
  };

  // Demo orders for the account area
  const statusMap = {
    processing: { cls: 'warning', label: L.account.statusProcessing },
    shipped: { cls: 'info', label: L.account.statusShipped },
    delivered: { cls: 'success', label: L.account.statusDelivered },
    cancelled: { cls: 'danger', label: L.account.statusCancelled },
    returned: { cls: 'muted', label: L.account.statusReturned },
  };
  const mkOrder = (code, date, status, itemDefs, { tracking = null, recent = false } = {}) => {
    const items = itemDefs.map(([id, qty]) => ({ product: byId.get(id), qty, lineTotal: byId.get(id).price * qty }));
    const subtotal = items.reduce((a, i) => a + (i.product.oldPrice || i.product.price) * i.qty, 0);
    const lineSum = items.reduce((a, i) => a + i.lineTotal, 0);
    const shipping = lineSum >= S.config.locales[lang].freeShipping ? 0 : 95000;
    return {
      code, date: formatDate(date, lang), iso: date, status, statusClass: statusMap[status].cls, statusLabel: statusMap[status].label, items, tracking, recent,
      subtotal, discount: subtotal - lineSum, shipping, total: lineSum + shipping,
      stepProcessing: ['shipped', 'delivered'].includes(status), stepShipped: ['shipped', 'delivered'].includes(status), stepDelivered: status === 'delivered',
      canCancel: status === 'processing',
    };
  };
  const orders = [
    mkOrder('NX-240912', '2026-08-30', 'processing', [[1001, 1], [1006, 2]], { recent: true }),
    mkOrder('NX-240871', '2026-08-25', 'shipped', [[2001, 1]], { tracking: 'RR 4521 8890 3 IR', recent: true }),
    mkOrder('NX-240790', '2026-08-14', 'delivered', [[3003, 1], [3004, 1]], { tracking: 'RR 4519 2201 7 IR', recent: true }),
    mkOrder('NX-240611', '2026-07-28', 'delivered', [[4001, 1]], { tracking: 'RR 4511 0092 1 IR' }),
    mkOrder('NX-240402', '2026-07-03', 'cancelled', [[1005, 1]]),
    mkOrder('NX-240188', '2026-06-11', 'delivered', [[5001, 1], [5002, 1]], { tracking: 'RR 4488 7712 4 IR' }),
  ];

  return { lang, L, t, currency, categories, brands, products, byId, reviewsFor, testimonials, posts, lists, priceMin, priceMax, blog, orders };
}

/* ------------------------------------------------------------------ */
/* Template helpers                                                    */
/* ------------------------------------------------------------------ */
function makeHelpers(M, ctxFor) {
  return {
    /* text helpers (escaped unless used with triple braces) */
    t: (key, named) => M.t(key, named),
    img: (p, named, scope) => `${scope.root}assets/img/${p}`,
    url: (p, named, scope) => `${scope.root}${p}`,
    price: (base, named, scope) => formatPrice(base, M.lang, M.currency, named || {}),
    num: (n) => formatNumber(n, M.lang),
    digits: (s) => toLocaleDigits(s, M.lang),
    date: (iso) => formatDate(iso, M.lang),
    json: (v) => JSON.stringify(v),
    crumbPos: (n) => Number(n) + 1,
    add: (a, named) => Number(a) + Number(named?.n ?? 0),
    attr: (v) => esc(v),
    /* HTML helpers (never escaped) */
    icon: (name, named) => raw(R.icon(name, named?.size || '', named?.class || '')),
    svg: (id, named) => raw(R.svgIcon(id, named?.class || '')),
    cards: (list, named, scope) => raw((list || []).map((p, i) => R.productCardHTML(ctxFor(scope), p, { flash: !!named?.flash, priority: i < 2 && !!named?.priority })).join('\n')),
    slides: (list, named, scope) => raw((list || []).map((p) => `<div class="swiper-slide">${R.productCardHTML(ctxFor(scope), p, { flash: !!named?.flash })}</div>`).join('\n')),
    minis: (list, named, scope) => raw((list || []).map((p, i) => R.productMiniHTML(ctxFor(scope), p, named?.ranked ? i + 1 : 0)).join('\n')),
    rating: (rating, named, scope) => raw(R.ratingHTML(ctxFor(scope), Number(rating), named?.count != null ? Number(named.count) : null, named || {})),
    priceTag: (p, named, scope) => raw(R.priceHTML(ctxFor(scope), p, named || {})),
    skeletons: (n) => raw(Array.from({ length: Number(n) || 4 }, R.productCardSkeletonHTML).join('')),
    productPage: (p, named, scope) => raw(productPageHTML(ctxFor(scope), p, M.reviewsFor(p.id), { related: M.products.filter((x) => x.category === p.category && x.id !== p.id).slice(0, 8) })),
    postPage: (post, named, scope) => raw(postPageHTML(ctxFor(scope), post, { posts: M.posts })),
    reviews: (list, named, scope) => raw((list || []).map((r) => R.reviewHTML(ctxFor(scope), r)).join('')),
    empty: (named, scope) => raw(R.emptyHTML(ctxFor(scope), named)),
  };
}

/* ------------------------------------------------------------------ */
/* Render one page                                                     */
/* ------------------------------------------------------------------ */
function renderPage({ S, M, page, root, langRoot, extra = {}, compiled }) {
  const { L, lang } = M;
  const dir = L.meta.dir;
  const ctxFor = (scope) => R.createCtx({ lang, dir, root: scope.root, t: M.t, currency: M.currency });
  const title = typeof page.title === 'function' ? page.title(L) : page.title;
  const fullTitle = page.id === 'home' ? title : `${title}${L.meta.titleSuffix}`;
  const crumbs = typeof page.crumbs === 'function' ? page.crumbs(L) : page.crumbs || [];
  const otherLang = lang === 'fa' ? 'en' : 'fa';
  const otherRoot = otherLang === 'en' ? `${root}en/` : root;
  const relPath = extra.relPath || page.file; // e.g. product/slug.html
  const otherHref = `${otherRoot}${relPath}`;

  const scope = {
    L, lang, dir, root, langRoot, isRTL: dir === 'rtl', otherLang, otherLangHref: otherHref, otherRoot,
    page: { ...page, title, fullTitle, description: extra.description || L.meta.description, bodyClass: page.bodyClass || `page-${page.id}`, crumbs, path: relPath, canonical: `${S.config.site.domain}/${lang === 'en' ? 'en/' : ''}${relPath === 'index.html' ? '' : relPath}` },
    site: S.config.site, currency: M.currency,
    categories: M.categories, brands: M.brands, products: M.products, lists: M.lists, posts: M.posts, homePosts: M.posts.slice(0, 3), testimonials: M.testimonials,
    priceMin: M.priceMin, priceMax: M.priceMax,
    year: lang === 'fa' ? '۱۴۰۵' : '2026',
    walletBalance: formatPrice(450000, lang, M.currency),
    ...M.blog, orders: M.orders, order: M.orders[1],
    shopHref: `${root}shop.html`, cartHref: `${root}cart.html`, homeHref: `${root}index.html`, loginHref: `${root}login.html`,
    vendor: { swiper: true, photoswipe: (page.vendor || []).includes('photoswipe') || !!extra.photoswipe },
    sprite: S.sprite,
    ...extra,
  };
  scope.content = compiled.pages[page.template](scope);
  return compiled.layout(scope);
}

/* ------------------------------------------------------------------ */
/* Bundle runtime JS with esbuild                                      */
/* ------------------------------------------------------------------ */
async function bundleJS() {
  await esbuild.build({
    entryPoints: [path.join(SRC, 'js/main.js')],
    bundle: true,
    format: 'iife',
    target: ['es2019'],
    outfile: path.join(ROOT, 'assets/js/app.js'),
    sourcemap: false,
    minify: false,
    legalComments: 'none',
    banner: { js: '/* Nexora runtime — built from src/js (do not edit directly, run `npm run build`) */' },
  });
  await esbuild.build({
    entryPoints: [path.join(SRC, 'js/main.js')],
    bundle: true, format: 'iife', target: ['es2019'], minify: true, legalComments: 'none',
    outfile: path.join(ROOT, 'assets/js/app.min.js'),
  });
  // PhotoSwipe as a classic script so the template also works over file://
  await esbuild.build({
    stdin: {
      contents: `import PhotoSwipeLightbox from 'photoswipe/lightbox'; import PhotoSwipe from 'photoswipe'; window.PhotoSwipeLightbox = PhotoSwipeLightbox; window.PhotoSwipe = PhotoSwipe;`,
      resolveDir: ROOT, loader: 'js',
    },
    bundle: true, format: 'iife', target: ['es2019'], minify: true, legalComments: 'none',
    outfile: path.join(ROOT, 'assets/vendor/photoswipe/photoswipe.iife.min.js'),
  });
}

/* ------------------------------------------------------------------ */
/* Main build                                                          */
/* ------------------------------------------------------------------ */
async function build() {
  const started = Date.now();
  const S = await loadSources();
  const partialsCompiled = {};
  const compiled = { layout: null, pages: {} };
  const helpersByLang = {};

  const outputs = [];
  for (const lang of ['fa', 'en']) {
    const M = buildLocaleModel(lang, S);
    const ctxFor = (scope) => R.createCtx({ lang, dir: M.L.meta.dir, root: scope.root, t: M.t, currency: M.currency });
    const helpers = makeHelpers(M, ctxFor);
    helpersByLang[lang] = helpers;
    // compile templates per language (helpers are language bound)
    const partials = {};
    for (const [name, src] of Object.entries(S.partials)) {
      const fn = compile(src, { partials, helpers });
      partials[name] = (ctx) => fn(ctx);
    }
    const layout = compile(S.layoutSrc, { partials, helpers });
    const pages = {};
    for (const [name, src] of Object.entries(S.templates)) pages[name] = compile(src, { partials, helpers });
    const C = { layout, pages, partials };

    const langPrefix = lang === 'en' ? 'en/' : '';
    const rootFor = (depth) => (depth === 0 ? (lang === 'en' ? '../' : './') : (lang === 'en' ? '../../' : '../'));
    const langRootFor = (depth) => (depth === 0 ? './' : '../');
    // depth 0 = files directly in the lang root; depth 1 = product/ or blog/ subfolders

    for (const page of PAGES) {
      const html = renderPage({ S, M, page, root: rootFor(0), langRoot: langRootFor(0), compiled: C });
      outputs.push(write(`${langPrefix}${page.file}`, html));
    }
    // product pages
    for (const p of M.products) {
      const page = { id: 'product-static', template: 'product', title: p.name, nav: 'shop', bodyClass: 'page-product', crumbs: [[M.L.nav.shop, 'shop.html'], [p.categoryName, `shop.html?cat=${p.category}`], [p.name, null]], vendor: ['photoswipe'] };
      const html = renderPage({ S, M, page, root: rootFor(1), langRoot: langRootFor(1), compiled: C, extra: { product: p, relPath: `product/${p.slug}.html`, description: p.short } });
      outputs.push(write(`${langPrefix}product/${p.slug}.html`, html));
    }
    // blog posts
    for (const post of M.posts) {
      const page = { id: 'post', template: 'post', title: post.title, nav: 'blog', bodyClass: 'page-post', crumbs: [[M.L.nav.blog, 'blog.html'], [post.category.name, `blog.html?cat=${post.category.slug}`], [post.title, null]] };
      const html = renderPage({ S, M, page, root: rootFor(1), langRoot: langRootFor(1), compiled: C, extra: { post, relPath: `blog/${post.slug}.html`, description: post.excerpt } });
      outputs.push(write(`${langPrefix}blog/${post.slug}.html`, html));
    }
    // single-post.html convenience copy (featured post) at lang root
    {
      const post = M.posts.find((p) => p.featured) || M.posts[0];
      const page = { id: 'post', template: 'post', title: post.title, nav: 'blog', bodyClass: 'page-post', crumbs: [[M.L.nav.blog, 'blog.html'], [post.category.name, `blog.html?cat=${post.category.slug}`], [post.title, null]] };
      const html = renderPage({ S, M, page, root: rootFor(0), langRoot: langRootFor(0), compiled: C, extra: { post, relPath: 'single-post.html', description: post.excerpt } });
      outputs.push(write(`${langPrefix}single-post.html`, html));
    }

    // runtime data files
    const runtimeProducts = M.products.map(({ description, specs, ...rest }) => rest);
    const catalog = {
      lang, currency: M.currency, config: { ...S.config.site, ...S.config.locales[lang], currency: undefined },
      categories: M.categories, brands: M.brands, products: runtimeProducts,
      reviews: Object.fromEntries(Object.keys(S.reviews.byProduct).map((id) => [id, M.reviewsFor(id)])),
      genericReviews: M.reviewsFor('__none__'),
      posts: M.posts.map(({ body, ...rest }) => rest),
      priceMin: M.priceMin, priceMax: M.priceMax,
    };
    outputs.push(write(`assets/js/catalog.${lang}.js`, `/* generated by scripts/build.mjs */\nwindow.NEXORA_CATALOG = ${JSON.stringify(catalog)};\n`));
    outputs.push(write(`assets/js/i18n.${lang}.js`, `/* generated by scripts/build.mjs */\nwindow.NEXORA_I18N = ${JSON.stringify(M.L)};\n`));
  }
  await Promise.all(outputs);
  await bundleJS();
  // sitemap + robots
  const S2 = S;
  const urls = [];
  for (const lang of ['fa', 'en']) {
    const prefix = `${S2.config.site.domain}/${lang === 'en' ? 'en/' : ''}`;
    for (const p of PAGES) if (!['404', 'account-order'].includes(p.id) && !p.id.startsWith('account')) urls.push(prefix + (p.file === 'index.html' ? '' : p.file));
    for (const p of S2.products) urls.push(`${prefix}product/${p.slug}.html`);
    for (const p of S2.posts) urls.push(`${prefix}blog/${p.slug}.html`);
  }
  await write('sitemap.xml', `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n${urls.map((u) => `  <url><loc>${esc(u)}</loc></url>`).join('\n')}\n</urlset>\n`);
  await write('robots.txt', `User-agent: *\nAllow: /\nDisallow: /account\nDisallow: /checkout.html\nSitemap: ${S2.config.site.domain}/sitemap.xml\n`);
  console.log(`✔ built ${outputs.length} files in ${Date.now() - started}ms`);
}

async function main() {
  await build();
  if (!WATCH) return;
  const { watch } = await import('node:fs');
  let timer = null;
  const trigger = () => { clearTimeout(timer); timer = setTimeout(() => build().catch((e) => console.error(e)), 150); };
  for (const dir of ['src']) watch(path.join(ROOT, dir), { recursive: true }, trigger);
  console.log('… watching src/ for changes');
}

main().catch((e) => { console.error(e); process.exit(1); });
