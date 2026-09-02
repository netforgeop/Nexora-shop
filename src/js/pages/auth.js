/** Demo auth flows: login, register, forgot-password (OTP). */
import { $, $$ } from '../core/dom.js';
import * as store from '../store/state.js';
import { toast } from '../components/toast.js';
import { validateForm, liveValidation } from '../components/misc.js';

export function initAuth(ctx) {
  $$('[data-auth-form]').forEach((form) => {
    liveValidation(form, ctx);
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      if (!validateForm(form, ctx)) return;
      const kind = form.dataset.authForm;
      const btn = $('button[type="submit"]', form);
      btn.classList.add('is-loading');
      setTimeout(() => {
        btn.classList.remove('is-loading');
        if (kind === 'login') {
          const id = form.identifier.value.trim();
          store.login({ name: ctx.t('account.userName'), email: id.includes('@') ? id : ctx.t('account.userEmail') });
          toast(ctx.t('auth.loginSuccess'), { type: 'success' });
          setTimeout(() => { location.href = redirectTarget(ctx); }, 900);
        } else if (kind === 'register') {
          store.login({ name: form.name.value.trim(), email: form.email.value.trim() || ctx.t('account.userEmail') });
          toast(ctx.t('auth.registerSuccess'), { type: 'success' });
          setTimeout(() => { location.href = ctx.url('account.html'); }, 900);
        } else if (kind === 'forgot') {
          const target = form.identifier.value.trim();
          form.hidden = true;
          const otp = $('[data-forgot-step="2"]');
          otp.hidden = false;
          $('[data-otp-sub]', otp).textContent = ctx.t('auth.otpSub', { target });
          toast(ctx.t('auth.codeSent'), { type: 'success' });
          $('input', otp).focus();
          startResendTimer(ctx, $('[data-otp-resend]', otp));
        } else if (kind === 'otp') {
          const code = $$('[data-otp] input', form).map((i) => i.value).join('');
          if (code.length < 5) { $('.form-error', form).textContent = ctx.t('checkout.errors.required'); $('.form-error', form).classList.add('is-visible'); return; }
          form.hidden = true;
          $('[data-forgot-step="3"]').hidden = false;
          store.login({ name: ctx.t('account.userName'), email: ctx.t('account.userEmail') });
        }
      }, 800);
    });
  });

  // OTP inputs: auto-advance, paste, backspace
  const otp = $('[data-otp]');
  if (otp) {
    const inputs = $$('input', otp);
    inputs.forEach((inp, i) => {
      inp.addEventListener('input', () => { inp.value = inp.value.replace(/\D/g, '').slice(-1); if (inp.value && inputs[i + 1]) inputs[i + 1].focus(); });
      inp.addEventListener('keydown', (e) => { if (e.key === 'Backspace' && !inp.value && inputs[i - 1]) inputs[i - 1].focus(); });
      inp.addEventListener('paste', (e) => { const txt = (e.clipboardData.getData('text') || '').replace(/\D/g, ''); if (!txt) return; e.preventDefault(); txt.split('').slice(0, inputs.length).forEach((c, j) => { inputs[j].value = c; }); inputs[Math.min(txt.length, inputs.length) - 1].focus(); });
    });
  }

  // already logged in → account pages
  if (ctx.page === 'login' && store.isLoggedIn() && new URLSearchParams(location.search).get('force') !== '1') {
    // stay on the page (demo) but show hint
  }
}

function redirectTarget(ctx) {
  const next = new URLSearchParams(location.search).get('next');
  return next && /^[\w\-./?=&%]+$/.test(next) ? next : ctx.url('account.html');
}

function startResendTimer(ctx, btn) {
  if (!btn) return;
  let n = 60;
  btn.disabled = true;
  const tick = () => { btn.textContent = n > 0 ? ctx.t('auth.resendIn', { n: ctx.digits(n) }) : ctx.t('auth.resend'); if (n-- > 0) setTimeout(tick, 1000); else btn.disabled = false; };
  tick();
  btn.addEventListener('click', () => { toast(ctx.t('auth.codeSent'), { type: 'success' }); n = 60; btn.disabled = true; tick(); });
}
