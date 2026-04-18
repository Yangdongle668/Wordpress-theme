/**
 * VoltCore v3 — advanced widget interactions.
 *
 * Self-contained. Runs after DOM ready. All selectors are scoped to the
 * data-attributes emitted by the v3 widgets, so this file is safe to
 * enqueue globally — if a widget isn't on the page, its binder is a
 * no-op.
 *
 * Binders registered in this file:
 *   - Panel video play/pause toggle
 *   - Mega Nav: panel open/close on hover + click, scrim, mobile drawer
 *   - Account Drawer: open/close + tab switching
 */

(function () {
	"use strict";

	const qs  = (sel, ctx) => (ctx || document).querySelector(sel);
	const qsa = (sel, ctx) => Array.from((ctx || document).querySelectorAll(sel));
	const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

	const lockScroll   = () => document.body.classList.add("vc-scroll-locked");
	const unlockScroll = () => document.body.classList.remove("vc-scroll-locked");

	document.addEventListener("DOMContentLoaded", init);

	function init() {
		bindPanelVideo();
		bindMegaNav();
		bindAccountDrawer();
	}

	/* ================================================================
	 * 1. Panel video play/pause toggle.
	 * Auto-pauses when the panel leaves the viewport to save CPU.
	 * ================================================================ */
	function bindPanelVideo() {
		qsa("[data-vc-video]").forEach(video => {
			// pause offscreen
			if ("IntersectionObserver" in window) {
				const io = new IntersectionObserver(entries => {
					entries.forEach(e => {
						if (!video.dataset.userPaused) {
							if (e.isIntersecting) video.play().catch(() => {});
							else video.pause();
						}
					});
				}, { threshold: 0.25 });
				io.observe(video);
			}
		});

		qsa("[data-vc-video-toggle]").forEach(btn => {
			btn.addEventListener("click", () => {
				const video = qs("[data-vc-video]", btn.closest(".vc-panel"));
				if (!video) return;
				if (video.paused) {
					video.play().catch(() => {});
					delete video.dataset.userPaused;
					btn.classList.remove("is-paused");
					btn.setAttribute("aria-label", "Pause video");
				} else {
					video.pause();
					video.dataset.userPaused = "1";
					btn.classList.add("is-paused");
					btn.setAttribute("aria-label", "Play video");
				}
			});
		});
	}

	/* ================================================================
	 * 2. Mega Nav — open mega panel on trigger click, keep open while
	 * hovering inside, close on outside click or escape.
	 * ================================================================ */
	function bindMegaNav() {
		qsa("[data-vc-mega]").forEach(nav => {
			const triggers = qsa("[data-vc-mega-trigger]", nav);
			const panels   = qsa("[data-vc-mega-panel]", nav);
			const scrim    = qs("[data-vc-mega-scrim]", nav);

			let openId  = null;
			let hoverTimer;

			function closeAll() {
				panels.forEach(p => { p.setAttribute("data-open", "false"); p.hidden = true; });
				triggers.forEach(t => t.setAttribute("aria-expanded", "false"));
				nav.classList.remove("is-panel-open");
				openId = null;
			}
			function open(id) {
				const panel = panels.find(p => p.id === id);
				if (!panel) return;
				panels.forEach(p => {
					const match = p.id === id;
					p.hidden = !match;
					p.setAttribute("data-open", match ? "true" : "false");
				});
				triggers.forEach(t => t.setAttribute("aria-expanded", t.getAttribute("data-vc-mega-trigger") === id ? "true" : "false"));
				nav.classList.add("is-panel-open");
				openId = id;
			}

			triggers.forEach(t => {
				const id = t.getAttribute("data-vc-mega-trigger");
				t.addEventListener("click", e => {
					e.preventDefault();
					if (openId === id) closeAll();
					else open(id);
				});
				t.addEventListener("mouseenter", () => {
					clearTimeout(hoverTimer);
					if (window.matchMedia("(hover: hover)").matches && window.innerWidth > 900) open(id);
				});
			});

			panels.forEach(p => {
				p.addEventListener("mouseleave", () => {
					if (!window.matchMedia("(hover: hover)").matches) return;
					hoverTimer = setTimeout(closeAll, 150);
				});
				p.addEventListener("mouseenter", () => clearTimeout(hoverTimer));
			});
			nav.addEventListener("mouseleave", () => {
				if (!window.matchMedia("(hover: hover)").matches) return;
				hoverTimer = setTimeout(closeAll, 200);
			});

			if (scrim) scrim.addEventListener("click", closeAll);
			document.addEventListener("keydown", e => { if (e.key === "Escape" && openId) closeAll(); });
		});
	}

	/* ================================================================
	 * 3. Account Drawer — open on any [data-vc-account-open], close on
	 * scrim / close button / Escape. Simple tabs inside.
	 * ================================================================ */
	function bindAccountDrawer() {
		const drawer = qs("[data-vc-account]");
		if (!drawer) return;
		let lastFocus = null;

		function openDrawer() {
			lastFocus = document.activeElement;
			drawer.hidden = false;
			lockScroll();
			setTimeout(() => {
				const first = qs("input, button, [tabindex]", drawer);
				if (first) first.focus();
			}, 50);
		}
		function closeDrawer() {
			drawer.hidden = true;
			unlockScroll();
			if (lastFocus && typeof lastFocus.focus === "function") lastFocus.focus();
		}

		qsa("[data-vc-account-open]").forEach(btn => btn.addEventListener("click", e => {
			e.preventDefault();
			openDrawer();
		}));
		qsa("[data-vc-account-close]", drawer).forEach(btn => btn.addEventListener("click", closeDrawer));

		document.addEventListener("keydown", e => {
			if (e.key === "Escape" && !drawer.hidden) closeDrawer();
		});

		// Tabs
		const tabs   = qsa("[data-vc-tab]", drawer);
		const panels = qsa("[data-vc-tab-panel]", drawer);
		tabs.forEach(tab => {
			tab.addEventListener("click", () => {
				const target = tab.getAttribute("data-vc-tab");
				tabs.forEach(t => t.setAttribute("aria-selected", t === tab ? "true" : "false"));
				panels.forEach(p => p.classList.toggle("is-active", p.getAttribute("data-vc-tab-panel") === target));
			});
		});
	}

	// Expose a small helper for other modules that may want to lock scroll
	window.VoltCoreV3 = Object.assign(window.VoltCoreV3 || {}, {
		lockScroll, unlockScroll, prefersReducedMotion
	});
})();
