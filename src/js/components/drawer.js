/** Off-canvas drawers (mobile menu, filters) + generic <dialog> modals. */
import { $, $$, on, trapFocus, lockScroll } from '../core/dom.js';

let overlay;
let openDrawer = null;
let releaseTrap = null;
let lastFocus = null;

export function initDrawers() {
  overlay = $('[data-overlay]');
  if (overlay) overlay.hidden = false;

  on(document, 'click', '[data-drawer-open]', (e, btn) => {
    e.preventDefault();
    open(btn.getAttribute('data-drawer-open'), btn);
  });
  on(document, 'click', '[data-drawer-close]', (e, btn) => {
    const drawer = btn.closest('[data-drawer]');
    if (drawer) close();
  });
  overlay?.addEventListener('click', () => close());
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && openDrawer) close(); });

  /* Mobile nav tabs & sub-menus */
  on(document, 'click', '[data-mobile-tab]', (e, tab) => {
    const drawer = tab.closest('[data-drawer]');
    $$('[data-mobile-tab]', drawer).forEach((t) => { const sel = t === tab; t.setAttribute('aria-selected', String(sel)); t.tabIndex = sel ? 0 : -1; });
    $$('[data-mobile-panel]', drawer).forEach((p) => { p.hidden = p.dataset.mobilePanel !== tab.dataset.mobileTab; });
  });
  on(document, 'click', '[data-mobile-sub-toggle]', (e, btn) => {
    const sub = document.getElementById(btn.getAttribute('aria-controls'));
    const open = btn.getAttribute('aria-expanded') === 'true';
    btn.setAttribute('aria-expanded', String(!open));
    sub?.classList.toggle('is-open', !open);
  });

  /* Generic modal close */
  on(document, 'click', '[data-modal-close]', (e, btn) => { btn.closest('dialog')?.close(); });
  $$('dialog.modal').forEach((d) => {
    d.addEventListener('click', (e) => { if (e.target === d) d.close(); });
    d.addEventListener('close', () => lockScroll(false));
  });

  /* Close drawers when switching to desktop */
  window.matchMedia('(min-width: 992px)').addEventListener('change', (e) => { if (e.matches && openDrawer?.id === 'drawer-menu') close(); });
}

export function open(id, trigger) {
  const drawer = document.getElementById(id);
  if (!drawer || openDrawer === drawer) return;
  if (openDrawer) close(true);
  lastFocus = trigger || document.activeElement;
  drawer.inert = false;
  drawer.setAttribute('aria-hidden', 'false');
  drawer.classList.add('is-open');
  overlay?.classList.add('is-visible');
  lockScroll(true);
  openDrawer = drawer;
  trigger?.setAttribute('aria-expanded', 'true');
  releaseTrap = trapFocus(drawer);
  const first = $('[data-drawer-close], a, button, input', drawer);
  setTimeout(() => first?.focus(), 60);
  drawer.dispatchEvent(new CustomEvent('drawer:open', { bubbles: true }));
}

export function close(silent = false) {
  if (!openDrawer) return;
  const drawer = openDrawer;
  drawer.classList.remove('is-open');
  drawer.setAttribute('aria-hidden', 'true');
  drawer.inert = true;
  overlay?.classList.remove('is-visible');
  lockScroll(false);
  releaseTrap?.();
  releaseTrap = null;
  $$(`[data-drawer-open="${drawer.id}"]`).forEach((b) => b.setAttribute('aria-expanded', 'false'));
  openDrawer = null;
  if (!silent && lastFocus && document.contains(lastFocus)) lastFocus.focus();
  drawer.dispatchEvent(new CustomEvent('drawer:close', { bubbles: true }));
}

export function openModal(dialog) {
  if (!dialog) return;
  if (typeof dialog.showModal === 'function') { if (!dialog.open) dialog.showModal(); }
  else dialog.setAttribute('open', '');
  lockScroll(true);
}
