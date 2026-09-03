/**
 * Customizer live preview (site title / tagline).
 *
 * @package Nexora
 */
/* global wp */
(function () {
	'use strict';
	if (!window.wp || !wp.customize) { return; }
	wp.customize('blogname', function (value) {
		value.bind(function (to) {
			document.querySelectorAll('.brand__text > span:first-child').forEach(function (el) { el.textContent = to; });
		});
	});
	wp.customize('blogdescription', function (value) {
		value.bind(function (to) {
			document.querySelectorAll('.brand__tag').forEach(function (el) { el.textContent = to; });
		});
	});
})();
