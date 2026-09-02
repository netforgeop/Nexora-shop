/** Countdown timers, reveal-on-scroll, tabs, newsletter forms, generic form validation. */
import { $, $$, on, prefersReducedMotion } from '../core/dom.js';
import { pad2 } from '../core/format.js';
import { toast } from './toast.js';

/* ---------------- Countdown ---------------- */
export function initCountdowns(ctx) {
  $$('[data-countdown]').forEach((el) => {
    const hours = Number(el.dataset.countdownHours || 24);
    // deterministic end time per day so the timer looks realistic on reload
    const key = 'nx:countdown-end';
    let end = Number(sessionStorage.getItem(key));
    if (!end || end < Date.now()) { end = Date.now() + hours * 3600 * 1000; sessionStorage.setItem(key, String(end)); }
    const d = $('[data-cd="d"]', el); const h = $('[data-cd="h"]', el); const m = $('[data-cd="m"]', el); const s = $('[data-cd="s"]', el);
    const tick = () => {
      let diff = Math.max(0, end - Date.now());
      const days = Math.floor(diff / 86400000); diff -= days * 86400000;
      const hrs = Math.floor(diff / 3600000); diff -= hrs * 3600000;
      const mins = Math.floor(diff / 60000); diff -= mins * 60000;
      const secs = Math.floor(diff / 1000);
      if (d) d.textContent = ctx.digits(pad2(days));
      if (h) h.textContent = ctx.digits(pad2(d ? hrs : hrs + days * 24));
      if (m) m.textContent = ctx.digits(pad2(mins));
      if (s) s.textContent = ctx.digits(pad2(secs));
      if (end - Date.now() <= 0) { el.classList.add('is-expired'); clearInterval(timer); }
    };
    tick();
    const timer = setInterval(tick, 1000);
  });
}

/* ---------------- Reveal on scroll ---------------- */
export function initReveal() {
  const nodes = $$('[data-reveal]');
  if (!nodes.length) return;
  if (prefersReducedMotion() || !('IntersectionObserver' in window)) { nodes.forEach((n) => n.classList.add('is-visible')); return; }
  const io = new IntersectionObserver((entries) => {
    entries.forEach((e) => { if (e.isIntersecting) { e.target.classList.add('is-visible'); io.unobserve(e.target); } });
  }, { rootMargin: '0px 0px -8% 0px', threshold: 0.05 });
  nodes.forEach((n) => io.observe(n));
}

/* ---------------- Tabs ---------------- */
export function initTabs(root = document) {
  $$('[data-tabs]', root).forEach((tabs) => {
    const list = $('[role="tablist"]', tabs);
    const btns = $$('[role="tab"]', list);
    const panels = btns.map((b) => document.getElementById(b.getAttribute('aria-controls'))).filter(Boolean);
    const activate = (btn, focus = false) => {
      btns.forEach((b) => { const sel = b === btn; b.setAttribute('aria-selected', String(sel)); b.tabIndex = sel ? 0 : -1; });
      panels.forEach((p) => { p.hidden = p.id !== btn.getAttribute('aria-controls'); });
      if (focus) btn.focus();
      tabs.dispatchEvent(new CustomEvent('tabs:change', { bubbles: true, detail: { tab: btn.dataset.tab } }));
    };
    btns.forEach((b) => {
      b.addEventListener('click', () => activate(b));
      b.addEventListener('keydown', (e) => {
        const i = btns.indexOf(b);
        const rtl = document.documentElement.dir === 'rtl';
        const nextKey = rtl ? 'ArrowLeft' : 'ArrowRight';
        const prevKey = rtl ? 'ArrowRight' : 'ArrowLeft';
        if (e.key === nextKey) { e.preventDefault(); activate(btns[(i + 1) % btns.length], true); }
        else if (e.key === prevKey) { e.preventDefault(); activate(btns[(i - 1 + btns.length) % btns.length], true); }
        else if (e.key === 'Home') { e.preventDefault(); activate(btns[0], true); }
        else if (e.key === 'End') { e.preventDefault(); activate(btns[btns.length - 1], true); }
      });
    });
    // deep link: #tab-reviews or [data-tab-jump]
    const jump = (name) => { const b = btns.find((x) => x.dataset.tab === name); if (b) { activate(b); tabs.scrollIntoView({ behavior: prefersReducedMotion() ? 'auto' : 'smooth', block: 'start' }); } };
    if (location.hash.startsWith('#tab-')) jump(location.hash.slice(5));
    on(document, 'click', '[data-tab-jump]', (e, a) => { e.preventDefault(); jump(a.dataset.tabJump); });
  });
}

