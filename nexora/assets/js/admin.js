/**
 * Nexora admin UI: settings fields, presets editor, demo import, wizard, onboarding tour.
 * Loaded only on theme screens (see nexora_admin_assets). Depends on jQuery, wp-color-picker,
 * jquery-ui-sortable, wp-util, wp.media.
 *
 * @package Nexora
 */
/* global jQuery, wp, NEXORA_ADMIN */
(function ($) {
	'use strict';

	var CFG = window.NEXORA_ADMIN || {};
	var I18N = CFG.i18n || {};
	var root = document.getElementById('nexora-admin');
	if (!root) { return; }

	var $$ = function (sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); };
	var esc = function (s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };
	var debounce = function (fn, ms) { var t; return function () { var a = arguments, s = this; clearTimeout(t); t = setTimeout(function () { fn.apply(s, a); }, ms || 250); }; };

	function ajax(action, data, method) {
		data = $.extend({ action: action, nonce: CFG.nonce }, data || {});
		return $.ajax({ url: CFG.ajaxUrl, method: method || 'POST', data: data, dataType: 'json' }).then(function (r) {
			if (!r || !r.success) { return $.Deferred().reject(r && r.data && r.data.message ? r.data.message : I18N.error).promise(); }
			return r.data;
		}, function () { return $.Deferred().reject(I18N.error).promise(); });
	}

	function notice(message, type) {
		var box = document.createElement('div');
		box.className = 'nx-notice nx-notice--' + (type || 'info') + ' nx-notice--float';
		box.setAttribute('role', 'status');
		box.textContent = message;
		root.appendChild(box);
		setTimeout(function () { box.classList.add('is-leaving'); setTimeout(function () { box.remove(); }, 300); }, 3500);
	}

	/* ---------- Colour pickers ---------- */
	function initColors(ctx) {
		$('.nx-color', ctx).each(function () {
			if ($(this).hasClass('wp-color-picker')) { return; }
			var input = this;
			$(input).wpColorPicker({
				change: function (e, ui) { input.value = ui.color.toString(); input.dispatchEvent(new Event('nx:color', { bubbles: true })); },
				clear: function () { input.dispatchEvent(new Event('nx:color', { bubbles: true })); }
			});
		});
	}

	/* ---------- Media picker ---------- */
	function initImages(ctx) {
		$$('[data-image]', ctx).forEach(function (wrap) {
			if (wrap.dataset.ready) { return; }
			wrap.dataset.ready = '1';
			var input = wrap.querySelector('input[type="hidden"]'), preview = wrap.querySelector('.nx-image__preview'), remove = wrap.querySelector('[data-image-remove]');
			var frame;
			wrap.querySelector('[data-image-select]').addEventListener('click', function () {
				if (!window.wp || !wp.media) { return; }
				if (!frame) {
					frame = wp.media({ title: I18N.select, button: { text: I18N.select }, multiple: false, library: { type: 'image' } });
					frame.on('select', function () {
						var att = frame.state().get('selection').first().toJSON();
						var url = att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url;
						input.value = att.id;
						preview.innerHTML = '<img src="' + esc(url) + '" alt="">';
						preview.classList.add('has-image');
						remove.hidden = false;
						input.dispatchEvent(new Event('change', { bubbles: true }));
					});
				}
				frame.open();
			});
			remove.addEventListener('click', function () {
				input.value = '';
				preview.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21"/></svg>';
				preview.classList.remove('has-image');
				remove.hidden = true;
				input.dispatchEvent(new Event('change', { bubbles: true }));
			});
		});
	}

	/* ---------- Icon picker ---------- */
	var iconCache = null, iconPop = null;
	function initIconPickers(ctx) {
		$$('[data-iconpick]', ctx).forEach(function (wrap) {
			if (wrap.dataset.ready) { return; }
			wrap.dataset.ready = '1';
			var input = wrap.querySelector('input[type="hidden"]'), btn = wrap.querySelector('[data-iconpick-open]');
			btn.addEventListener('click', function () {
				var load = iconCache ? $.Deferred().resolve(iconCache).promise() : ajax('nexora_admin_icons', {}, 'GET').then(function (d) { iconCache = d; return d; });
				load.then(function (names) { openIconPop(wrap, input, btn, names); });
			});
		});
	}
	function openIconPop(wrap, input, btn, names) {
		closeIconPop();
		iconPop = document.createElement('div');
		iconPop.className = 'nx-iconpop';
		iconPop.innerHTML = '<input type="search" class="nx-iconpop__search" placeholder="' + esc(I18N.search) + '"><div class="nx-iconpop__grid"></div>';
		var grid = iconPop.querySelector('.nx-iconpop__grid'), search = iconPop.querySelector('input');
		var render = function (q) {
			grid.innerHTML = names.filter(function (n) { return !q || n.indexOf(q) > -1; }).map(function (n) {
				return '<button type="button" class="nx-iconpop__item' + (n === input.value ? ' is-active' : '') + '" data-icon="' + esc(n) + '" title="' + esc(n) + '"><span class="icon linear-icon-' + esc(n) + '"></span></button>';
			}).join('');
		};
		render('');
		search.addEventListener('input', debounce(function () { render(search.value.trim().toLowerCase()); }, 120));
		grid.addEventListener('click', function (e) {
			var item = e.target.closest('[data-icon]');
			if (!item) { return; }
			input.value = item.dataset.icon;
			btn.querySelector('.icon').className = 'icon linear-icon-' + item.dataset.icon;
			btn.querySelector('.nx-iconpick__name').textContent = item.dataset.icon;
			input.dispatchEvent(new Event('change', { bubbles: true }));
			closeIconPop();
		});
		wrap.appendChild(iconPop);
		search.focus();
		setTimeout(function () { document.addEventListener('click', outsideIconPop); }, 0);
	}
	function outsideIconPop(e) { if (iconPop && !iconPop.contains(e.target)) { closeIconPop(); } }
	function closeIconPop() { if (iconPop) { iconPop.remove(); iconPop = null; document.removeEventListener('click', outsideIconPop); } }

	/* ---------- Product search field ---------- */
	function initProducts(ctx) {
		$$('[data-products]', ctx).forEach(function (wrap) {
			if (wrap.dataset.ready) { return; }
			wrap.dataset.ready = '1';
			var input = wrap.querySelector('input[type="hidden"]'), list = wrap.querySelector('.nx-products__list'), search = wrap.querySelector('[data-products-search]'), results = wrap.querySelector('.nx-products__results');
			var sync = function () { input.value = $$('li[data-id]', list).map(function (li) { return li.dataset.id; }).join(','); };
			var add = function (id, text) {
				if (list.querySelector('li[data-id="' + id + '"]')) { return; }
				var li = document.createElement('li');
				li.dataset.id = id;
				li.innerHTML = '<span>' + esc(text) + '</span><button type="button" class="nx-x" data-remove aria-label="' + esc(I18N.remove) + '">&times;</button>';
				list.appendChild(li);
				sync();
			};
			list.addEventListener('click', function (e) { if (e.target.closest('[data-remove]')) { e.target.closest('li').remove(); sync(); } });
			$(list).sortable({ axis: 'y', update: sync });
			search.addEventListener('input', debounce(function () {
				var q = search.value.trim();
				if (q.length < 2) { results.hidden = true; return; }
				ajax('nexora_admin_product_search', { q: q }, 'GET').then(function (items) {
					results.innerHTML = items.length ? items.map(function (p) { return '<li><button type="button" data-id="' + p.id + '">' + esc(p.text) + '</button></li>'; }).join('') : '<li class="nx-muted">—</li>';
					results.hidden = false;
				});
			}, 250));
			results.addEventListener('click', function (e) {
				var b = e.target.closest('button[data-id]');
				if (!b) { return; }
				add(b.dataset.id, b.textContent);
				results.hidden = true;
				search.value = '';
			});
			document.addEventListener('click', function (e) { if (!wrap.contains(e.target)) { results.hidden = true; } });
		});
	}

	/* ---------- Sortables & repeaters ---------- */
	function initSortables(ctx) {
		$('[data-sortable]', ctx).each(function () { if (!$(this).data('ui-sortable')) { $(this).sortable({ axis: 'y', handle: '.nx-sortable__handle' }); } });
	}
	function initRepeaters(ctx) {
		$$('[data-repeater]', ctx).forEach(function (rep) {
			if (rep.dataset.ready) { return; }
			rep.dataset.ready = '1';
			var rows = rep.querySelector('[data-repeater-rows]'), tpl = rep.querySelector('.nx-repeater__tpl'), addBtn = rep.querySelector('[data-repeater-add]'), max = Number(rep.dataset.max || 50);
			var reindex = function () {
				$$('[data-repeater-row]', rows).forEach(function (row, i) {
					$$('[name]', row).forEach(function (el) { el.name = el.name.replace(/\[(\d+|__i__)\]/, '[' + i + ']'); });
				});
				addBtn.disabled = $$('[data-repeater-row]', rows).length >= max;
			};
			var bindRow = function (row) {
				var body = row.querySelector('.nx-repeater__body'), toggle = row.querySelector('[data-repeater-toggle]'), title = row.querySelector('[data-row-title]');
				toggle.addEventListener('click', function () { var open = body.hidden; body.hidden = !open; toggle.setAttribute('aria-expanded', String(open)); });
				row.querySelector('[data-repeater-remove]').addEventListener('click', function () { if (window.confirm(I18N.confirm)) { row.remove(); reindex(); } });
				var titleSrc = row.querySelector('.nx-field--text input, .nx-link input[type="text"]');
				if (titleSrc) { titleSrc.addEventListener('input', function () { title.textContent = titleSrc.value || title.dataset.fallback || '…'; }); }
				initFieldWidgets(row);
			};
			$$('[data-repeater-row]', rows).forEach(bindRow);
			$(rows).sortable({ axis: 'y', handle: '.nx-sortable__handle', update: reindex });
			addBtn.addEventListener('click', function () {
				var idx = $$('[data-repeater-row]', rows).length;
				if (idx >= max) { return; }
				var html = tpl.innerHTML.replace(/__i__/g, idx);
				var tmp = document.createElement('div');
				tmp.innerHTML = html;
				var row = tmp.firstElementChild;
				rows.appendChild(row);
				bindRow(row);
				row.querySelector('.nx-repeater__body').hidden = false;
				row.querySelector('[data-repeater-toggle]').setAttribute('aria-expanded', 'true');
				reindex();
				var f = row.querySelector('input, textarea, select');
				if (f) { f.focus(); }
			});
			reindex();
		});
	}
	function initFieldWidgets(ctx) {
		initColors(ctx);
		initImages(ctx);
		initIconPickers(ctx);
		initProducts(ctx);
		initSortables(ctx);
		initRepeaters(ctx);
		initConditions(ctx);
	}

	/* ---------- Conditional fields ---------- */
	function initConditions(ctx) {
		var fields = $$('[data-show-if]', ctx);
		if (!fields.length) { return; }
		var valueOf = function (key, scope) {
			var host = scope.querySelector('[data-key="' + key + '"]') || document.querySelector('[data-key="' + key + '"]');
			if (!host) { return null; }
			var cb = host.querySelector('input[type="checkbox"]');
			if (cb && host.classList.contains('nx-field--toggle')) { return cb.checked ? '1' : '0'; }
			var input = host.querySelector('select, input:not([type="hidden"]), input[type="hidden"]');
			return input ? input.value : null;
		};
		var update = function () {
			fields.forEach(function (f) {
				var scope = f.closest('[data-repeater-row]') || root;
				var v = valueOf(f.dataset.showIf, scope);
				var wanted = String(f.dataset.showValue).split(',');
				var show = v === null || wanted.indexOf(String(v)) > -1 || (wanted.indexOf('1') > -1 && (v === 'on' || v === 'true'));
				f.hidden = !show;
			});
		};
		update();
		root.addEventListener('change', update);
		root.addEventListener('input', update);
	}

	/* ---------- Settings page: sticky subnav, unsaved warning, confirm ---------- */
	function initSettingsPage() {
		var form = document.getElementById('nexora-settings-form');
		if (form) {
			var dirty = false;
			form.addEventListener('change', function () { dirty = true; });
			form.addEventListener('submit', function () { dirty = false; });
			window.addEventListener('beforeunload', function (e) { if (dirty) { e.preventDefault(); e.returnValue = ''; } });
			var links = $$('.nx-settings__subnav a'), sections = $$('.nx-section');
			if ('IntersectionObserver' in window && sections.length) {
				var io = new IntersectionObserver(function (entries) {
					entries.forEach(function (en) { if (en.isIntersecting) { links.forEach(function (a) { a.classList.toggle('is-active', a.getAttribute('href') === '#' + en.target.id); }); } });
				}, { rootMargin: '-30% 0px -60% 0px' });
				sections.forEach(function (s) { io.observe(s); });
			}
		}
		root.addEventListener('click', function (e) {
			var btn = e.target.closest('[data-confirm]');
			if (btn && !window.confirm(I18N.confirm)) { e.preventDefault(); e.stopImmediatePropagation(); }
		}, true);
		$$('[data-select-all]').forEach(function (ta) { ta.addEventListener('focus', function () { ta.select(); }); });
		$$('[data-filter-list]').forEach(function (input) {
			var items = $$(input.dataset.filterList);
			input.addEventListener('input', debounce(function () {
				var q = input.value.trim().toLowerCase();
				items.forEach(function (it) { it.hidden = q && (it.dataset.text || it.textContent).toLowerCase().indexOf(q) === -1; });
			}, 120));
		});
	}

	/* ---------- Presets editor: live preview + contrast ---------- */
	function hexToRgb(hex) {
		hex = String(hex || '').replace('#', '');
		if (hex.length === 3) { hex = hex.split('').map(function (c) { return c + c; }).join(''); }
		var n = parseInt(hex, 16);
		return isNaN(n) ? null : [(n >> 16) & 255, (n >> 8) & 255, n & 255];
	}
	function luminance(rgb) {
		var a = rgb.map(function (v) { v /= 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); });
		return 0.2126 * a[0] + 0.7152 * a[1] + 0.0722 * a[2];
	}
	function contrast(h1, h2) {
		var a = hexToRgb(h1), b = hexToRgb(h2);
		if (!a || !b) { return 0; }
		var l1 = luminance(a), l2 = luminance(b);
		return (Math.max(l1, l2) + 0.05) / (Math.min(l1, l2) + 0.05);
	}
	function shade(hex, pct) {
		var rgb = hexToRgb(hex);
		if (!rgb) { return hex; }
		return '#' + rgb.map(function (v) { v = Math.round(v * (1 - pct / 100)); return ('0' + Math.max(0, Math.min(255, v)).toString(16)).slice(-2); }).join('');
	}
	function initPresetEditor() {
		var editor = document.querySelector('[data-preset-editor]'), preview = document.querySelector('[data-preset-preview]'), out = document.querySelector('[data-contrast-out]');
		if (!editor) { return; }
		var inputs = $$('.nx-color[data-var]', editor);
		var get = function (name) { var i = editor.querySelector('[name="colors[' + name + ']"]'); return i ? i.value : ''; };
		var update = function () {
			if (preview) {
				inputs.forEach(function (i) { preview.style.setProperty(i.dataset.var, i.value); });
				var rgb = hexToRgb(get('primary'));
				if (rgb) { preview.style.setProperty('--theme-primary-rgb', rgb.join(',')); }
				preview.style.setProperty('--theme-primary-active', shade(get('primary'), 22));
				preview.style.setProperty('--theme-primary-text', shade(get('primary'), 35));
			}
			if (out) {
				var checks = [
					{ label: 'Primary / text on primary', a: get('primary'), b: get('on_primary') },
					{ label: 'Button / button text', a: get('button'), b: get('button_text') },
					{ label: 'Text / background', a: get('text'), b: get('secondary') },
					{ label: 'Header text / header', a: get('header_text'), b: get('header_bg') },
					{ label: 'Footer text / footer', a: get('footer_text'), b: get('footer_bg') }
				];
				out.innerHTML = checks.map(function (c) {
					var r = contrast(c.a, c.b), cls = r >= 4.5 ? 'ok' : (r >= 3 ? 'warn' : 'bad');
					return '<span class="nx-pill nx-pill--' + cls + '" title="' + esc(c.label) + '">' + esc(c.label) + ': ' + r.toFixed(1) + ':1</span>';
				}).join(' ');
			}
		};
		editor.addEventListener('nx:color', debounce(update, 30));
		editor.addEventListener('input', debounce(update, 60));
		update();
		var exportBtn = document.querySelector('[data-preset-export]');
		if (exportBtn) {
			exportBtn.addEventListener('click', function () {
				var colors = {};
				inputs.forEach(function (i) { colors[i.name.replace(/^colors\[|\]$/g, '')] = i.value; });
				var name = (editor.querySelector('[name="preset_name"]') || {}).value || 'preset';
				var blob = new Blob([JSON.stringify({ name: name, colors: colors }, null, 2)], { type: 'application/json' });
				var a = document.createElement('a');
				a.href = URL.createObjectURL(blob); a.download = name.replace(/[^a-z0-9_-]+/gi, '-').toLowerCase() + '.json';
				document.body.appendChild(a); a.click(); a.remove();
			});
		}
		$$('.nx-presetpick__item input').forEach(function (r) { r.addEventListener('change', function () { $$('.nx-presetpick__item').forEach(function (l) { l.classList.toggle('is-active', l.contains(r) && r.checked); }); }); });
	}

	/* ---------- Demo import (batched AJAX) ---------- */
	function initDemoImport() {
		$$('[data-demo-import]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var id = btn.dataset.demoImport, card = btn.closest('[data-demo-card]');
				if (!window.confirm(I18N.confirm)) { return; }
				var steps = $$('li[data-step]', card).map(function (li) { return li.dataset.step; });
				var progress = card.querySelector('.nx-demo__progress'), bar = card.querySelector('.nx-progress span'), status = card.querySelector('.nx-demo__status');
				progress.hidden = false;
				btn.disabled = true;
				$$('[data-demo-import]').forEach(function (b) { b.disabled = true; });
				card.classList.add('is-importing');
				status.textContent = I18N.importing;
				var stepIdx = 0, offset = 0, guard = 0;
				var run = function () {
					if (stepIdx >= steps.length) {
						bar.style.width = '100%';
						status.textContent = I18N.saved;
						card.classList.remove('is-importing');
						card.classList.add('is-installed');
						$$('li[data-step]', card).forEach(function (li) { li.classList.add('is-done'); });
						setTimeout(function () { window.location.reload(); }, 900);
						return;
					}
					var step = steps[stepIdx];
					$$('li[data-step]', card).forEach(function (li, i) { li.classList.toggle('is-active', i === stepIdx); li.classList.toggle('is-done', i < stepIdx); });
					ajax('nexora_demo_step', { demo: id, step: step, offset: offset }).then(function (d) {
						status.textContent = d.message || '';
						bar.style.width = Math.round(((stepIdx + (d.done ? 1 : 0.5)) / steps.length) * 100) + '%';
						if (d.done) { stepIdx++; offset = 0; guard = 0; }
						else { offset = d.offset; if (++guard > 500) { status.textContent = I18N.error; return; } }
						run();
					}, function (msg) {
						status.textContent = msg || I18N.error;
						card.classList.remove('is-importing');
						$$('[data-demo-import]').forEach(function (b) { b.disabled = false; });
					});
				};
				run();
			});
		});
		$$('[data-demo-remove]').forEach(function (btn) {
			btn.addEventListener('click', function (e) {
				e.preventDefault();
				btn.disabled = true;
				ajax('nexora_demo_remove').then(function (d) { notice(d.message, 'info'); setTimeout(function () { window.location.reload(); }, 800); }, function (m) { notice(m, 'error'); btn.disabled = false; });
			});
		});
	}

	/* ---------- Onboarding tour ---------- */
	function initOnboarding() {
		var dataEl = document.getElementById('nexora-onboarding');
		if (!dataEl) { return; }
		var data;
		try { data = JSON.parse(dataEl.textContent); } catch (e) { return; }
		var steps = (data.steps || []).filter(function (s) { return !s.target || document.querySelector(s.target); });
		if (!steps.length) { return; }
		var i18n = data.i18n || {}, idx = 0;
		var overlay = document.createElement('div'); overlay.className = 'nx-tour-overlay';
		var pop = document.createElement('div'); pop.className = 'nx-tour'; pop.setAttribute('role', 'dialog'); pop.setAttribute('aria-live', 'polite');
		document.body.appendChild(overlay); document.body.appendChild(pop);
		var finish = function () {
			overlay.remove(); pop.remove();
			$$('.nx-tour-target').forEach(function (n) { n.classList.remove('nx-tour-target'); });
			ajax('nexora_onboarding_done');
		};
		var render = function () {
			var s = steps[idx];
			$$('.nx-tour-target').forEach(function (n) { n.classList.remove('nx-tour-target'); });
			var target = s.target ? document.querySelector(s.target) : null;
			pop.innerHTML = '<div class="nx-tour__step">' + (idx + 1) + ' ' + esc(i18n.of) + ' ' + steps.length + '</div><h3>' + esc(s.title) + '</h3><p>' + esc(s.text) + '</p>' +
				'<div class="nx-tour__actions"><button type="button" class="button-link" data-tour="skip">' + esc(i18n.skip) + '</button><span>' +
				(idx > 0 ? '<button type="button" class="button" data-tour="back">' + esc(i18n.back) + '</button> ' : '') +
				'<button type="button" class="button button-primary" data-tour="next">' + esc(idx === steps.length - 1 ? i18n.finish : i18n.next) + '</button></span></div>';
			if (target) {
				target.classList.add('nx-tour-target');
				target.scrollIntoView({ behavior: 'smooth', block: 'center' });
				var r = target.getBoundingClientRect();
				pop.style.top = Math.min(window.innerHeight - pop.offsetHeight - 20, Math.max(20, r.bottom + 12)) + 'px';
				pop.style.left = Math.max(20, Math.min(window.innerWidth - 380, r.left)) + 'px';
				pop.classList.remove('nx-tour--center');
			} else {
				pop.classList.add('nx-tour--center');
				pop.style.top = ''; pop.style.left = '';
			}
			var f = pop.querySelector('[data-tour="next"]'); if (f) { f.focus(); }
		};
		pop.addEventListener('click', function (e) {
			var b = e.target.closest('[data-tour]');
			if (!b) { return; }
			if (b.dataset.tour === 'skip') { finish(); }
			else if (b.dataset.tour === 'back') { idx = Math.max(0, idx - 1); render(); }
			else if (idx >= steps.length - 1) { finish(); } else { idx++; render(); }
		});
		document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && document.body.contains(pop)) { finish(); } });
		window.addEventListener('resize', debounce(render, 150));
		render();
	}

	/* ---------- Wizard niceties ---------- */
	function initWizard() {
		if (root.dataset.page !== 'nexora-wizard') { return; }
		$$('.nx-checkgrid label').forEach(function (l) {
			var cb = l.querySelector('input[type="checkbox"]');
			if (cb) { l.classList.toggle('is-checked', cb.checked); cb.addEventListener('change', function () { l.classList.toggle('is-checked', cb.checked); }); }
		});
	}

	$(function () {
		initFieldWidgets(root);
		initSettingsPage();
		initPresetEditor();
		initDemoImport();
		initOnboarding();
		initWizard();
	});
})(jQuery);
