/**
 * Persistent client-side state: cart, wishlist, compare, recently viewed,
 * demo auth session, coupon. Backed by localStorage; a tiny pub/sub notifies
 * UI components (header badges, mini cart, pages) on every change.
 */
const NS = 'nexora:v1';
const listeners = new Set();

const defaults = () => ({
  cart: [],          // { key, id, qty, color, colorHex, size }
  wishlist: [],      // [id]
  compare: [],       // [id]
  recent: [],        // [id]
  coupon: null,      // 'NEXORA10'
  user: null,        // { name, email }
  addresses: null,   // null = use demo defaults
});

let state = load();

function load() {
  try {
    const raw = localStorage.getItem(NS);
    return raw ? { ...defaults(), ...JSON.parse(raw) } : defaults();
  } catch {
    return defaults();
  }
}

function persist() {
  try { localStorage.setItem(NS, JSON.stringify(state)); } catch { /* private mode */ }
}

function emit(type, payload) {
  for (const fn of listeners) fn({ type, payload, state });
}

export function subscribe(fn) {
  listeners.add(fn);
  return () => listeners.delete(fn);
}

export function getState() {
  return state;
}

/* keep tabs in sync */
window.addEventListener('storage', (e) => {
  if (e.key === NS) { state = load(); emit('sync'); }
});

/* ---------------- Cart ---------------- */
export const cartKey = (id, color, size) => [id, color || '', size || ''].join('|');

export function cartAdd({ id, qty = 1, color = '', colorHex = '', size = '', max = 5 }) {
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
  emit('cart:add', { id, key, clamped });
  return { key, clamped };
}

export function cartSetQty(key, qty, max = 5) {
  const item = state.cart.find((i) => i.key === key);
  if (!item) return;
  const q = Math.max(1, Math.min(max, Number(qty) || 1));
  if (item.qty === q) return;
  item.qty = q;
  persist();
  emit('cart:update', { key, qty: q });
}

export function cartRemove(key) {
  const idx = state.cart.findIndex((i) => i.key === key);
  if (idx === -1) return null;
  const [removed] = state.cart.splice(idx, 1);
  persist();
  emit('cart:remove', removed);
  return removed;
}

export function cartRestore(item) {
  if (!item || state.cart.some((i) => i.key === item.key)) return;
  state.cart.push(item);
  persist();
  emit('cart:add', { id: item.id, key: item.key });
}

export function cartClear() {
  state.cart = [];
  state.coupon = null;
  persist();
  emit('cart:clear');
}

export const cartCount = () => state.cart.reduce((a, i) => a + i.qty, 0);
export const cartHas = (id) => state.cart.some((i) => i.id === id);

/* ---------------- Coupon ---------------- */
export function setCoupon(code) {
  state.coupon = code || null;
  persist();
  emit('coupon', state.coupon);
}

/* ---------------- Wishlist ---------------- */
export function wishlistToggle(id) {
  const idx = state.wishlist.indexOf(id);
  const added = idx === -1;
  if (added) state.wishlist.unshift(id); else state.wishlist.splice(idx, 1);
  persist();
  emit('wishlist', { id, added });
  return added;
}
export const wishlistHas = (id) => state.wishlist.includes(id);
export function wishlistClear() { state.wishlist = []; persist(); emit('wishlist', { cleared: true }); }
export function wishlistRemove(id) { const i = state.wishlist.indexOf(id); if (i > -1) { state.wishlist.splice(i, 1); persist(); emit('wishlist', { id, added: false }); } }

/* ---------------- Compare ---------------- */
export function compareToggle(id, limit = 4) {
  const idx = state.compare.indexOf(id);
  if (idx > -1) { state.compare.splice(idx, 1); persist(); emit('compare', { id, added: false }); return { added: false }; }
  if (state.compare.length >= limit) return { added: false, limit: true };
  state.compare.push(id);
  persist();
  emit('compare', { id, added: true });
  return { added: true };
}
export const compareHas = (id) => state.compare.includes(id);
export function compareClear() { state.compare = []; persist(); emit('compare', { cleared: true }); }
export function compareRemove(id) { const i = state.compare.indexOf(id); if (i > -1) { state.compare.splice(i, 1); persist(); emit('compare', { id, added: false }); } }

/* ---------------- Recently viewed ---------------- */
export function recentPush(id, limit = 8) {
  state.recent = [id, ...state.recent.filter((x) => x !== id)].slice(0, limit);
  persist();
}

/* ---------------- Demo auth ---------------- */
export function login(user) { state.user = user; persist(); emit('auth', user); }
export function logout() { state.user = null; persist(); emit('auth', null); }
export const isLoggedIn = () => !!state.user;

/* ---------------- Addresses (demo) ---------------- */
export function setAddresses(list) { state.addresses = list; persist(); emit('addresses', list); }
