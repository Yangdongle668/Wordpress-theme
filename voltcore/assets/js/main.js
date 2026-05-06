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
		bindMegaMenu();
		bindVehiclePanels();
		bindModelGallery();
		bindModelSpecs();
		bindConfigurator();
		bindSavingsCalc();
		bindSuperchargerMap();
		bindInventory();
		bindTestDrive();
		bindModals();
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
				const section = btn.closest(".hero, section, .vc-vpanel");
				if (!section) return;
				const next = section.nextElementSibling;
				if (next && typeof next.scrollIntoView === "function") {
					next.scrollIntoView({ behavior: prefersReducedMotion ? "auto" : "smooth", block: "start" });
				}
			});
		});
	}

	/* --------------------------------------------------------------------
	 * Mega-menu: desktop hover/focus opens the panel matching data-panel.
	 * Mobile (≤900px) falls back to drawer.
	 * -------------------------------------------------------------------- */
	function bindMegaMenu() {
		qsa("[data-vc-mega-menu]").forEach(menu => {
			const nav = menu.closest(".vc-nav");
			if (!nav) return;
			const items = qsa(".vc-nav__item.has-panel", menu);
			const panels = qsa("[data-mega-panel]", nav);
			let openId = null;
			let leaveTimer = null;

			function open(id) {
				clearTimeout(leaveTimer);
				if (openId === id) return;
				openId = id;
				panels.forEach(p => {
					const on = p.id === id;
					p.hidden = !on;
					p.classList.toggle("is-open", on);
				});
				nav.classList.add("has-mega-open");
			}
			function close() {
				openId = null;
				panels.forEach(p => { p.classList.remove("is-open"); });
				setTimeout(() => panels.forEach(p => { if (!p.classList.contains("is-open")) p.hidden = true; }), 320);
				nav.classList.remove("has-mega-open");
			}
			function scheduleClose() {
				clearTimeout(leaveTimer);
				leaveTimer = setTimeout(close, 220);
			}

			items.forEach(li => {
				const id = li.getAttribute("data-panel");
				li.addEventListener("mouseenter", () => open(id));
				li.addEventListener("focusin", () => open(id));
				li.addEventListener("mouseleave", scheduleClose);
			});
			panels.forEach(p => {
				p.addEventListener("mouseenter", () => { clearTimeout(leaveTimer); });
				p.addEventListener("mouseleave", scheduleClose);
			});
			document.addEventListener("keydown", e => { if (e.key === "Escape") close(); });
			window.addEventListener("scroll", close, { passive: true });
		});
	}

	/* --------------------------------------------------------------------
	 * Vehicle Panels: autoplay videos when their panel enters the viewport.
	 * -------------------------------------------------------------------- */
	function bindVehiclePanels() {
		const videos = qsa(".vc-vpanel__media video");
		if (!videos.length || !("IntersectionObserver" in window)) return;
		const io = new IntersectionObserver(entries => {
			entries.forEach(entry => {
				const v = entry.target;
				if (entry.isIntersecting) {
					v.play && v.play().catch(() => {});
				} else {
					v.pause && v.pause();
				}
			});
		}, { threshold: 0.4 });
		videos.forEach(v => io.observe(v));
	}

	/* --------------------------------------------------------------------
	 * Model Gallery: pin one image while features scroll past, swap the
	 * active image based on which feature block is centered in viewport.
	 * -------------------------------------------------------------------- */
	function bindModelGallery() {
		qsa("[data-vc-modelgal]").forEach(root => {
			const features = qsa(".vc-modelgal__feature", root);
			const stack = qsa(".vc-modelgal__media-stack > *", root);
			if (!features.length || !stack.length) return;

			function setActive(idx) {
				stack.forEach((el, i) => el.classList.toggle("is-active", i === idx));
			}
			setActive(0);

			if (!("IntersectionObserver" in window)) return;
			const io = new IntersectionObserver(entries => {
				entries.forEach(entry => {
					if (entry.isIntersecting) {
						const idx = features.indexOf(entry.target);
						const target = Math.min(idx, stack.length - 1);
						if (target >= 0) setActive(target);
					}
				});
			}, { rootMargin: "-40% 0px -40% 0px", threshold: 0 });
			features.forEach(f => io.observe(f));
		});
	}

	/* --------------------------------------------------------------------
	 * Model Specs: simple tab switcher.
	 * -------------------------------------------------------------------- */
	function bindModelSpecs() {
		qsa("[data-vc-mspecs]").forEach(root => {
			const tabs   = qsa(".vc-mspecs__tab", root);
			const panels = qsa(".vc-mspecs__panel", root);
			if (!tabs.length || !panels.length) return;
			tabs.forEach((tab, i) => {
				tab.addEventListener("click", () => {
					tabs.forEach(t => t.classList.remove("is-active"));
					panels.forEach(p => p.classList.remove("is-active"));
					tab.classList.add("is-active");
					if (panels[i]) panels[i].classList.add("is-active");
				});
			});
		});
	}

	/* --------------------------------------------------------------------
	 * Configurator (Design Studio): paint / wheels / interior / autopilot.
	 * Reads JSON from a script[type=application/json] inside the widget.
	 * -------------------------------------------------------------------- */
	function bindConfigurator() {
		qsa("[data-vc-config]").forEach(root => {
			const dataNode = qs("script[type='application/json']", root);
			if (!dataNode) return;
			let data;
			try { data = JSON.parse(dataNode.textContent); } catch (e) { return; }

			const stage = qs(".vc-config__stage-stack", root);
			const priceEl = qs("[data-config-price]", root);
			const ctaEl = qs("[data-config-cta]", root);
			const state = {
				paint: 0,
				wheel: 0,
				interior: 0,
				autopilot: 0,
			};

			function format(n) { return "$" + Math.round(n).toLocaleString(); }

			function render() {
				if (stage) {
					const imgs = qsa("img", stage);
					const want = (data.paints[state.paint] && data.paints[state.paint].image)
						|| (data.image || "");
					imgs.forEach(img => img.classList.toggle("is-active", img.dataset.paint == state.paint));
				}
				const total = data.basePrice
					+ (data.paints[state.paint]?.price || 0)
					+ (data.wheels[state.wheel]?.price || 0)
					+ (data.interiors[state.interior]?.price || 0)
					+ (data.autopilots[state.autopilot]?.price || 0);
				if (priceEl) priceEl.textContent = format(total);
				if (ctaEl) {
					const params = new URLSearchParams({
						paint: data.paints[state.paint]?.id || "",
						wheel: data.wheels[state.wheel]?.id || "",
						interior: data.interiors[state.interior]?.id || "",
						autopilot: data.autopilots[state.autopilot]?.id || "",
					});
					ctaEl.href = (ctaEl.dataset.base || "#") + "?" + params.toString();
				}
				qsa(".vc-config__paint", root).forEach((b, i) => b.classList.toggle("is-active", i === state.paint));
				qsa("[data-config-group='wheel'] .vc-config__option", root).forEach((b, i) => b.classList.toggle("is-active", i === state.wheel));
				qsa("[data-config-group='interior'] .vc-config__option", root).forEach((b, i) => b.classList.toggle("is-active", i === state.interior));
				qsa("[data-config-group='autopilot'] .vc-config__option", root).forEach((b, i) => b.classList.toggle("is-active", i === state.autopilot));
			}

			qsa(".vc-config__paint", root).forEach((b, i) => b.addEventListener("click", () => { state.paint = i; render(); }));
			qsa("[data-config-group='wheel'] .vc-config__option", root).forEach((b, i) => b.addEventListener("click", () => { state.wheel = i; render(); }));
			qsa("[data-config-group='interior'] .vc-config__option", root).forEach((b, i) => b.addEventListener("click", () => { state.interior = i; render(); }));
			qsa("[data-config-group='autopilot'] .vc-config__option", root).forEach((b, i) => b.addEventListener("click", () => { state.autopilot = i; render(); }));

			render();
		});
	}

	/* --------------------------------------------------------------------
	 * Savings calculator (Powerwall / Solar).
	 * -------------------------------------------------------------------- */
	function bindSavingsCalc() {
		qsa("[data-vc-calc]").forEach(root => {
			const usageEl = qs("[data-calc-usage]", root);
			const billEl  = qs("[data-calc-bill]", root);
			const sunEl   = qs("[data-calc-sun]", root);
			const sysEl   = qs("[data-calc-system]", root);
			const outYear = qs("[data-calc-out-year]", root);
			const outLife = qs("[data-calc-out-life]", root);
			const outCo2  = qs("[data-calc-out-co2]", root);
			const outPay  = qs("[data-calc-out-payback]", root);

			function compute() {
				const usage = parseFloat(usageEl?.value || 900);
				const bill  = parseFloat(billEl?.value  || 180);
				const sun   = parseFloat(sunEl?.value   || 5.0);
				const system = parseFloat(sysEl?.value  || 12000);
				const ratePerKwh = bill / usage;
				const annualKwh = sun * 365 * (system / 1000) * 0.85;
				const annualSavings = Math.min(annualKwh * ratePerKwh, bill * 12);
				const lifetime = annualSavings * 25;
				const co2 = annualKwh * 0.0007 * 25;
				const payback = system / Math.max(annualSavings, 1);
				if (outYear) outYear.textContent = "$" + Math.round(annualSavings).toLocaleString();
				if (outLife) outLife.textContent = "$" + Math.round(lifetime).toLocaleString();
				if (outCo2)  outCo2.textContent  = co2.toFixed(1) + " t";
				if (outPay)  outPay.textContent  = payback.toFixed(1) + " yr";
			}
			[usageEl, billEl, sunEl, sysEl].forEach(el => el && el.addEventListener("input", compute));
			compute();
		});
	}

	/* --------------------------------------------------------------------
	 * Supercharger map. If Leaflet is on the page (vc-leaflet enqueued
	 * by the widget), render an interactive map; otherwise fall back to
	 * a static list of locations.
	 * -------------------------------------------------------------------- */
	function bindSuperchargerMap() {
		qsa("[data-vc-map]").forEach(root => {
			const dataNode = qs("script[type='application/json']", root);
			if (!dataNode) return;
			let stations;
			try { stations = JSON.parse(dataNode.textContent); } catch (e) { return; }

			const canvas = qs(".vc-map__canvas", root);
			const list   = qs("[data-map-list]", root);
			const search = qs("[data-map-search]", root);
			const chips  = qsa(".vc-map__chip", root);
			let filter = "all";
			let term = "";
			let leafMap = null;
			let markers = {};

			function visible(s) {
				if (filter !== "all" && s.type !== filter) return false;
				if (term && !(s.name + " " + (s.address || "")).toLowerCase().includes(term)) return false;
				return true;
			}

			function renderList() {
				if (!list) return;
				list.innerHTML = "";
				stations.filter(visible).forEach(s => {
					const li = document.createElement("li");
					li.className = "vc-map__item";
					li.innerHTML = '<p class="vc-map__item-name"></p><p class="vc-map__item-meta"></p>';
					li.querySelector(".vc-map__item-name").textContent = s.name;
					li.querySelector(".vc-map__item-meta").textContent = (s.stalls ? s.stalls + " stalls · " : "") + (s.address || "");
					li.addEventListener("click", () => {
						if (leafMap && markers[s.id]) {
							leafMap.setView([s.lat, s.lng], 12, { animate: true });
							markers[s.id].openPopup && markers[s.id].openPopup();
						}
					});
					list.appendChild(li);
				});
			}

			function renderMarkers() {
				if (!leafMap) return;
				Object.values(markers).forEach(m => leafMap.removeLayer(m));
				markers = {};
				stations.filter(visible).forEach(s => {
					const icon = window.L.divIcon({
						className: "",
						html: '<div class="vc-map__pin' + (s.type === "v3" ? " vc-map__pin--v3" : "") + '">' + (s.stalls || "") + '</div>',
						iconSize: [24, 24],
						iconAnchor: [12, 12],
					});
					const m = window.L.marker([s.lat, s.lng], { icon }).addTo(leafMap);
					m.bindPopup("<b>" + s.name + "</b><br>" + (s.address || "") + "<br>" + (s.stalls || 0) + " stalls");
					markers[s.id] = m;
				});
			}

			if (window.L && canvas) {
				leafMap = window.L.map(canvas, { zoomControl: true, scrollWheelZoom: false }).setView(
					[stations[0]?.lat || 39.5, stations[0]?.lng || -98.5],
					stations.length > 1 ? 4 : 11
				);
				window.L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
					attribution: "&copy; OpenStreetMap contributors", maxZoom: 19,
				}).addTo(leafMap);
				renderMarkers();
			}
			renderList();

			if (search) search.addEventListener("input", () => { term = search.value.trim().toLowerCase(); renderList(); renderMarkers(); });
			chips.forEach(c => c.addEventListener("click", () => {
				chips.forEach(x => x.classList.remove("is-active"));
				c.classList.add("is-active");
				filter = c.getAttribute("data-filter") || "all";
				renderList(); renderMarkers();
			}));
		});
	}

	/* --------------------------------------------------------------------
	 * Inventory: client-side filter + count.
	 * -------------------------------------------------------------------- */
	function bindInventory() {
		qsa("[data-vc-inv]").forEach(root => {
			const cards   = qsa(".vc-inv__card", root);
			const count   = qs("[data-inv-count]", root);
			const empty   = qs("[data-inv-empty]", root);
			const inputs  = qsa("[data-inv-filter]", root);

			function apply() {
				let visible = 0;
				const f = {};
				inputs.forEach(i => {
					const k = i.getAttribute("data-inv-filter");
					const v = (i.value || "").toString().trim().toLowerCase();
					if (v && v !== "any") f[k] = v;
				});
				cards.forEach(card => {
					let show = true;
					Object.keys(f).forEach(k => {
						if (k === "max-price") {
							const p = parseFloat(card.dataset.price || 0);
							if (p > parseFloat(f[k])) show = false;
						} else if (k === "zip") {
							const z = (card.dataset.zip || "").toLowerCase();
							if (!z.startsWith(f[k].slice(0, 2))) show = false;
						} else {
							const val = (card.dataset[k] || "").toLowerCase();
							if (!val.includes(f[k])) show = false;
						}
					});
					card.style.display = show ? "" : "none";
					if (show) visible++;
				});
				if (count) count.textContent = visible + (visible === 1 ? " result" : " results");
				if (empty) empty.style.display = visible ? "none" : "";
			}
			inputs.forEach(i => i.addEventListener("input", apply));
			inputs.forEach(i => i.addEventListener("change", apply));
			apply();
		});
	}

	/* --------------------------------------------------------------------
	 * Test Drive multi-step booking.
	 * -------------------------------------------------------------------- */
	function bindTestDrive() {
		qsa("[data-vc-td]").forEach(root => {
			const steps  = qsa(".vc-td__step", root);
			const panels = qsa(".vc-td__panel", root);
			const next   = qs("[data-td-next]", root);
			const prev   = qs("[data-td-prev]", root);
			const submit = qs("[data-td-submit]", root);
			const form   = qs("form", root);
			let i = 0;

			function go(idx) {
				i = Math.max(0, Math.min(panels.length - 1, idx));
				steps.forEach((s, k) => {
					s.classList.toggle("is-active", k === i);
					s.classList.toggle("is-done", k < i);
				});
				panels.forEach((p, k) => p.classList.toggle("is-active", k === i));
				if (prev) prev.style.visibility = i === 0 ? "hidden" : "";
				if (next) next.style.display = i >= panels.length - 2 ? "none" : "";
				if (submit) submit.style.display = i === panels.length - 2 ? "" : "none";
			}

			qsa(".vc-td__choice", root).forEach(ch => {
				ch.addEventListener("click", () => {
					const group = ch.parentNode;
					qsa(".vc-td__choice", group).forEach(x => x.classList.remove("is-active"));
					ch.classList.add("is-active");
					const input = qs(ch.dataset.target ? "[name='" + ch.dataset.target + "']" : "input[type=hidden]", root);
					if (input && ch.dataset.value) input.value = ch.dataset.value;
				});
			});

			next && next.addEventListener("click", () => go(i + 1));
			prev && prev.addEventListener("click", () => go(i - 1));
			form && form.addEventListener("submit", e => {
				e.preventDefault();
				go(panels.length - 1);
			});
			go(0);
		});
	}

	/* --------------------------------------------------------------------
	 * Generic modal: any button with data-vc-modal-open="id" toggles
	 * the corresponding [data-vc-modal=id]. Closes on backdrop click,
	 * close button, or Esc.
	 * -------------------------------------------------------------------- */
	function bindModals() {
		const openers = qsa("[data-vc-modal-open]");
		const modals = qsa("[data-vc-modal]");
		if (!openers.length && !modals.length) return;

		function open(id) {
			const m = qs('[data-vc-modal="' + id + '"]');
			if (!m) return;
			m.classList.add("is-open");
			m.setAttribute("aria-hidden", "false");
			document.body.style.overflow = "hidden";
			const focusable = qs("input, select, button, a", m);
			focusable && focusable.focus();
		}
		function close(m) {
			m.classList.remove("is-open");
			m.setAttribute("aria-hidden", "true");
			document.body.style.overflow = "";
		}
		openers.forEach(b => {
			b.addEventListener("click", e => {
				e.preventDefault();
				open(b.getAttribute("data-vc-modal-open"));
			});
		});
		modals.forEach(m => {
			m.setAttribute("aria-hidden", "true");
			m.addEventListener("click", e => { if (e.target === m) close(m); });
			qsa("[data-vc-modal-close]", m).forEach(b => b.addEventListener("click", () => close(m)));
		});
		document.addEventListener("keydown", e => {
			if (e.key === "Escape") qsa(".vc-modal.is-open").forEach(close);
		});
	}
})();