/* ---------------- Newsletter ---------------- */
export function initNewsletter(ctx) {
  $$('[data-newsletter]').forEach((form) => {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const input = $('input[type="email"]', form);
      const err = $('.form-error', form);
      const ok = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(input.value.trim());
      form.querySelector('.form-group')?.classList.toggle('is-invalid', !ok);
      input.setAttribute('aria-invalid', String(!ok));
      if (err) err.textContent = ok ? '' : ctx.t('footer.newsletterError');
      if (!ok) { input.focus(); return; }
      const btn = $('button[type="submit"]', form);
      btn.classList.add('is-loading');
      setTimeout(() => {
        btn.classList.remove('is-loading');
        toast(ctx.t('footer.newsletterSuccess'), { type: 'success' });
        form.reset();
      }, 700);
    });
  });
}

/* ---------------- Generic form validation ---------------- */
const RULES = {
  phone: (v) => /^(\+?\d[\d\s-]{8,14}|0?9\d{9})$/.test(v.replace(/[۰-۹]/g, (d) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d))),
  email: (v) => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v),
  identifier: (v) => RULES.email(v) || RULES.phone(v),
  postal: (v) => /^\d{5}(-?\d{4,5})?$/.test(v.replace(/[۰-۹]/g, (d) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d))),
  password: (v) => v.length >= 8 && /[a-zA-Z]/.test(v) && /\d/.test(v),
  terms: (v, field) => field.checked,
  passwordMatch: (v, field) => v === (document.getElementById(field.dataset.match)?.value || ''),
};

export function validateForm(form, ctx) {
  let firstInvalid = null;
  $$('input, select, textarea', form).forEach((field) => {
    if (field.closest('[hidden]') || field.disabled) return;
    const group = field.closest('.form-group');
    const err = group ? $('.form-error', group) : null;
    const value = (field.value || '').trim();
    let msg = '';
    const required = field.required || field.hasAttribute('data-required');
    if (required && !value && field.type !== 'checkbox' && field.type !== 'radio') msg = ctx.t('checkout.errors.required');
    else if (field.type === 'checkbox' && field.dataset.validate === 'terms' && !field.checked) msg = ctx.t('checkout.errors.terms');
    else if (value && field.dataset.minlength && value.length < Number(field.dataset.minlength)) msg = ctx.t('checkout.errors.minLength', { n: ctx.digits(field.dataset.minlength) });
    else if (value && field.dataset.validate && RULES[field.dataset.validate] && !RULES[field.dataset.validate](value, field)) msg = ctx.t(`checkout.errors.${field.dataset.validate === 'identifier' ? 'email' : field.dataset.validate}`);
    if (field.type === 'radio' && field.required && !$(`input[name="${field.name}"]:checked`, form)) msg = ctx.t('checkout.errors.required');
    if (group) group.classList.toggle('is-invalid', !!msg);
    if (field.type !== 'radio') field.setAttribute('aria-invalid', msg ? 'true' : 'false');
    if (err) err.textContent = msg;
    if (msg && !firstInvalid) firstInvalid = field;
  });
  if (firstInvalid) { firstInvalid.focus(); toast(ctx.t('toast.formError'), { type: 'error' }); return false; }
  return true;
}

