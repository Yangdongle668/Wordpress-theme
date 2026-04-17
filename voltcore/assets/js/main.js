/**
 * VoltCore theme scripts.
 * - Header: transparent → solid on scroll.
 * - Mobile drawer toggle.
 * - IntersectionObserver fade-in for [data-fade] elements.
 * - Animated counter-up for [data-count] values.
 * - Scroll hint button jumps to next section.
 */

(function () {
	"use strict";

	const qs  = (sel, ctx) => (ctx || document).querySelector(sel);
	const qsa = (sel, ctx) => Array.from((ctx || document).querySelectorAll(sel));
	const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

	document.addEventListener("DOMContentLoaded", init);

	function init() {
		bindHeader();
		bindMobileDrawer();
		bindFadeIn();
		bindCounters();
		bindScrollHints();
	}

	/* --------------------------------------------------------------------
	 * Header: add .is-solid after user scrolls past ~20px. If we're on a
	 * page without a full-bleed hero (blog, single, page), make it solid
	 * immediately so the text stays readable on white backgrounds.
	 * -------------------------------------------------------------------- */
	function bindHeader() {
		const header = qs("[data-header]");
		if (!header) return;

		const hasHero = !!qs(".hero, .single-hero, .page-header--image");

		const update = () => {
			if (!hasHero || window.scrollY > 20) {
				header.classList.add("is-solid");
			} else {
				header.classList.remove("is-solid");
			}
		};

		update();
		window.addEventListener("scroll", update, { passive: true });
		window.addEventListener("resize", update, { passive: true });
	}

	/* --------------------------------------------------------------------
	 * Mobile drawer.
	 * -------------------------------------------------------------------- */
	function bindMobileDrawer() {
		const toggle = qs("[data-nav-toggle]");
		const drawer = qs("[data-drawer]");
		if (!toggle || !drawer) return;

		drawer.hidden = false;

		toggle.addEventListener("click", () => {
			const open = drawer.classList.toggle("is-open");
			toggle.classList.toggle("is-open", open);
			toggle.setAttribute("aria-expanded", open ? "true" : "false");
			document.body.style.overflow = open ? "hidden" : "";
		});

		qsa("a", drawer).forEach(a => {
			a.addEventListener("click", () => {
				drawer.classList.remove("is-open");
				toggle.classList.remove("is-open");
				toggle.setAttribute("aria-expanded", "false");
				document.body.style.overflow = "";
			});
		});
	}

	/* --------------------------------------------------------------------
	 * Fade-in on scroll.
	 * -------------------------------------------------------------------- */
	function bindFadeIn() {
		const nodes = qsa("[data-fade]");
		if (!nodes.length) return;

		if (prefersReducedMotion || !("IntersectionObserver" in window)) {
			nodes.forEach(n => n.classList.add("is-visible"));
			return;
		}

		const io = new IntersectionObserver((entries) => {
			entries.forEach(entry => {
				if (entry.isIntersecting) {
					const el = entry.target;
					const delay = parseInt(el.getAttribute("data-fade-delay") || "0", 10);
					if (delay > 0) {
						el.style.transitionDelay = delay + "ms";
					}
					el.classList.add("is-visible");
					io.unobserve(el);
				}
			});
		}, { threshold: 0.12, rootMargin: "0px 0px -40px 0px" });

		nodes.forEach(n => io.observe(n));
	}

	/* --------------------------------------------------------------------
	 * Animated counters.
	 * Values may be numeric ("640", "1.2", "99.98"). We keep the original
	 * formatting (decimals) and only animate numeric part.
	 * -------------------------------------------------------------------- */
	function bindCounters() {
		const nodes = qsa("[data-count]");
		if (!nodes.length || prefersReducedMotion || !("IntersectionObserver" in window)) return;

		const io = new IntersectionObserver((entries) => {
			entries.forEach(entry => {
				if (entry.isIntersecting) {
					animateCount(entry.target);
					io.unobserve(entry.target);
				}
			});
		}, { threshold: 0.4 });

		nodes.forEach(n => io.observe(n));
	}

	function animateCount(el) {
		const raw = (el.getAttribute("data-count") || "").trim();
		const match = raw.match(/^(-?\d+(?:\.\d+)?)(.*)$/);
		if (!match) return;
		const target = parseFloat(match[1]);
		const suffix = match[2] || "";
		const decimals = (match[1].split(".")[1] || "").length;
		const duration = 1400;
		const start = performance.now();

		function frame(now) {
			const t = Math.min(1, (now - start) / duration);
			const eased = 1 - Math.pow(1 - t, 3); // easeOutCubic
			const value = target * eased;
			el.textContent = value.toFixed(decimals) + suffix;
			if (t < 1) requestAnimationFrame(frame);
			else el.textContent = target.toFixed(decimals) + suffix;
		}
		requestAnimationFrame(frame);
	}

	/* --------------------------------------------------------------------
	 * Scroll hint buttons — jump to next section.
	 * -------------------------------------------------------------------- */
	function bindScrollHints() {
		qsa("[data-scroll-hint]").forEach(btn => {
			btn.addEventListener("click", () => {
				const section = btn.closest(".hero, section");
				if (!section) return;
				const next = section.nextElementSibling;
				if (next && typeof next.scrollIntoView === "function") {
					next.scrollIntoView({ behavior: prefersReducedMotion ? "auto" : "smooth", block: "start" });
				}
			});
		});
	}
})();
