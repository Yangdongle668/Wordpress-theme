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
		bindLegalToc();
		bindVoltcoreNav();
	}

	/* --------------------------------------------------------------------
	 * VoltCore Navbar widget: transparent → solid on scroll, drawer toggle.
	 * Mirrors the behaviour of the classic header but scoped to the
	 * Elementor-built .vc-nav element so it works wherever editors drop
	 * the widget.
	 * -------------------------------------------------------------------- */
	function bindVoltcoreNav() {
		qsa("[data-vc-nav]").forEach(nav => {
			const mode = nav.getAttribute("data-mode") || "transparent";
			const hasHero = !!qs(".hero, .about-hero, .careers-hero, .product-hero, .single-hero, .page-header--image, .elementor-section-full_width");

			function update() {
				if (mode === "solid") { nav.classList.add("is-solid"); return; }
				if (mode === "transparent-always") { nav.classList.remove("is-solid"); return; }
				if (!hasHero || window.scrollY > 20) {
					nav.classList.add("is-solid");
				} else {
					nav.classList.remove("is-solid");
				}
			}
			update();
			window.addEventListener("scroll", update, { passive: true });
			window.addEventListener("resize", update, { passive: true });
		});

		qsa("[data-vc-nav-toggle]").forEach(btn => {
			btn.addEventListener("click", () => {
				const drawer = qs("[data-vc-nav-drawer]");
				if (!drawer) return;
				drawer.hidden = false;
				const open = drawer.classList.toggle("is-open");
				btn.classList.toggle("is-open", open);
				btn.setAttribute("aria-expanded", open ? "true" : "false");
				document.body.style.overflow = open ? "hidden" : "";
			});
		});
		qsa("[data-vc-nav-drawer] a").forEach(a => {
			a.addEventListener("click", () => {
				const drawer = qs("[data-vc-nav-drawer]");
				const btn = qs("[data-vc-nav-toggle]");
				if (drawer) drawer.classList.remove("is-open");
				if (btn) { btn.classList.remove("is-open"); btn.setAttribute("aria-expanded", "false"); }
				document.body.style.overflow = "";
			});
		});
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
	 * Legal TOC — scrape h2 headings in .entry-content, generate a
	 * numbered anchor list in [data-legal-toc], and highlight the
	 * active section as the reader scrolls.
	 * -------------------------------------------------------------------- */
	function bindLegalToc() {
		const toc = qs("[data-legal-toc]");
		const content = qs("[data-legal-content]");
		if (!toc || !content) return;

		const headings = qsa("h2", content).filter(h => h.textContent.trim() !== "");
		if (!headings.length) {
			const aside = toc.closest(".legal-toc");
			if (aside) aside.style.display = "none";
			return;
		}

		headings.forEach((h, i) => {
			if (!h.id) {
				const slug = h.textContent.trim().toLowerCase()
					.replace(/[^\w\s-]/g, "")
					.replace(/\s+/g, "-")
					.replace(/-+/g, "-")
					.slice(0, 60) || ("section-" + (i + 1));
				h.id = slug;
			}
			const li = document.createElement("li");
			const a  = document.createElement("a");
			a.href = "#" + h.id;
			a.textContent = h.textContent.trim();
			a.setAttribute("data-toc-link", h.id);
			li.appendChild(a);
			toc.appendChild(li);
		});

		if (!("IntersectionObserver" in window) || prefersReducedMotion) return;

		const links = qsa("[data-toc-link]", toc);
		const io = new IntersectionObserver((entries) => {
			entries.forEach(entry => {
				const id = entry.target.id;
				const link = links.find(l => l.getAttribute("data-toc-link") === id);
				if (!link) return;
				if (entry.isIntersecting) {
					links.forEach(l => l.classList.remove("is-active"));
					link.classList.add("is-active");
				}
			});
		}, { rootMargin: "-30% 0px -60% 0px", threshold: 0 });

		headings.forEach(h => io.observe(h));
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
