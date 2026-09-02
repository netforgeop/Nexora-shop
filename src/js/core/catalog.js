/**
 * Catalog normalisation shared by the build (Node) and the browser.
 * Raw data keeps `{ fa, en }` objects; `localizeProduct()` flattens them for a
 * given language and derives everything the UI needs (discount, images, urls).
 */
import { discountPercent } from './format.js';

const pick = (v, lang) => (v && typeof v === 'object' && !Array.isArray(v) && (lang in v) ? v[lang] : v);

export function localizeCategory(cat, lang) {
  return {
    slug: cat.slug,
    name: pick(cat.name, lang),
    icon: cat.icon,
    image: cat.image,
    tile: cat.tile,
    promo: cat.promo ? { ...cat.promo, title: pick(cat.promo.title, lang), badge: pick(cat.promo.badge, lang) } : null,
    children: (cat.children || []).map((c) => ({ slug: c.slug, name: pick(c.name, lang) })),
  };
}

export function localizeProduct(p, lang, { categories = [], brands = [] } = {}) {
  const category = categories.find((c) => c.slug === p.category);
  const sub = category?.children?.find((c) => c.slug === p.subcategory);
  const brand = brands.find((b) => b.slug === p.brand);
  const images = [1, 2, 3, 4].map((i) => `products/${p.slug}-${i}.webp`);
  return {
    id: p.id,
    slug: p.slug,
    sku: p.sku,
    name: pick(p.name, lang),
    category: p.category,
    categoryName: category ? pick(category.name, lang) : p.category,
    subcategory: p.subcategory,
    subcategoryName: sub ? pick(sub.name, lang) : '',
    brand: p.brand,
    brandName: brand ? brand.name : p.brand,
    price: p.price,
    oldPrice: p.oldPrice || null,
    discount: discountPercent(p.price, p.oldPrice),
    rating: p.rating,
    reviewCount: p.reviewCount,
    sold: p.sold,
    views: p.views,
    stock: p.stock,
    maxQty: p.maxQty || 5,
    inStock: p.stock > 0,
    lowStock: p.stock > 0 && p.stock <= 5,
    flags: p.flags || {},
    colors: (p.colors || []).map((c) => ({ name: pick(c.name, lang), hex: c.hex })),
    sizes: p.sizes || [],
    short: pick(p.short, lang),
    description: pick(p.description, lang),
    highlights: pick(p.highlights, lang) || [],
    specs: pick(p.specs, lang) || [],
    tags: pick(p.tags, lang) || [],
    images,
    image: images[0],
    imageHover: images[1],
    createdAt: p.createdAt,
    isNew: !!p.flags?.new,
  };
}

/** Sort helpers used by the shop page and the collections. */
export const sorters = {
  newest: (a, b) => (b.createdAt > a.createdAt ? 1 : b.createdAt < a.createdAt ? -1 : b.id - a.id),
  popular: (a, b) => b.views - a.views,
  best: (a, b) => b.sold - a.sold,
  rating: (a, b) => b.rating - a.rating || b.reviewCount - a.reviewCount,
  priceAsc: (a, b) => a.price - b.price,
  priceDesc: (a, b) => b.price - a.price,
  discount: (a, b) => b.discount - a.discount,
};

/**
 * Filter products.
 * @param {Array} products localized products
 * @param {object} f { q, cats:[], brands:[], min, max, rating, colors:[], inStock, discount }
 */
export function filterProducts(products, f = {}) {
  const q = (f.q || '').trim().toLowerCase();
  const terms = q ? q.split(/\s+/) : [];
  return products.filter((p) => {
    if (f.cats?.length && !f.cats.includes(p.category) && !f.cats.includes(p.subcategory)) return false;
    if (f.brands?.length && !f.brands.includes(p.brand)) return false;
    if (f.min != null && p.price < f.min) return false;
    if (f.max != null && p.price > f.max) return false;
    if (f.rating && p.rating < f.rating) return false;
    if (f.colors?.length && !p.colors.some((c) => f.colors.includes(c.hex))) return false;
    if (f.inStock && !p.inStock) return false;
    if (f.discount && !p.discount) return false;
    if (terms.length) {
      const hay = `${p.name} ${p.brandName} ${p.categoryName} ${p.subcategoryName} ${p.tags.join(' ')} ${p.sku}`.toLowerCase();
      if (!terms.every((t) => hay.includes(t))) return false;
    }
    return true;
  });
}

export function searchProducts(products, q, limit = 6) {
  const query = (q || '').trim().toLowerCase();
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
