/**
 * Locale-aware number / price / date formatting.
 * Works in both Node (build time) and the browser.
 */

const PERSIAN_DIGITS = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

export function toLocaleDigits(str, lang) {
  if (lang !== 'fa') return String(str);
  return String(str).replace(/\d/g, (d) => PERSIAN_DIGITS[+d]);
}

export function formatNumber(value, lang, options = {}) {
  const n = Number(value) || 0;
  const locale = lang === 'fa' ? 'fa-IR' : 'en-US';
  const { decimals = 0 } = options;
  try {
    return new Intl.NumberFormat(locale, { maximumFractionDigits: decimals, minimumFractionDigits: 0, useGrouping: true }).format(n);
  } catch {
    return toLocaleDigits(n.toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ','), lang);
  }
}

/**
 * Convert a base price (stored in IRT) to the locale currency and format it.
 * @param {number} base   price in base currency (toman)
 * @param {string} lang   'fa' | 'en'
 * @param {object} currency { symbol, rate, position, decimals }
 * @param {object} [opts]  { symbol: boolean }
 */
export function formatPrice(base, lang, currency, opts = {}) {
  const { symbol = true } = opts;
  const converted = (Number(base) || 0) * (currency.rate || 1);
  const decimals = currency.decimals ?? 0;
  const rounded = decimals === 0 ? Math.round(converted) : Number(converted.toFixed(decimals));
  const num = formatNumber(rounded, lang, { decimals });
  if (!symbol) return num;
  return currency.position === 'before' ? `${currency.symbol}${num}` : `${num} ${currency.symbol}`;
}

export function discountPercent(price, oldPrice) {
  if (!oldPrice || oldPrice <= price) return 0;
  return Math.round(((oldPrice - price) / oldPrice) * 100);
}

export function formatDate(iso, lang) {
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return iso;
  try {
    if (lang === 'fa') return new Intl.DateTimeFormat('fa-IR', { year: 'numeric', month: 'long', day: 'numeric' }).format(d);
    return new Intl.DateTimeFormat('en-US', { year: 'numeric', month: 'short', day: 'numeric' }).format(d);
  } catch {
    return iso;
  }
}

export function pad2(n) {
  return String(n).padStart(2, '0');
}