export function liveValidation(form, ctx) {
  form.addEventListener('input', (e) => {
    const field = e.target;
    const group = field.closest('.form-group');
    if (group?.classList.contains('is-invalid')) {
      // re-validate just this field by running the whole validator silently for the group
      const value = field.value.trim();
      let ok = true;
      if ((field.required || field.hasAttribute('data-required')) && !value) ok = false;
      else if (field.dataset.minlength && value.length < Number(field.dataset.minlength)) ok = false;
      else if (field.dataset.validate && RULES[field.dataset.validate] && !RULES[field.dataset.validate](value, field)) ok = false;
      if (ok) { group.classList.remove('is-invalid'); field.setAttribute('aria-invalid', 'false'); const err = $('.form-error', group); if (err) err.textContent = ''; }
    }
  });
}

/* ---------------- Password toggles + strength ---------------- */
export function initPasswordTools(ctx) {
  on(document, 'click', '[data-toggle-password]', (e, btn) => {
    const input = btn.parentElement.querySelector('input');
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.setAttribute('aria-pressed', String(show));
    btn.setAttribute('aria-label', ctx.t(show ? 'auth.hidePassword' : 'auth.showPassword'));
    btn.innerHTML = `<span class="icon icon--sm linear-icon-${show ? 'eye-crossed' : 'eye'}" aria-hidden="true"></span>`;
  });
  $$('[data-password-strength]').forEach((meter) => {
    const input = meter.closest('.form-group')?.querySelector('input[type="password"], input[name="password"]');
    const text = $('[data-strength-text]', meter);
    input?.addEventListener('input', () => {
      const v = input.value;
      let score = 0;
      if (v.length >= 8) score++;
      if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
      if (/\d/.test(v)) score++;
      if (/[^A-Za-z0-9]/.test(v) && v.length >= 10) score++;
      if (!v) score = 0;
      meter.dataset.level = String(score);
      if (text) text.textContent = ctx.t(`auth.strength${score}`);
    });
  });
}

/* ---------------- Generic demo forms (contact / review / comment / profile) ---------------- */
export function initDemoForms(ctx) {
  const selectors = ['[data-contact-form]', '[data-review-form]', '[data-comment-form]', '[data-profile-form]', '[data-password-form]', '[data-qa-form]'];
  $$(selectors.join(',')).forEach((form) => {
    liveValidation(form, ctx);
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      if (!validateForm(form, ctx)) return;
      const btn = $('button[type="submit"]', form);
      btn?.classList.add('is-loading');
      setTimeout(() => {
        btn?.classList.remove('is-loading');
        const key = form.hasAttribute('data-review-form') ? 'product.reviewSubmitted' : form.hasAttribute('data-comment-form') ? 'blog.commentSubmitted' : 'toast.saved';
        toast(ctx.t(key), { type: 'success', duration: 5000 });
        if (!form.hasAttribute('data-profile-form')) form.reset();
        if (form.hasAttribute('data-review-form')) { form.hidden = true; $('[data-review-toggle]')?.setAttribute('aria-expanded', 'false'); }
      }, 800);
    });
  });
  on(document, 'click', '[data-review-toggle]', (e, btn) => {
    const form = document.getElementById(btn.getAttribute('aria-controls'));
    if (!form) return;
    const open = form.hidden;
    form.hidden = !open;
    btn.setAttribute('aria-expanded', String(open));
    if (open) $('input, textarea', form)?.focus();
  });
  on(document, 'click', '[data-review-cancel]', (e, btn) => { const form = btn.closest('form'); form.hidden = true; $('[data-review-toggle]')?.setAttribute('aria-expanded', 'false'); });
  on(document, 'click', '[data-helpful]', (e, btn) => { btn.disabled = true; btn.style.borderColor = 'var(--color-primary)'; toast(ctx.t('toast.saved'), { type: 'success' }); });
  on(document, 'click', '[data-social-login]', () => toast(ctx.t('common.loading'), { type: 'info' }));
  on(document, 'click', '[data-delete-account]', () => { if (confirm(ctx.t('account.deleteAccountText'))) toast(ctx.t('toast.saved'), { type: 'success' }); });
  on(document, 'click', '[data-invoice], [data-cancel-order], [data-return-order], [data-size-guide]', () => toast(ctx.t('toast.saved'), { type: 'info' }));
}
