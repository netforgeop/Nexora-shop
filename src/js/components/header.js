/**
 * Header: announcement bar, sticky behaviour, category megamenu, nav
 * dropdown keyboard support, mini cart toggle, language switch preservation.
 */
import { $, $$, on, throttle, mq } from '../core/dom.js';

export function initHeader(ctx) {
  const header = $('[data-header]');
  if (!header) return;

  /* ---- Announcement ---- */
  const ann = $('[data-announcement]');
  if (ann) {
    try { if (localStorage.getItem('nx:announcement') === 'dismissed') ann.classList.add('is-dismissed'); } catch { /* noop */ }
    $('[data-announcement-close]', ann)?.addEventListener('click', () => {
      ann.classList.add('is-dismissed');
      try { localStorage.setItem('nx:announcement', 'dismissed'); } catch { /* noop */ }
    });
  }

  /* ---- Sticky header (desktop + mobile) ---- */
  if (!header.hasAttribute('data-no-sticky')) {
    const main = $('.header-main', header);
    let stuck = false;
    let lastY = window.scrollY;
    const threshold = () => (main ? main.offsetTop + main.offsetHeight + 40 : 200);
    const update = () => {
      const y = window.scrollY;
      const shouldStick = y > threshold();
      if (shouldStick !== stuck) {
        stuck = shouldStick;
        if (stuck) header.style.setProperty('--stuck-offset', `${main.offsetHeight}px`);
        header.classList.toggle('is-stuck', stuck);
        if (!stuck) header.classList.remove('is-hidden');
      }
      // hide on scroll down / show on scroll up (mobile only)
      if (stuck && mq('(max-width: 991.98px)').matches) {
        header.classList.toggle('is-hidden', y > lastY && y - lastY > 4);
      }
      lastY = y;
    };
    window.addEventListener('scroll', throttle(update, 80), { passive: true });
    update();
  }

  /* ---- Category megamenu (hover on desktop, click to pin) ---- */
  const catMenu = $('[data-cat-menu]');
  if (catMenu) {
    const trigger = $('[data-cat-menu-trigger]', catMenu);
    const pinned = catMenu.classList.contains('is-pinned');
    const setOpen = (open) => { catMenu.classList.toggle('is-open', open); trigger.setAttribute('aria-expanded', String(open)); };
    trigger.addEventListener('click', () => setOpen(!catMenu.classList.contains('is-open')));
    if (!pinned) {
      let hoverTimer;
      catMenu.addEventListener('mouseenter', () => { if (mq('(hover: hover)').matches) { clearTimeout(hoverTimer); setOpen(true); } });
      catMenu.addEventListener('mouseleave', () => { if (mq('(hover: hover)').matches) hoverTimer = setTimeout(() => setOpen(false), 180); });
    }
    document.addEventListener('click', (e) => { if (!catMenu.contains(e.target) && !pinned) setOpen(false); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && catMenu.classList.contains('is-open') && !pinned) { setOpen(false); trigger.focus(); } });
    // keyboard: arrow navigation across category links
    catMenu.addEventListener('keydown', (e) => {
      if (!['ArrowDown', 'ArrowUp'].includes(e.key)) return;
      const links = $$('.cat-menu__link', catMenu);
      const idx = links.indexOf(document.activeElement);
      if (idx === -1) return;
      e.preventDefault();
      links[(idx + (e.key === 'ArrowDown' ? 1 : -1) + links.length) % links.length].focus();
    });
    // un-pin the home megamenu once the user scrolls past the hero
    if (pinned) {
      const unpin = () => { if (window.scrollY > 120) { catMenu.classList.remove('is-pinned'); setOpen(false); window.removeEventListener('scroll', unpin); } };
      window.addEventListener('scroll', unpin, { passive: true });
    }
  }

  /* ---- Nav dropdowns: keyboard + touch ---- */
  $$('[data-nav-dropdown]').forEach((item) => {
    const link = $('.nav__link', item);
    const setOpen = (open) => { item.classList.toggle('is-open', open); link.setAttribute('aria-expanded', String(open)); };
    link.addEventListener('click', (e) => {
      if (!mq('(hover: hover)').matches && !item.classList.contains('is-open')) { e.preventDefault(); setOpen(true); }
    });
    link.addEventListener('keydown', (e) => { if (e.key === 'ArrowDown' || e.key === ' ') { e.preventDefault(); setOpen(true); $('.dropdown__link', item)?.focus(); } });
    item.addEventListener('keydown', (e) => { if (e.key === 'Escape') { setOpen(false); link.focus(); } });
    item.addEventListener('focusout', (e) => { if (!item.contains(e.relatedTarget)) setOpen(false); });
    document.addEventListener('click', (e) => { if (!item.contains(e.target)) setOpen(false); });
  });

  /* ---- Mini cart toggle (click on desktop keeps it open; touch navigates) ---- */
  const wrap = $('[data-mini-cart-wrap]');
  if (wrap) {
    const toggle = $('[data-mini-cart-toggle]', wrap);
    toggle.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowDown') { e.preventDefault(); wrap.classList.add('is-open'); toggle.setAttribute('aria-expanded', 'true'); $('a, button', $('[data-mini-cart]', wrap))?.focus(); }
    });
    wrap.addEventListener('keydown', (e) => { if (e.key === 'Escape') { wrap.classList.remove('is-open'); toggle.setAttribute('aria-expanded', 'false'); toggle.focus(); } });
    wrap.addEventListener('focusout', (e) => { if (!wrap.contains(e.relatedTarget)) { wrap.classList.remove('is-open'); toggle.setAttribute('aria-expanded', 'false'); } });
  }

  /* ---- Language switch: keep query string ---- */
  $$('[data-lang-switch]').forEach((a) => {
    a.addEventListener('click', () => {
      const url = new URL(a.href, location.href);
      url.search = location.search;
      url.hash = location.hash;
      a.href = url.toString();
      try { localStorage.setItem('nx:lang', a.getAttribute('hreflang')); } catch { /* noop */ }
    });
  });

  /* ---- Back to top ---- */
  const btt = document.getElementById('back-to-top');
  if (btt) {
    window.addEventListener('scroll', throttle(() => btt.classList.toggle('is-visible', window.scrollY > 600), 120), { passive: true });
    btt.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  }

  /* ---- Page progress bar ---- */
  const prog = document.getElementById('page-progress');
  if (prog) {
    prog.style.inlineSize = '70%';
    window.addEventListener('load', () => { prog.classList.add('is-done'); setTimeout(() => prog.remove(), 600); }, { once: true });
    setTimeout(() => { prog.classList.add('is-done'); }, 2500);
  }
}
