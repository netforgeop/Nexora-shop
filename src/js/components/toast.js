import { toastHTML } from '../core/render.js';
import { el } from '../core/dom.js';

let region;
let ctx;

export function initToasts(appCtx) {
  ctx = appCtx;
  region = document.getElementById('toast-region');
  if (!region) {
    region = el('div', { id: 'toast-region', class: 'toast-region', 'aria-live': 'polite' });
    document.body.append(region);
  }
  region.addEventListener('click', (e) => {
    const close = e.target.closest('[data-toast-close]');
    if (close) dismiss(close.closest('.toast'));
    const undo = e.target.closest('[data-toast-undo]');
    if (undo) { undo.dispatchEvent(new CustomEvent('toast:undo', { bubbles: true })); dismiss(undo.closest('.toast')); }
  });
}

export function toast(message, { type = 'default', action, actionHref, onUndo, duration = 4000 } = {}) {
  if (!region) return null;
  const wrap = document.createElement('div');
  wrap.innerHTML = toastHTML({ message, type, action, actionHref, closeLabel: ctx?.t('common.close') || 'Close' });
  const node = wrap.firstElementChild;
  if (onUndo) {
    const btn = el('button', { type: 'button', class: 'toast__action', 'data-toast-undo': '', text: ctx?.t('toast.undo') || 'Undo' });
    node.insertBefore(btn, node.querySelector('.toast__close'));
    node.addEventListener('toast:undo', onUndo, { once: true });
  }
  region.append(node);
  // keep at most 4 live toasts (dismiss() is async, so only count the ones not already leaving)
  const live = () => Array.from(region.children).filter((n) => !n.classList.contains('is-leaving'));
  while (live().length > 4) dismiss(live()[0]);
  const timer = setTimeout(() => dismiss(node), duration);
  node.addEventListener('mouseenter', () => clearTimeout(timer), { once: true });
  return node;
}

function dismiss(node) {
  if (!node || node.classList.contains('is-leaving')) return;
  node.classList.add('is-leaving');
  setTimeout(() => node.remove(), 260);
}
