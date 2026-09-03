/**
 * Dismissible contextual help boxes on core admin screens.
 *
 * @package Nexora
 */
/* global NEXORA_HINTS */
(function () {
	'use strict';
	var cfg = window.NEXORA_HINTS || {};
	document.addEventListener('click', function (e) {
		var btn = e.target.closest('.nexora-hint .notice-dismiss, .nexora-hint [data-dismiss]');
		if (!btn) { return; }
		var box = btn.closest('.nexora-hint');
		if (!box || !box.dataset.hint) { return; }
		var body = new URLSearchParams({ action: 'nexora_dismiss_hint', hint: box.dataset.hint, nonce: cfg.nonce || '' });
		fetch(cfg.ajaxUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body.toString() }).catch(function () {});
	}, true);
})();
