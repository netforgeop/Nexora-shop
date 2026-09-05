/**
 * Nexora theme runtime.
 *
 * Ported from the original static template: same data-attribute API, but every
 * data operation now goes through WordPress / WooCommerce (admin-ajax with a
 * nonce, or WooCommerce's own forms). No product catalogue lives in JS.
 *
 * @package Nexora
 */
(function () {
	'use strict';

	var CFG = window.NEXORA || {};
	var I18N = CFG.i18n || {};
	var FA_DIGITS = '۰۱۲۳۴۵۶۷۸۹';

	/* ------------------------------------------------------------------ */
	/* Core helpers                                                       */
	/* ------------------------------------------------------------------ */

	var $ = function (sel, root) { return (root || document).querySelector(sel); };
	var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };
	var t = function (key, fallback) { return I18N[key] || fallback || key; };
	var digits = function (v) {
		v = String(v);
		return CFG.fa ? v.replace(/\d/g, function (d) { return FA_DIGITS[d]; }) : v;
	};
	var esc = function (s) {
		return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	};
	var mq = function (q) { return window.matchMedia(q); };
	var reduceMotion = function () { return mq('(prefers-reduced-motion: reduce)').matches; };
	var isRTL = document.documentElement.dir === 'rtl';

	function on(root, event, selector, handler, opts) {
		root.addEventListener(event, function (e) {
			var target = e.target.closest ? e.target.closest(selector) : null;
			if (target && root.contains(target)) { handler(e, target); }
		}, opts);
	}
	function debounce(fn, ms) {
		var timer;
		return function () {
			var args = arguments, self = this;
			clearTimeout(timer);
			timer = setTimeout(function () { fn.apply(self, args); }, ms || 200);
		};
	}
	function throttle(fn, ms) {
		var last = 0, timer;
		return function () {
			var args = arguments, now = Date.now(), remaining = (ms || 100) - (now - last);
			if (remaining <= 0) { last = now; fn.apply(null, args); }
			else { clearTimeout(timer); timer = setTimeout(function () { last = Date.now(); fn.apply(null, args); }, remaining); }
		};
	}
	function lockScroll(lock) { document.body.classList.toggle('is-locked', !!lock); }
	function trapFocus(container) {
		var FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
		var handler = function (e) {
			if (e.key !== 'Tab') { return; }
			var nodes = $$(FOCUSABLE, container).filter(function (n) { return n.offsetParent !== null || n === document.activeElement; });
			if (!nodes.length) { return; }
			var first = nodes[0], last = nodes[nodes.length - 1];
			if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
			else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
		};
		container.addEventListener('keydown', handler);
		return function () { container.removeEventListener('keydown', handler); };
	}
	function announce(text) {
		var live = document.getElementById('nx-live');
		if (!live) {
			live = document.createElement('div');
			live.id = 'nx-live'; live.className = 'visually-hidden';
			live.setAttribute('aria-live', 'polite'); live.setAttribute('aria-atomic', 'true');
			document.body.appendChild(live);
		}
		live.textContent = '';
		requestAnimationFrame(function () { live.textContent = text; });
	}
	function scrollToEl(node, offset) {
		if (!node) { return; }
		var top = node.getBoundingClientRect().top + window.scrollY - (offset == null ? 90 : offset);
		window.scrollTo({ top: top, behavior: reduceMotion() ? 'auto' : 'smooth' });
	}
	function storage(key, value) {
		try {
			if (value === undefined) { var raw = localStorage.getItem(key); return raw ? JSON.parse(raw) : null; }
			if (value === null) { localStorage.removeItem(key); } else { localStorage.setItem(key, JSON.stringify(value)); }
		} catch (e) { /* private mode */ }
		return value;
	}

	/* AJAX -------------------------------------------------------------- */

	function ajax(action, data, method) {
		data = data || {};
		var isGet = (method || 'POST') === 'GET';
		var body = new URLSearchParams();
		body.set('action', action);
		body.set('nonce', CFG.nonce || '');
		Object.keys(data).forEach(function (k) {
			var v = data[k];
			if (Array.isArray(v)) { v.forEach(function (x) { body.append(k + '[]', x); }); }
			else if (v !== undefined && v !== null) { body.set(k, v); }
		});
		var url = CFG.ajaxUrl || '/wp-admin/admin-ajax.php';
		var opts = { method: isGet ? 'GET' : 'POST', credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } };
		if (isGet) { url += (url.indexOf('?') > -1 ? '&' : '?') + body.toString(); }
		else { opts.body = body; opts.headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8'; }
		return fetch(url, opts).then(function (r) { return r.json(); }).then(function (json) {
			if (!json || json.success === false) {
				var msg = json && json.data && json.data.message ? json.data.message : t('error', 'Something went wrong.');
				var err = new Error(msg); err.data = json && json.data; throw err;
			}
			return json.data || {};
		});
	}
	function applyFragments(fragments) {
		if (!fragments) { return; }
		Object.keys(fragments).forEach(function (sel) {
			$$(sel).forEach(function (node) {
				var tmp = document.createElement('div');
				tmp.innerHTML = fragments[sel];
				if (tmp.firstElementChild) { node.replaceWith(tmp.firstElementChild); }
			});
		});
		document.dispatchEvent(new CustomEvent('nexora:fragments'));
		if (window.jQuery) { window.jQuery(document.body).trigger('wc_fragments_refreshed'); }
	}

	/* Toasts ------------------------------------------------------------ */

	var toastRegion;
	function initToasts() {
		toastRegion = document.getElementById('toast-region');
		if (!toastRegion) {
			toastRegion = document.createElement('div');
			toastRegion.id = 'toast-region'; toastRegion.className = 'toast-region'; toastRegion.setAttribute('aria-live', 'polite');
			document.body.appendChild(toastRegion);
		}
		toastRegion.addEventListener('click', function (e) {
			var close = e.target.closest('[data-toast-close]');
			if (close) { dismissToast(close.closest('.toast')); }
			var undo = e.target.closest('[data-toast-undo]');
			if (undo) { undo.dispatchEvent(new CustomEvent('toast:undo', { bubbles: true })); dismissToast(undo.closest('.toast')); }
		});
	}
	function toast(message, opts) {
		opts = opts || {};
		if (!toastRegion) { return null; }
		var node = document.createElement('div');
		node.className = 'toast toast--' + (opts.type || 'default');
		node.setAttribute('role', 'status');
		var html = '<span class="toast__msg">' + esc(message) + '</span>';
		if (opts.action && opts.actionHref) { html += '<a class="toast__action" href="' + esc(opts.actionHref) + '">' + esc(opts.action) + '</a>'; }
		if (opts.onUndo) { html += '<button type="button" class="toast__action" data-toast-undo>' + esc(t('undo', 'Undo')) + '</button>'; }
		html += '<button type="button" class="toast__close" data-toast-close aria-label="' + esc(t('close', 'Close')) + '"><span class="icon icon--xs linear-icon-cross" aria-hidden="true"></span></button>';
		node.innerHTML = html;
		if (opts.onUndo) { node.addEventListener('toast:undo', opts.onUndo, { once: true }); }
		toastRegion.appendChild(node);
		var live = function () { return $$('.toast', toastRegion).filter(function (n) { return !n.classList.contains('is-leaving'); }); };
		while (live().length > 4) { dismissToast(live()[0]); }
		var timer = setTimeout(function () { dismissToast(node); }, opts.duration || 4000);
		node.addEventListener('mouseenter', function () { clearTimeout(timer); }, { once: true });
		announce(message);
		return node;
	}
	function dismissToast(node) {
		if (!node || node.classList.contains('is-leaving')) { return; }
		node.classList.add('is-leaving');
		setTimeout(function () { node.remove(); }, 260);
	}

	/* ------------------------------------------------------------------ */
	/* Header                                                             */
	/* ------------------------------------------------------------------ */

	function initHeader() {
		var header = $('[data-header]');
		if (!header) { return; }

		var ann = $('[data-announcement]');
		if (ann) {
			if (storage('nx:announcement') === 'dismissed') { ann.classList.add('is-dismissed'); }
			var annClose = $('[data-announcement-close]', ann);
			if (annClose) { annClose.addEventListener('click', function () { ann.classList.add('is-dismissed'); storage('nx:announcement', 'dismissed'); }); }
		}

		if (!header.hasAttribute('data-no-sticky')) {
			var main = $('.header-main', header);
			var stuck = false, lastY = window.scrollY;
			var threshold = function () { return main ? main.offsetTop + main.offsetHeight + 40 : 200; };
			var update = function () {
				var y = window.scrollY, should = y > threshold();
				if (should !== stuck) {
					stuck = should;
					if (stuck && main) { header.style.setProperty('--stuck-offset', main.offsetHeight + 'px'); }
					header.classList.toggle('is-stuck', stuck);
					if (!stuck) { header.classList.remove('is-hidden'); }
				}
				if (stuck && mq('(max-width: 991.98px)').matches) { header.classList.toggle('is-hidden', y > lastY && y - lastY > 4); }
				lastY = y;
			};
			window.addEventListener('scroll', throttle(update, 80), { passive: true });
			update();
		}

		var catMenu = $('[data-cat-menu]');
		if (catMenu) {
			var trigger = $('[data-cat-menu-trigger]', catMenu);
			var pinned = catMenu.classList.contains('is-pinned');
			var setOpen = function (open) { catMenu.classList.toggle('is-open', open); if (trigger) { trigger.setAttribute('aria-expanded', String(open)); } };
			if (trigger) { trigger.addEventListener('click', function () { setOpen(!catMenu.classList.contains('is-open')); }); }
			if (!pinned) {
				var hoverTimer;
				catMenu.addEventListener('mouseenter', function () { if (mq('(hover: hover)').matches) { clearTimeout(hoverTimer); setOpen(true); } });
				catMenu.addEventListener('mouseleave', function () { if (mq('(hover: hover)').matches) { hoverTimer = setTimeout(function () { setOpen(false); }, 180); } });
			}
			document.addEventListener('click', function (e) { if (!catMenu.contains(e.target) && !pinned) { setOpen(false); } });
			document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && catMenu.classList.contains('is-open') && !pinned) { setOpen(false); if (trigger) { trigger.focus(); } } });
			catMenu.addEventListener('keydown', function (e) {
				if (e.key !== 'ArrowDown' && e.key !== 'ArrowUp') { return; }
				var links = $$('.cat-menu__link', catMenu), idx = links.indexOf(document.activeElement);
				if (idx === -1) { return; }
				e.preventDefault();
				links[(idx + (e.key === 'ArrowDown' ? 1 : -1) + links.length) % links.length].focus();
			});
			if (pinned) {
				var unpin = function () { if (window.scrollY > 120) { catMenu.classList.remove('is-pinned'); setOpen(false); window.removeEventListener('scroll', unpin); } };
				window.addEventListener('scroll', unpin, { passive: true });
			}
		}

		$$('[data-nav-dropdown]').forEach(function (item) {
			var link = $('.nav__link', item);
			if (!link) { return; }
			var setOpen = function (open) { item.classList.toggle('is-open', open); link.setAttribute('aria-expanded', String(open)); };
			link.addEventListener('click', function (e) { if (!mq('(hover: hover)').matches && !item.classList.contains('is-open')) { e.preventDefault(); setOpen(true); } });
			link.addEventListener('keydown', function (e) { if (e.key === 'ArrowDown' || e.key === ' ') { e.preventDefault(); setOpen(true); var f = $('.dropdown__link', item); if (f) { f.focus(); } } });
			item.addEventListener('keydown', function (e) { if (e.key === 'Escape') { setOpen(false); link.focus(); } });
			item.addEventListener('focusout', function (e) { if (!item.contains(e.relatedTarget)) { setOpen(false); } });
			document.addEventListener('click', function (e) { if (!item.contains(e.target)) { setOpen(false); } });
		});

		var wrap = $('[data-mini-cart-wrap]');
		if (wrap) {
			var toggle = $('[data-mini-cart-toggle]', wrap);
			if (toggle) {
				toggle.addEventListener('keydown', function (e) {
					if (e.key === 'ArrowDown') { e.preventDefault(); wrap.classList.add('is-open'); toggle.setAttribute('aria-expanded', 'true'); var f = $('[data-mini-cart] a, [data-mini-cart] button', wrap); if (f) { f.focus(); } }
				});
				wrap.addEventListener('keydown', function (e) { if (e.key === 'Escape') { wrap.classList.remove('is-open'); toggle.setAttribute('aria-expanded', 'false'); toggle.focus(); } });
				wrap.addEventListener('focusout', function (e) { if (!wrap.contains(e.relatedTarget)) { wrap.classList.remove('is-open'); toggle.setAttribute('aria-expanded', 'false'); } });
			}
		}

		var btt = document.getElementById('back-to-top');
		if (btt) {
			window.addEventListener('scroll', throttle(function () { btt.classList.toggle('is-visible', window.scrollY > 600); }, 120), { passive: true });
			btt.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: reduceMotion() ? 'auto' : 'smooth' }); });
		}
		var prog = document.getElementById('page-progress');
		if (prog) {
			prog.style.inlineSize = '70%';
			var done = function () { prog.classList.add('is-done'); setTimeout(function () { prog.remove(); }, 600); };
			if (document.readyState === 'complete') { done(); } else { window.addEventListener('load', done, { once: true }); }
			setTimeout(done, 2500);
		}
	}

	/* ------------------------------------------------------------------ */
	/* Drawers & modals                                                   */
	/* ------------------------------------------------------------------ */

	var overlay, openDrawer = null, releaseTrap = null, lastFocus = null;

	function initDrawers() {
		overlay = $('[data-overlay]');
		if (overlay) { overlay.hidden = false; }
		on(document, 'click', '[data-drawer-open]', function (e, btn) { e.preventDefault(); drawerOpen(btn.getAttribute('data-drawer-open'), btn); });
		on(document, 'click', '[data-drawer-close]', function (e, btn) { if (btn.closest('[data-drawer]')) { drawerClose(); } });
		if (overlay) { overlay.addEventListener('click', function () { drawerClose(); }); }
		document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && openDrawer) { drawerClose(); } });

		on(document, 'click', '[data-mobile-tab]', function (e, tab) {
			var drawer = tab.closest('[data-drawer]');
			$$('[data-mobile-tab]', drawer).forEach(function (x) { var sel = x === tab; x.setAttribute('aria-selected', String(sel)); x.tabIndex = sel ? 0 : -1; });
			$$('[data-mobile-panel]', drawer).forEach(function (p) { p.hidden = p.dataset.mobilePanel !== tab.dataset.mobileTab; });
		});
		on(document, 'click', '[data-mobile-sub-toggle]', function (e, btn) {
			var sub = document.getElementById(btn.getAttribute('aria-controls'));
			var open = btn.getAttribute('aria-expanded') === 'true';
			btn.setAttribute('aria-expanded', String(!open));
			if (sub) { sub.classList.toggle('is-open', !open); }
		});
		on(document, 'click', '[data-modal-close]', function (e, btn) { var d = btn.closest('dialog'); if (d) { d.close(); } });
		$$('dialog.modal').forEach(function (d) {
			d.addEventListener('click', function (e) { if (e.target === d) { d.close(); } });
			d.addEventListener('close', function () { lockScroll(false); });
		});
		mq('(min-width: 992px)').addEventListener('change', function (e) { if (e.matches && openDrawer && openDrawer.id === 'drawer-menu') { drawerClose(); } });
	}
	function drawerOpen(id, trigger) {
		var drawer = document.getElementById(id);
		if (!drawer || openDrawer === drawer) { return; }
		if (openDrawer) { drawerClose(true); }
		lastFocus = trigger || document.activeElement;
		drawer.inert = false;
		drawer.setAttribute('aria-hidden', 'false');
		drawer.classList.add('is-open');
		if (overlay) { overlay.classList.add('is-visible'); }
		lockScroll(true);
		openDrawer = drawer;
		if (trigger) { trigger.setAttribute('aria-expanded', 'true'); }
		releaseTrap = trapFocus(drawer);
		var first = $('[data-drawer-close], a, button, input', drawer);
		setTimeout(function () { if (first) { first.focus(); } }, 60);
		drawer.dispatchEvent(new CustomEvent('drawer:open', { bubbles: true }));
	}
	function drawerClose(silent) {
		if (!openDrawer) { return; }
		var drawer = openDrawer;
		drawer.classList.remove('is-open');
		drawer.setAttribute('aria-hidden', 'true');
		drawer.inert = true;
		if (overlay) { overlay.classList.remove('is-visible'); }
		lockScroll(false);
		if (releaseTrap) { releaseTrap(); releaseTrap = null; }
		$$('[data-drawer-open="' + drawer.id + '"]').forEach(function (b) { b.setAttribute('aria-expanded', 'false'); });
		openDrawer = null;
		if (!silent && lastFocus && document.contains(lastFocus)) { lastFocus.focus(); }
		drawer.dispatchEvent(new CustomEvent('drawer:close', { bubbles: true }));
	}
	function openModal(dialog) {
		if (!dialog) { return; }
		if (typeof dialog.showModal === 'function') { if (!dialog.open) { dialog.showModal(); } } else { dialog.setAttribute('open', ''); }
		lockScroll(true);
	}

	/* ------------------------------------------------------------------ */
	/* Swiper carousels                                                   */
	/* ------------------------------------------------------------------ */

	function initSwipers(root) {
		if (typeof window.Swiper !== 'function') { return; }
		var reduce = reduceMotion();
		$$('[data-swiper]', root || document).forEach(function (el) {
			if (el.swiper) { return; }
			var type = el.dataset.swiper, id = el.dataset.carouselId;
			var nav = id ? { prevEl: $('[data-carousel-prev="' + id + '"]'), nextEl: $('[data-carousel-next="' + id + '"]') } : null;
			var common = { a11y: { enabled: true }, watchOverflow: true, observer: true, observeParents: true, grabCursor: true };
			var opts;
			switch (type) {
				case 'hero': {
					var delay = Number(el.dataset.autoplay || CFG.heroDelay || 6000);
					opts = Object.assign({}, common, {
						loop: el.querySelectorAll('.swiper-slide').length > 1,
						speed: reduce ? 0 : 700,
						effect: 'fade', fadeEffect: { crossFade: true },
						autoplay: reduce || delay <= 0 ? false : { delay: delay, disableOnInteraction: false, pauseOnMouseEnter: true },
						pagination: { el: $('.swiper-pagination', el), clickable: true, renderBullet: function (i, cls) { return '<button type="button" class="' + cls + '" aria-label="' + esc(t('page', 'Page')) + ' ' + digits(i + 1) + '"></button>'; } },
						navigation: { prevEl: $('[data-swiper-prev]', el), nextEl: $('[data-swiper-next]', el) },
						keyboard: { enabled: true, onlyInViewport: true }
					});
					break;
				}
				case 'products': {
					var xl = Number(el.dataset.slidesXl || 4), xxl = Number(el.dataset.slidesXxl || 4);
					opts = Object.assign({}, common, {
						slidesPerView: 2, spaceBetween: 12, speed: reduce ? 0 : 450,
						breakpoints: { 576: { slidesPerView: 2, spaceBetween: 16 }, 768: { slidesPerView: 3, spaceBetween: 16 }, 992: { slidesPerView: 3, spaceBetween: 20 }, 1200: { slidesPerView: xl, spaceBetween: 20 }, 1400: { slidesPerView: xxl, spaceBetween: 24 } },
						navigation: nav, keyboard: { enabled: true, onlyInViewport: true }
					});
					break;
				}
				case 'brands':
					opts = Object.assign({}, common, { slidesPerView: 'auto', spaceBetween: 8, loop: el.querySelectorAll('.swiper-slide').length > 6, speed: reduce ? 0 : 5000, autoplay: reduce ? false : { delay: 0, disableOnInteraction: false }, allowTouchMove: true, freeMode: true });
					break;
				case 'reviews':
					opts = Object.assign({}, common, { slidesPerView: 1, spaceBetween: 16, speed: reduce ? 0 : 450, breakpoints: { 768: { slidesPerView: 2, spaceBetween: 20 }, 1200: { slidesPerView: 3, spaceBetween: 24 } }, navigation: nav });
					break;
				default:
					opts = Object.assign({}, common);
			}
			new window.Swiper(el, opts); // eslint-disable-line no-new
			if (type === 'brands') { el.style.setProperty('--swiper-wrapper-transition-timing-function', 'linear'); }
		});
	}

	/* ------------------------------------------------------------------ */
	/* Wishlist / compare (localStorage + server sync)                    */
	/* ------------------------------------------------------------------ */

	var lists = { wishlist: storage('nx:wishlist') || [], compare: storage('nx:compare') || [] };

	function listSave(type) {
		storage('nx:' + type, lists[type]);
		renderBadges();
		syncPressed(document);
		renderCompareBar();
		if (CFG.loggedIn) { ajax('nexora_list_sync', { type: type, ids: lists[type], mode: 'set' }).catch(function () {}); }
		document.dispatchEvent(new CustomEvent('nexora:list', { detail: { type: type, ids: lists[type].slice() } }));
	}
	function listToggle(type, id) {
		id = Number(id);
		var arr = lists[type], i = arr.indexOf(id), limit = type === 'compare' ? (CFG.compareLimit || 4) : 200;
		if (i > -1) { arr.splice(i, 1); listSave(type); return false; }
		if (arr.length >= limit) {
			if (type === 'compare') { toast(t('compareFull', 'You can compare up to %s products').replace('%s', digits(limit)), { type: 'warning' }); return null; }
			arr.shift();
		}
		arr.push(id); listSave(type); return true;
	}
	function listsInit() {
		if (!CFG.loggedIn) { return; }
		['wishlist', 'compare'].forEach(function (type) {
			ajax('nexora_list_sync', { type: type, ids: lists[type], mode: 'merge' }).then(function (d) {
				if (d && Array.isArray(d.ids)) { lists[type] = d.ids.map(Number); storage('nx:' + type, lists[type]); renderBadges(); syncPressed(document); renderCompareBar(); }
			}).catch(function () {});
		});
	}
	function renderBadges() {
		['wishlist', 'compare'].forEach(function (type) {
			$$('[data-count="' + type + '"]').forEach(function (b) { b.textContent = digits(lists[type].length); b.classList.toggle('is-empty', !lists[type].length); });
		});
	}
	function syncPressed(root) {
		$$('[data-action="wishlist"][data-id], [data-action="compare"][data-id]', root).forEach(function (btn) {
			var type = btn.dataset.action, active = lists[type].indexOf(Number(btn.dataset.id)) > -1;
			btn.setAttribute('aria-pressed', String(active));
			btn.classList.toggle('is-active', active);
		});
	}
	function renderCompareBar() {
		var bar = $('[data-compare-bar]');
		if (!bar) { return; }
		var n = lists.compare.length;
		bar.classList.toggle('is-visible', n > 0);
		var label = $('[data-compare-label]', bar);
		if (label) { label.textContent = t('compareCount', '%s products to compare').replace('%s', digits(n)); }
		var thumbs = $('[data-compare-thumbs]', bar);
		if (thumbs) {
			thumbs.innerHTML = lists.compare.map(function (id) {
				var card = $('[data-product-card][data-id="' + id + '"] img, [data-product-page][data-id="' + id + '"] img');
				return card ? '<img src="' + esc(card.currentSrc || card.src) + '" alt="" width="32" height="32">' : '<span class="compare-bar__dot"></span>';
			}).join('');
		}
	}

	/* ------------------------------------------------------------------ */
	/* Cart                                                               */
	/* ------------------------------------------------------------------ */

	function addToCart(payload, btn) {
		if (btn) { btn.classList.add('is-loading'); btn.disabled = true; }
		return ajax('nexora_add_to_cart', payload).then(function (d) {
			applyFragments(d.fragments);
			toast(d.message || t('addedToCart', 'Added to cart'), { type: 'success', action: t('viewCart', 'View cart'), actionHref: CFG.cartUrl });
			if (window.jQuery) { window.jQuery(document.body).trigger('added_to_cart', [d.fragments, '', btn ? window.jQuery(btn) : null]); }
			return d;
		}).catch(function (err) {
			toast(err.message || t('cartError', 'Could not add to cart.'), { type: 'error' });
		}).finally(function () {
			if (btn) { btn.classList.remove('is-loading'); btn.disabled = false; }
		});
	}
	function formPayload(form) {
		var fd = new FormData(form), payload = { variation: {} };
		var pid = fd.get('add-to-cart') || fd.get('product_id') || (form.closest('[data-product-page]') || {}).dataset && form.closest('[data-product-page]').dataset.id;
		var submit = $('[name="add-to-cart"]', form);
		if (!pid && submit) { pid = submit.value; }
		payload.product_id = pid;
		payload.variation_id = fd.get('variation_id') || 0;
		payload.quantity = fd.get('quantity') || 1;
		fd.forEach(function (v, k) { if (k.indexOf('attribute_') === 0) { payload['variation[' + k + ']'] = v; } });
		delete payload.variation;
		return payload;
	}
	function updateQtyButtons(wrap) {
		var input = $('input', wrap);
		if (!input) { return; }
		var min = Number(input.min || 0), max = input.max !== '' ? Number(input.max) : Infinity, v = Number(input.value || 0);
		var dec = $('[data-qty-dec]', wrap), inc = $('[data-qty-inc]', wrap);
		if (dec) { dec.disabled = v <= min; }
		if (inc) { inc.disabled = v >= max; }
	}
	function initCart() {
		on(document, 'click', '[data-qty-dec], [data-qty-inc]', function (e, btn) {
			var wrap = btn.closest('[data-qty]'), input = $('input', wrap);
			if (!input) { return; }
			var step = Number(input.step || 1), min = Number(input.min || 0), max = input.max !== '' ? Number(input.max) : Infinity;
			var v = Number(input.value || 0) + (btn.hasAttribute('data-qty-inc') ? step : -step);
			if (v > max) { v = max; toast(t('maxQty', 'Maximum quantity is %s').replace('%s', digits(max)), { type: 'warning' }); }
			if (v < min) { v = min; }
			input.value = v;
			input.dispatchEvent(new Event('change', { bubbles: true }));
			updateQtyButtons(wrap);
		});
		on(document, 'change', '[data-qty] input', function (e, input) { updateQtyButtons(input.closest('[data-qty]')); });
		$$('[data-qty]').forEach(updateQtyButtons);

		// Simple products from cards / wishlist.
		on(document, 'click', '[data-action="add-to-cart"][data-id]', function (e, btn) {
			e.preventDefault();
			var card = btn.closest('[data-product-card], .wish-item, .compare-table');
			if (card && card.dataset.type && card.dataset.type !== 'simple') { window.location.href = btn.dataset.href || $('a', card).href; return; }
			addToCart({ product_id: btn.dataset.id, quantity: 1 }, btn);
		});

		// Product forms (single, quick view): AJAX unless "buy now".
		on(document, 'click', '[data-add-to-cart-form] [type="submit"], .variations_form [type="submit"]', function (e, btn) {
			var form = btn.closest('form');
			if (!form) { return; }
			if (btn.hasAttribute('data-buy-now')) { form.dataset.buyNow = '1'; return; }
			delete form.dataset.buyNow;
			if (form.classList.contains('variations_form') && !$('[name="variation_id"]', form).value) { return; } // let WC show its validation
			if (btn.hasAttribute('data-add-to-cart-submit') || form.hasAttribute('data-add-to-cart-form')) {
				e.preventDefault();
				addToCart(formPayload(form), btn).then(function (d) {
					if (d) { var qv = form.closest('dialog'); if (qv) { qv.close(); } }
				});
			}
		});

		// Sticky bar / aside "add" buttons proxy to the main form button.
		on(document, 'click', '[data-sticky-add], [data-aside-add]', function (e, btn) {
			var primary = $('[data-product-page] .single_add_to_cart_button');
			if (primary) { primary.click(); } else { scrollToEl($('[data-product-page] .buy-box')); }
		});

		// Mini-cart quantities & removals.
		var miniQty = debounce(function (input) {
			var item = input.closest('[data-cart-key]');
			if (!item) { return; }
			ajax('nexora_cart_update', { key: item.dataset.cartKey, quantity: input.value }).then(function (d) { applyFragments(d.fragments); }).catch(function (err) { toast(err.message, { type: 'error' }); });
		}, 350);
		on(document, 'change', '[data-mini-cart] [data-qty] input', function (e, input) { miniQty(input); });
		on(document, 'click', '[data-mini-cart] [data-cart-remove]', function (e, btn) {
			e.preventDefault();
			var item = btn.closest('[data-cart-key]');
			item.classList.add('is-removing');
			ajax('nexora_cart_update', { key: btn.dataset.cartRemove, quantity: 0 }).then(function (d) { applyFragments(d.fragments); toast(t('removed', 'Removed from cart')); }).catch(function (err) { item.classList.remove('is-removing'); toast(err.message, { type: 'error' }); });
		});

		// Cart page: qty change auto-submits WooCommerce's update (its own AJAX handles the rest).
		var cartPage = $('[data-cart-page]');
		if (cartPage) {
			var autoUpdate = debounce(function () { var btn = $('[name="update_cart"]', cartPage); if (btn) { btn.disabled = false; btn.click(); } }, 500);
			on(cartPage, 'change', '.woocommerce-cart-form [data-qty] input', function () { autoUpdate(); });
			on(cartPage, 'click', '[data-action="cart-to-wishlist"]', function (e, btn) {
				e.preventDefault();
				if (lists.wishlist.indexOf(Number(btn.dataset.id)) === -1) { listToggle('wishlist', btn.dataset.id); }
				var remove = $('[data-cart-remove="' + btn.dataset.key + '"]', cartPage);
				if (remove) { remove.click(); }
				toast(t('movedToWishlist', 'Moved to wishlist'), { type: 'success' });
			});
			if (window.jQuery) { window.jQuery(document.body).on('updated_wc_div updated_cart_totals', function () { $$('[data-qty]', cartPage).forEach(updateQtyButtons); }); }
		}
		document.addEventListener('nexora:fragments', function () { $$('[data-mini-cart] [data-qty]').forEach(updateQtyButtons); });
	}

	/* ------------------------------------------------------------------ */
	/* Wishlist / compare buttons, share, quick view                      */
	/* ------------------------------------------------------------------ */

	function initActions() {
		on(document, 'click', '[data-action="wishlist"][data-id]', function (e, btn) {
			e.preventDefault();
			var added = listToggle('wishlist', btn.dataset.id);
			if (added === null) { return; }
			toast(added ? t('addedToWishlist', 'Added to wishlist') : t('removedFromWishlist', 'Removed from wishlist'), { type: added ? 'success' : 'default', action: added ? t('wishlist', 'Wishlist') : null, actionHref: CFG.wishlistUrl });
			if (!added && btn.closest('.wish-item')) { var item = btn.closest('.wish-item'); item.remove(); wishlistPageState(); }
		});
		on(document, 'click', '[data-action="compare"][data-id]', function (e, btn) {
			e.preventDefault();
			var added = listToggle('compare', btn.dataset.id);
			if (added === null) { return; }
			toast(added ? t('addedToCompare', 'Added to compare') : t('removedFromCompare', 'Removed from compare'), { type: added ? 'success' : 'default', action: added ? t('compare', 'Compare') : null, actionHref: CFG.compareUrl });
			if (!added && btn.closest('[data-compare-page]')) { location.reload(); }
		});
		on(document, 'click', '[data-compare-clear], [data-compare-clear-page]', function () { lists.compare = []; listSave('compare'); if ($('[data-compare-page]')) { location.href = CFG.compareUrl; } });
		on(document, 'click', '[data-action="share"]', function (e, btn) {
			e.preventDefault();
			var data = { title: btn.dataset.shareTitle || document.title, url: btn.dataset.shareUrl || location.href };
			if (navigator.share) { navigator.share(data).catch(function () {}); }
			else { copy(data.url); }
		});
		on(document, 'click', '[data-action="copy-link"], [data-copy]', function (e, btn) { e.preventDefault(); copy(btn.dataset.copy || btn.dataset.shareUrl || location.href); });
		on(document, 'click', '[data-action="notify"]', function (e, btn) {
			e.preventDefault();
			toast(t('notifyInfo', 'We will let you know when this product is back in stock.'), { type: 'default' });
			var tab = $('[data-tab-jump="reviews"]');
			if (tab) { tab.click(); }
		});
		on(document, 'click', '[data-action="quick-view"][data-id]', function (e, btn) { e.preventDefault(); openQuickView(btn.dataset.id, btn); });
		on(document, 'click', '[data-toggle-password]', function (e, btn) {
			var input = btn.parentElement.querySelector('input');
			if (!input) { return; }
			var show = input.type === 'password';
			input.type = show ? 'text' : 'password';
			btn.setAttribute('aria-pressed', String(show));
			var icon = $('.icon', btn);
			if (icon) { icon.classList.toggle('linear-icon-eye', !show); icon.classList.toggle('linear-icon-eye-crossed', show); }
		});
		on(document, 'click', '[data-auth-switch]', function (e, a) {
			var target = a.dataset.authSwitch, login = $('[data-auth-form="login"]'), reg = $('[data-auth-form="register"]');
			if (!login || !reg) { return; }
			e.preventDefault();
			login.hidden = target !== 'login'; reg.hidden = target !== 'register';
			history.replaceState(null, '', a.href);
			var first = $('input:not([type="hidden"])', target === 'login' ? login : reg);
			if (first) { first.focus(); }
		});
	}
	function copy(text) {
		var done = function () { toast(t('copied', 'Link copied'), { type: 'success' }); };
		if (navigator.clipboard) { navigator.clipboard.writeText(text).then(done).catch(function () { fallbackCopy(text); done(); }); }
		else { fallbackCopy(text); done(); }
	}
	function fallbackCopy(text) {
		var ta = document.createElement('textarea'); ta.value = text; ta.setAttribute('readonly', ''); ta.style.position = 'fixed'; ta.style.opacity = '0';
		document.body.appendChild(ta); ta.select();
		try { document.execCommand('copy'); } catch (e) { /* noop */ }
		ta.remove();
	}
	function openQuickView(id, trigger) {
		var dialog = $('[data-quick-view]'), body = $('[data-quick-view-body]');
		if (!dialog || !body) { return; }
		body.innerHTML = '<div class="quick-view__loading"><span class="spinner" aria-hidden="true"></span></div>';
		openModal(dialog);
		ajax('nexora_quick_view', { product_id: id }, 'GET').then(function (d) {
			body.innerHTML = d.html;
			syncPressed(body);
			$$('[data-qty]', body).forEach(updateQtyButtons);
			initVariantForms(body);
			if (window.jQuery && window.jQuery.fn.wc_variation_form) { window.jQuery('.variations_form', body).each(function () { window.jQuery(this).wc_variation_form(); }); }
			var f = $('button, a, input', body);
			if (f) { f.focus(); }
		}).catch(function (err) { body.innerHTML = '<div class="empty-state"><p>' + esc(err.message) + '</p></div>'; });
	}

	/* ------------------------------------------------------------------ */
	/* Search suggestions                                                 */
	/* ------------------------------------------------------------------ */

	function initSearch() {
		$$('[data-search]').forEach(function (box) {
			var input = $('[data-search-input]', box), panel = $('[data-search-suggest]', box), form = $('form', box), cat = $('[data-search-cat]', box);
			if (!input || !panel) { return; }
			var popular = I18N.popularTerms || [];
			var close = function () { box.classList.remove('is-open'); input.setAttribute('aria-expanded', 'false'); };
			var open = function () { box.classList.add('is-open'); input.setAttribute('aria-expanded', 'true'); };
			var searchUrl = function (q) { var u = new URL(CFG.homeUrl || '/', location.href); u.searchParams.set('s', q); if (CFG.woo) { u.searchParams.set('post_type', 'product'); } return u.toString(); };
			var renderPopular = function () {
				if (!popular.length) { close(); return; }
				panel.innerHTML = '<div class="search__suggest-title">' + esc(t('popular', 'Popular searches')) + '</div><div class="search__suggest-chips">' + popular.map(function (c) { return '<a class="chip" href="' + esc(searchUrl(c)) + '" role="option">' + esc(c) + '</a>'; }).join('') + '</div>';
				open();
			};
			var seq = 0;
			var render = function () {
				var q = input.value.trim();
				if (q.length < 2) { renderPopular(); return; }
				var my = ++seq;
				ajax('nexora_suggest', { q: q, cat: cat ? cat.value : '' }, 'GET').then(function (d) {
					if (my !== seq) { return; }
					if (!d.items.length) { panel.innerHTML = '<div class="search__suggest-empty">' + esc(t('noSuggestion', 'No matching products')) + '</div>'; }
					else {
						panel.innerHTML = '<div class="search__suggest-title">' + esc(t('suggested', 'Suggested products')) + '</div><div class="search__suggest-list">' +
							d.items.map(function (p) { return '<a class="search__suggest-item" href="' + esc(p.url) + '" role="option"><img src="' + esc(p.image) + '" alt="" width="40" height="40" loading="lazy"><span class="search__suggest-body"><span class="search__suggest-name">' + esc(p.title) + '</span><span class="search__suggest-cat">' + esc(p.category) + '</span></span><span class="search__suggest-price num">' + p.price + '</span></a>'; }).join('') +
							'</div><a class="search__suggest-item search__suggest-more fw-medium" href="' + esc(d.more || searchUrl(q)) + '" role="option">' + esc(t('searchFor', 'Search results for')) + ' «' + esc(q) + '»</a>';
					}
					open();
				}).catch(function () { close(); });
			};
			input.addEventListener('input', debounce(render, 180));
			input.addEventListener('focus', render);
			input.addEventListener('keydown', function (e) {
				var items = $$('[role="option"]', panel);
				if (e.key === 'Escape') { close(); return; }
				if (items.length && e.key === 'ArrowDown') { e.preventDefault(); items[0].focus(); }
			});
			panel.addEventListener('keydown', function (e) {
				var items = $$('[role="option"]', panel), idx = items.indexOf(document.activeElement);
				if (e.key === 'ArrowDown') { e.preventDefault(); var n = items[(idx + 1) % items.length]; if (n) { n.focus(); } }
				else if (e.key === 'ArrowUp') { e.preventDefault(); if (idx <= 0) { input.focus(); } else { items[idx - 1].focus(); } }
				else if (e.key === 'Escape') { close(); input.focus(); }
			});
			document.addEventListener('click', function (e) { if (!box.contains(e.target)) { close(); } });
			box.addEventListener('focusout', function (e) { if (!box.contains(e.relatedTarget)) { setTimeout(function () { if (!box.contains(document.activeElement)) { close(); } }, 0); } });
			if (form) { form.addEventListener('submit', function (e) { if (!input.value.trim()) { e.preventDefault(); input.focus(); } }); }
		});
	}

	/* ------------------------------------------------------------------ */
	/* Misc components                                                    */
	/* ------------------------------------------------------------------ */

	function pad2(n) { return String(n).padStart(2, '0'); }
	function initCountdowns() {
		$$('[data-countdown]').forEach(function (el) {
			var end;
			if (el.dataset.countdownUntil) { end = new Date(el.dataset.countdownUntil).getTime(); }
			else { var d0 = new Date(); d0.setHours(24, 0, 0, 0); end = d0.getTime(); }
			var d = $('[data-cd="d"]', el), h = $('[data-cd="h"]', el), m = $('[data-cd="m"]', el), s = $('[data-cd="s"]', el);
			var timer;
			var tick = function () {
				var diff = Math.max(0, end - Date.now());
				var days = Math.floor(diff / 864e5); diff -= days * 864e5;
				var hrs = Math.floor(diff / 36e5); diff -= hrs * 36e5;
				var mins = Math.floor(diff / 6e4); diff -= mins * 6e4;
				var secs = Math.floor(diff / 1e3);
				if (d) { d.textContent = digits(pad2(days)); }
				if (h) { h.textContent = digits(pad2(d ? hrs : hrs + days * 24)); }
				if (m) { m.textContent = digits(pad2(mins)); }
				if (s) { s.textContent = digits(pad2(secs)); }
				if (end - Date.now() <= 0) { el.classList.add('is-expired'); clearInterval(timer); }
			};
			tick();
			timer = setInterval(tick, 1000);
		});
	}
	function initReveal() {
		var nodes = $$('[data-reveal]');
		if (!nodes.length) { return; }
		if (reduceMotion() || !('IntersectionObserver' in window)) { nodes.forEach(function (n) { n.classList.add('is-visible'); }); return; }
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('is-visible'); io.unobserve(e.target); } });
		}, { rootMargin: '0px 0px -8% 0px', threshold: 0.05 });
		nodes.forEach(function (n) { io.observe(n); });
		// Safety net: never leave content hidden (print, throttled tabs, screenshot tools).
		setTimeout(function () { nodes.forEach(function (n) { n.classList.add('is-visible'); }); }, 1800);
	}
	function initTabs(root) {
		$$('[data-tabs]', root || document).forEach(function (tabs) {
			if (tabs.dataset.tabsReady) { return; }
			tabs.dataset.tabsReady = '1';
			var list = $('[role="tablist"]', tabs);
			if (!list) { return; }
			var btns = $$('[role="tab"]', list);
			var panels = btns.map(function (b) { return document.getElementById(b.getAttribute('aria-controls')); }).filter(Boolean);
			var activate = function (btn, focus) {
				btns.forEach(function (b) { var sel = b === btn; b.setAttribute('aria-selected', String(sel)); b.tabIndex = sel ? 0 : -1; b.classList.toggle('is-active', sel); });
				panels.forEach(function (p) { p.hidden = p.id !== btn.getAttribute('aria-controls'); });
				if (focus) { btn.focus(); }
				tabs.dispatchEvent(new CustomEvent('tabs:change', { bubbles: true, detail: { tab: btn.dataset.tab } }));
			};
			btns.forEach(function (b) {
				b.addEventListener('click', function () { activate(b); });
				b.addEventListener('keydown', function (e) {
					var i = btns.indexOf(b), next = isRTL ? 'ArrowLeft' : 'ArrowRight', prev = isRTL ? 'ArrowRight' : 'ArrowLeft';
					if (e.key === next) { e.preventDefault(); activate(btns[(i + 1) % btns.length], true); }
					else if (e.key === prev) { e.preventDefault(); activate(btns[(i - 1 + btns.length) % btns.length], true); }
					else if (e.key === 'Home') { e.preventDefault(); activate(btns[0], true); }
					else if (e.key === 'End') { e.preventDefault(); activate(btns[btns.length - 1], true); }
				});
			});
			var jump = function (name) {
				var b = btns.filter(function (x) { return x.dataset.tab === name; })[0];
				if (b) { activate(b); scrollToEl(tabs, 100); }
			};
			if (location.hash.indexOf('#tab-') === 0) { jump(location.hash.slice(5)); }
			if (location.hash === '#reviews' || location.hash === '#comments') { jump('reviews'); }
			on(document, 'click', '[data-tab-jump]', function (e, a) { e.preventDefault(); jump(a.dataset.tabJump); });
		});
	}
	function ajaxForm(form, action, opts) {
		opts = opts || {};
		form.addEventListener('submit', function (e) {
			if (form.hasAttribute('data-newsletter-external')) { return; }
			e.preventDefault();
			var status = $('[data-form-status]', form), btn = $('[type="submit"]', form);
			var fd = new FormData(form), data = {};
			fd.forEach(function (v, k) { data[k] = v; });
			if (opts.validate && !opts.validate(form, data)) { return; }
			if (btn) { btn.disabled = true; btn.classList.add('is-loading'); }
			ajax(action, data).then(function (d) {
				form.reset();
				if (status) { status.textContent = d.message || ''; status.className = 'form-status is-success'; }
				toast(d.message || t('sent', 'Sent'), { type: 'success' });
			}).catch(function (err) {
				if (status) { status.textContent = err.message; status.className = 'form-status is-error'; }
				toast(err.message, { type: 'error' });
			}).finally(function () { if (btn) { btn.disabled = false; btn.classList.remove('is-loading'); } });
		});
	}
	function initForms() {
		$$('[data-newsletter]').forEach(function (f) {
			ajaxForm(f, 'nexora_newsletter', { validate: function (form, data) {
				var input = $('input[type="email"]', form);
				if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email || '')) { input.setAttribute('aria-invalid', 'true'); input.focus(); toast(t('invalidEmail', 'Please enter a valid email.'), { type: 'error' }); return false; }
				input.removeAttribute('aria-invalid');
				return true;
			} });
		});
		$$('[data-contact-form]').forEach(function (f) {
			ajaxForm(f, 'nexora_contact', { validate: function (form) {
				var ok = true;
				$$('[required]', form).forEach(function (i) { var bad = !i.value.trim() || (i.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(i.value)); i.setAttribute('aria-invalid', String(bad)); if (bad && ok) { i.focus(); ok = false; } });
				return ok;
			} });
		});
	}

	/* ------------------------------------------------------------------ */
	/* Shop archive: filters, sorting, view                               */
	/* ------------------------------------------------------------------ */

	function initShop() {
		var shop = $('[data-shop]');
		if (!shop) { return; }
		var results = $('[data-shop-results]', shop);
		var host = $('[data-filters-host]', shop), drawerBody = $('[data-filters-drawer-body]');

		// Move filters into the off-canvas drawer on small screens.
		var placeFilters = function () {
			var form = $('[data-filters]');
			if (!form || !host || !drawerBody) { return; }
			if (mq('(max-width: 991.98px)').matches) { if (form.parentElement !== drawerBody) { drawerBody.appendChild(form); } }
			else if (form.parentElement !== host) { host.appendChild(form); }
		};
		placeFilters();
		mq('(max-width: 991.98px)').addEventListener('change', placeFilters);

		var currentView = storage('nx:shop-view') || shop.dataset.view;
		var setView = function (view) {
			shop.dataset.view = view;
			var grid = $('[data-shop-grid]', shop);
			if (grid) { grid.classList.toggle('product-grid--list', view === 'list'); }
			$$('[data-view]', shop).forEach(function (b) { if (b.tagName === 'BUTTON') { b.setAttribute('aria-pressed', String(b.dataset.view === view)); } });
			storage('nx:shop-view', view);
		};
		if (currentView) { setView(currentView); }
		on(shop, 'click', 'button[data-view]', function (e, b) { setView(b.dataset.view); });

		var buildUrl = function () {
			var form = $('[data-filters]');
			var url = new URL(form ? form.action : location.href, location.href);
			var params = new URLSearchParams();
			if (form) {
				new FormData(form).forEach(function (v, k) { if (v !== '' && v !== null) { params.append(k, v); } });
				var pr = $('[data-filter="price"]', form);
				if (pr) {
					if (Number(params.get('min_price')) <= Number(pr.dataset.min)) { params.delete('min_price'); }
					if (Number(params.get('max_price')) >= Number(pr.dataset.max)) { params.delete('max_price'); }
				}
			}
			var ob = new URL(location.href).searchParams.get('orderby');
			if (ob && !params.get('orderby')) { params.set('orderby', ob); }
			url.search = params.toString();
			return url;
		};
		var busy = false;
		var load = function (url, push) {
			if (busy) { return; }
			busy = true;
			results.setAttribute('aria-busy', 'true');
			shop.classList.add('is-loading');
			fetch(url.toString(), { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
				.then(function (r) { return r.text(); })
				.then(function (html) {
					var doc = new DOMParser().parseFromString(html, 'text/html');
					['[data-shop-results]', '[data-active-filters]', '[data-shop-pagination]', '[data-shop-count]', '[data-filters-count]', '.shop-hero, .archive-head'].forEach(function (sel) {
						var from = doc.querySelector(sel), to = shop.querySelector(sel);
						if (from && to) { to.replaceWith(from); }
					});
					var newForm = doc.querySelector('[data-filters]'), oldForm = $('[data-filters]');
					if (newForm && oldForm) { oldForm.replaceWith(newForm); placeFilters(); initPriceRange(); }
					$$('[data-sort]', shop).forEach(function (a) { var fromA = doc.querySelector('[data-sort="' + a.dataset.sort + '"]'); if (fromA) { a.classList.toggle('is-active', fromA.classList.contains('is-active')); a.href = fromA.href; } });
					var sel = $('[data-sort-select]', shop), fromSel = doc.querySelector('[data-sort-select]');
					if (sel && fromSel) { sel.value = fromSel.value; }
					if (push) { history.pushState({ nexoraShop: true }, '', url.toString()); }
					setView(shop.dataset.view);
					syncPressed(shop);
					initReveal();
					var count = $('[data-shop-count]', shop);
					if (count) { announce(count.textContent.trim()); }
					if (push) { scrollToEl(shop, 120); }
				})
				.catch(function () { location.href = url.toString(); })
				.finally(function () { busy = false; results.setAttribute('aria-busy', 'false'); shop.classList.remove('is-loading'); });
		};
		var apply = debounce(function () { load(buildUrl(), true); }, 250);

		on(document, 'change', '[data-filters] input, [data-filters] select', function (e, input) { if (input.type === 'range' || input.hasAttribute('data-price-input')) { return; } apply(); });
		on(document, 'submit', '[data-filters]', function (e) { e.preventDefault(); load(buildUrl(), true); });
		on(document, 'click', '[data-filters-clear]', function (e, a) { e.preventDefault(); var form = $('[data-filters]'); load(new URL(a.href || (form ? form.action : location.pathname), location.href), true); });
		on(shop, 'click', '[data-active-filters] a, [data-shop-pagination] a, [data-sort]', function (e, a) { e.preventDefault(); load(new URL(a.href, location.href), true); });
		on(shop, 'change', '[data-sort-select]', function (e, sel) { var u = new URL(location.href); u.searchParams.set('orderby', sel.value); u.searchParams.delete('paged'); u.pathname = u.pathname.replace(/\/page\/\d+\/?$/, '/'); load(u, true); });
		on(document, 'input', '[data-brand-search]', function (e, input) {
			var q = input.value.trim().toLowerCase();
			$$('[data-brand-item]', input.closest('.filter-group')).forEach(function (li) { li.hidden = q && li.dataset.brandItem.toLowerCase().indexOf(q) === -1; });
		});
		window.addEventListener('popstate', function (e) { if (e.state && e.state.nexoraShop) { load(new URL(location.href), false); } });

		var initPriceRange = function () {
			var box = $('[data-filter="price"]');
			if (!box) { return; }
			var minR = $('[name="min_price"]', box), maxR = $('[name="max_price"]', box), fill = $('[data-range-fill]', box);
			var minL = $('[data-range-min-label]', box), maxL = $('[data-range-max-label]', box);
			var minI = $('[data-price-input="min"]', box), maxI = $('[data-price-input="max"]', box);
			if (!minR || !maxR) { return; }
			var lo = Number(box.dataset.min), hi = Number(box.dataset.max), sym = box.dataset.currency || '';
			var fmt = function (v) { return digits(Number(v).toLocaleString('en-US')) + (sym ? ' ' + sym : ''); };
			var sync = function (fromInputs) {
				if (fromInputs) {
					var a = Number(String(minI.value).replace(/[^\d]/g, '')) || lo, b = Number(String(maxI.value).replace(/[^\d]/g, '')) || hi;
					minR.value = Math.max(lo, Math.min(a, hi)); maxR.value = Math.min(hi, Math.max(b, lo));
				}
				if (Number(minR.value) > Number(maxR.value)) { var tmp = minR.value; minR.value = maxR.value; maxR.value = tmp; }
				var p1 = ((minR.value - lo) / (hi - lo || 1)) * 100, p2 = ((maxR.value - lo) / (hi - lo || 1)) * 100;
				if (fill) { fill.style.insetInlineStart = p1 + '%'; fill.style.inlineSize = (p2 - p1) + '%'; }
				if (minL) { minL.textContent = fmt(minR.value); }
				if (maxL) { maxL.textContent = fmt(maxR.value); }
				if (minI && document.activeElement !== minI) { minI.value = digits(Number(minR.value).toLocaleString('en-US')); }
				if (maxI && document.activeElement !== maxI) { maxI.value = digits(Number(maxR.value).toLocaleString('en-US')); }
			};
			[minR, maxR].forEach(function (r) { r.addEventListener('input', function () { sync(false); }); r.addEventListener('change', function () { sync(false); apply(); }); });
			[minI, maxI].forEach(function (i) { if (i) { i.addEventListener('change', function () { sync(true); apply(); }); } });
			sync(false);
		};
		initPriceRange();
	}

	/* ------------------------------------------------------------------ */
	/* Single product                                                     */
	/* ------------------------------------------------------------------ */

	function initVariantForms(root) {
		$$('.variations_form', root || document).forEach(function (form) {
			if (form.dataset.nexoraVariants) { return; }
			form.dataset.nexoraVariants = '1';
			var groups = $$('[data-variant]', form);
			var setLabel = function (group) {
				var checked = $('input[type="radio"]:checked', group), label = $('[data-variant-value]', group);
				if (label) { label.textContent = checked ? (checked.dataset.label || checked.value) : ''; }
			};
			groups.forEach(function (group) {
				var select = $('select', group);
				on(group, 'change', 'input[type="radio"]', function (e, radio) {
					if (select) { select.value = radio.value; if (window.jQuery) { window.jQuery(select).trigger('change'); } else { select.dispatchEvent(new Event('change', { bubbles: true })); } }
					setLabel(group);
				});
				if (select) {
					select.addEventListener('change', function () {
						$$('input[type="radio"]', group).forEach(function (r) { r.checked = r.value === select.value; });
						setLabel(group);
					});
					// Disable radios that WooCommerce hides as unavailable.
					var observer = new MutationObserver(function () {
						$$('input[type="radio"]', group).forEach(function (r) {
							var opt = select.querySelector('option[value="' + CSS.escape(r.value) + '"]');
							var unavailable = !opt || opt.disabled;
							r.closest('.variant__option').classList.toggle('is-unavailable', unavailable);
						});
					});
					observer.observe(select, { childList: true, subtree: true, attributes: true });
				}
				setLabel(group);
			});
			if (window.jQuery) {
				window.jQuery(form).on('found_variation', function (e, variation) {
					var stock = $('[data-stock]', form.closest('[data-product-page]') || document);
					if (stock && variation.availability_html) { stock.innerHTML = variation.availability_html; }
					var img = $('[data-variation-image]');
					if (img && variation.image && variation.image.src) { img.src = variation.image.src; img.srcset = variation.image.srcset || ''; }
					var sticky = $('[data-sticky-buy] .price');
					if (sticky && variation.price_html) { sticky.outerHTML = variation.price_html; }
				}).on('reset_data', function () { groups.forEach(setLabel); });
			}
		});
	}
	function initProduct() {
		var page = $('.single-product-page[data-product-page]');
		initVariantForms(document);
		if (!page) { return; }

		// Gallery.
		var gallery = $('[data-gallery]', page);
		if (gallery && typeof window.Swiper === 'function') {
			var thumbsEl = $('[data-gallery-thumbs]', gallery), mainEl = $('[data-gallery-main]', gallery), thumbs = null;
			var vertical = mq('(min-width: 576px)').matches;
			if (thumbsEl) { thumbs = new window.Swiper(thumbsEl, { direction: vertical ? 'vertical' : 'horizontal', slidesPerView: vertical ? 5 : 4.5, spaceBetween: 8, watchSlidesProgress: true, freeMode: true, a11y: { enabled: true } }); }
			var main = new window.Swiper(mainEl, { slidesPerView: 1, speed: reduceMotion() ? 0 : 400, spaceBetween: 0, navigation: { prevEl: $('[data-gallery-prev]', gallery), nextEl: $('[data-gallery-next]', gallery) }, thumbs: thumbs ? { swiper: thumbs } : undefined, keyboard: { enabled: true, onlyInViewport: true }, a11y: { enabled: true } });
			if (thumbsEl) { on(thumbsEl, 'keydown', '.swiper-slide', function (e, s) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); main.slideTo($$('.swiper-slide', thumbsEl).indexOf(s)); } }); }
			if (window.PhotoSwipeLightbox && window.PhotoSwipe) {
				var lightbox = new window.PhotoSwipeLightbox({ gallery: mainEl, children: 'a[data-pswp-width]', pswpModule: window.PhotoSwipe, bgOpacity: 0.92, arrowPrevSVG: null, closeTitle: t('close', 'Close') });
				lightbox.on('close', function () { if (lightbox.pswp) { main.slideTo(lightbox.pswp.currIndex); } });
				lightbox.init();
			}
		}

		// Sticky buy bar.
		var sticky = $('[data-sticky-buy]'), buyBox = $('.buy-box', page);
		if (sticky && buyBox && 'IntersectionObserver' in window) {
			var io = new IntersectionObserver(function (entries) {
				var visible = entries[0].isIntersecting || entries[0].boundingClientRect.top > 0;
				sticky.classList.toggle('is-visible', !visible);
				sticky.setAttribute('aria-hidden', String(visible));
			}, { threshold: 0 });
			io.observe(buyBox);
		}

		// Reviews form toggle.
		var revToggle = $('[data-review-toggle]', page), revForm = $('[data-review-form]', page);
		var showReview = function (show) {
			if (!revForm) { return; }
			revForm.hidden = !show;
			if (revToggle) { revToggle.setAttribute('aria-expanded', String(show)); }
			if (show) { scrollToEl(revForm, 110); var f = $('textarea, input:not([type="hidden"])', revForm); if (f) { f.focus(); } }
		};
		if (revToggle) { revToggle.addEventListener('click', function () { showReview(revForm.hidden); }); }
		on(page, 'click', '[data-review-cancel]', function () { showReview(false); if (revToggle) { revToggle.focus(); } });
		if (location.hash === '#review_form' || location.hash === '#respond') { showReview(true); }
		on(page, 'click', '.comment-reply-link', function () { showReview(true); });

		// Star rating input (WooCommerce renders a select; we add radio-like buttons).
		var ratingSel = $('#rating', page);
		if (ratingSel) {
			ratingSel.classList.add('visually-hidden');
			var stars = document.createElement('div');
			stars.className = 'rating-input'; stars.setAttribute('role', 'radiogroup');
			for (var i = 1; i <= 5; i++) {
				var b = document.createElement('button');
				b.type = 'button'; b.className = 'rating-input__star'; b.dataset.value = i; b.setAttribute('aria-label', digits(i)); b.setAttribute('aria-pressed', 'false');
				b.innerHTML = '<svg aria-hidden="true" focusable="false"><use href="#i-star"></use></svg>';
				stars.appendChild(b);
			}
			ratingSel.insertAdjacentElement('afterend', stars);
			var paint = function (v) { $$('.rating-input__star', stars).forEach(function (s) { s.classList.toggle('is-on', Number(s.dataset.value) <= v); s.setAttribute('aria-pressed', String(Number(s.dataset.value) === v)); }); };
			on(stars, 'click', '.rating-input__star', function (e, s) { ratingSel.value = s.dataset.value; paint(Number(s.dataset.value)); });
			on(stars, 'mouseover', '.rating-input__star', function (e, s) { paint(Number(s.dataset.value)); });
			stars.addEventListener('mouseleave', function () { paint(Number(ratingSel.value || 0)); });
			paint(Number(ratingSel.value || 0));
		}
	}

	/* ------------------------------------------------------------------ */
	/* Wishlist & compare pages, account, checkout                        */
	/* ------------------------------------------------------------------ */

	function wishlistPageState() {
		var page = $('[data-wishlist-page]');
		if (!page) { return; }
		var n = $$('.wish-item', page).length;
		var filled = $('[data-wishlist-filled]', page), empty = $('[data-wishlist-empty]', page), actions = $('[data-wishlist-actions]', page), count = $('[data-wishlist-count]', page);
		if (filled) { filled.hidden = !n; }
		if (empty) { empty.hidden = !!n; }
		if (actions) { actions.hidden = !n; }
		if (count) { count.textContent = n ? '(' + digits(n) + ')' : ''; }
	}
	function initListPages() {
		['wishlist', 'compare'].forEach(function (type) {
			var page = $('[data-' + type + '-page]');
			if (!page) { return; }
			// Guests: the server can't know the list — reload once with ids from localStorage.
			if (page.dataset.loggedIn !== '1') {
				var u = new URL(location.href), have = u.searchParams.get('ids') || '', want = lists[type].join(',');
				if (have !== want) {
					if (want) { u.searchParams.set('ids', want); } else { u.searchParams.delete('ids'); }
					if (u.toString() !== location.href) { location.replace(u.toString()); return; }
				}
			}
		});
		var wl = $('[data-wishlist-page]');
		if (wl) {
			on(wl, 'click', '[data-wishlist-clear]', function () {
				var backup = lists.wishlist.slice();
				lists.wishlist = []; listSave('wishlist');
				$$('.wish-item', wl).forEach(function (i) { i.hidden = true; });
				wishlistPageState();
				toast(t('wishlistCleared', 'Wishlist cleared'), { onUndo: function () { lists.wishlist = backup; listSave('wishlist'); $$('.wish-item', wl).forEach(function (i) { i.hidden = false; }); wishlistPageState(); } });
				setTimeout(function () { $$('.wish-item[hidden]', wl).forEach(function (i) { i.remove(); }); }, 4200);
			});
			on(wl, 'click', '[data-wishlist-add-all]', function (e, btn) {
				var buttons = $$('.wish-item:not([hidden]) [data-action="add-to-cart"]', wl);
				if (!buttons.length) { return; }
				btn.disabled = true;
				var chain = Promise.resolve();
				buttons.forEach(function (b) { chain = chain.then(function () { return ajax('nexora_add_to_cart', { product_id: b.dataset.id, quantity: 1 }).then(function (d) { applyFragments(d.fragments); }).catch(function () {}); }); });
				chain.then(function () { btn.disabled = false; toast(t('addedToCart', 'Added to cart'), { type: 'success', action: t('viewCart', 'View cart'), actionHref: CFG.cartUrl }); });
			});
		}

		// Account: order filter tabs.
		var orders = $('[data-order-table]');
		if (orders) {
			on(document, 'click', '[data-order-filter]', function (e, tab) {
				$$('[data-order-filter]').forEach(function (x) { var sel = x === tab; x.classList.toggle('is-active', sel); x.setAttribute('aria-selected', String(sel)); });
				var f = tab.dataset.orderFilter, shown = 0;
				$$('tr[data-order-status]', orders).forEach(function (row) { var show = f === 'all' || row.dataset.orderStatus === f; row.hidden = !show; if (show) { shown++; } });
				var empty = $('[data-order-filter-empty]');
				if (empty) { empty.hidden = shown > 0; }
			});
		}
		on(document, 'click', '[data-reorder]', function (e, btn) {
			btn.disabled = true;
			ajax('nexora_reorder', { order_id: btn.dataset.reorder }).then(function (d) {
				applyFragments(d.fragments);
				toast(d.message || t('addedToCart', 'Added to cart'), { type: 'success', action: t('viewCart', 'View cart'), actionHref: CFG.cartUrl });
			}).catch(function (err) { toast(err.message, { type: 'error' }); }).finally(function () { btn.disabled = false; });
		});

		// Checkout: option-card radios drive WooCommerce's hidden checkbox.
		var checkout = $('[data-checkout-page]');
		if (checkout) {
			var cb = $('#ship-to-different-address-checkbox', checkout), addr = $('.shipping_address', checkout);
			var syncShip = function () {
				var mode = $('[data-ship-mode]:checked', checkout);
				if (!cb || !mode) { return; }
				var diff = mode.value === 'different';
				if (cb.checked !== diff) { cb.checked = diff; if (window.jQuery) { window.jQuery(cb).trigger('change'); } else { cb.dispatchEvent(new Event('change', { bubbles: true })); } }
				if (addr && !window.jQuery) { addr.style.display = diff ? '' : 'none'; }
			};
			on(checkout, 'change', '[data-ship-mode]', syncShip);
			syncShip();
			on(checkout, 'change', '.option-card input[type="radio"]', function (e, r) {
				var list = r.closest('.option-cards');
				if (list) { $$('.option-card', list).forEach(function (c) { c.classList.toggle('is-selected', $('input[type="radio"]', c) === r && r.checked); }); }
			});
			$$('.option-card input[type="radio"]:checked', checkout).forEach(function (r) { r.closest('.option-card').classList.add('is-selected'); });
			if (window.jQuery) { window.jQuery(document.body).on('updated_checkout payment_method_selected', function () { $$('.option-card', checkout).forEach(function (c) { var r = $('input[type="radio"]', c); c.classList.toggle('is-selected', !!(r && r.checked)); }); }); }
		}
	}

	/* ------------------------------------------------------------------ */
	/* Boot                                                               */
	/* ------------------------------------------------------------------ */

	function boot() {
		initToasts();
		initHeader();
		initDrawers();
		initSwipers();
		initSearch();
		initCountdowns();
		initReveal();
		initTabs();
		initForms();
		initActions();
		if (CFG.woo) {
			initCart();
			initShop();
			initProduct();
			initListPages();
			listsInit();
		}
		renderBadges();
		syncPressed(document);
		renderCompareBar();
		document.documentElement.classList.add('js-ready');
	}

	if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }

	window.Nexora = { toast: toast, ajax: ajax, openDrawer: drawerOpen, closeDrawer: drawerClose, openModal: openModal, initSwipers: initSwipers, lists: lists, digits: digits };
})();
