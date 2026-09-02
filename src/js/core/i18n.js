/**
 * Tiny i18n helper shared by the build script (Node) and the browser runtime.
 * `t('cart.count', { n: 3 })` → looks up a dotted key inside the locale
 * dictionary and interpolates `{n}` placeholders.
 */
export function createTranslator(dict) {
  function lookup(key) {
    return key.split('.').reduce((obj, part) => (obj != null && obj[part] !== undefined ? obj[part] : undefined), dict);
  }
  return function t(key, params) {
    const value = lookup(key);
    let str = value === undefined ? key : String(value);
    if (params) {
      for (const [k, v] of Object.entries(params)) str = str.split(`{${k}}`).join(v == null ? '' : String(v));
    }
    return str;
  };
}

/** Escape a string for safe insertion into HTML. */
export function esc(value) {
  return String(value == null ? '' : value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}
