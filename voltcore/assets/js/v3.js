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
		bindStickyCta();
		bindCookieBanner();
		bindConfigurator();
		bindFinancing();
		bindInventory();
		bindTestDrive();
		bindLocator();
		bindEnergyCalc();
		bindProduct360();
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

	/* ================================================================
	 * 4. Sticky CTA bar — reveal after scroll threshold, dismissible
	 * and remembered via sessionStorage.
	 * ================================================================ */
	function bindStickyCta() {
		qsa("[data-vc-sticky-cta]").forEach(bar => {
			const key = "vc_sticky_dismissed_" + (bar.id || "default");
			if (sessionStorage.getItem(key) === "1") { bar.remove(); return; }

			bar.hidden = false;
			const threshold = parseInt(bar.getAttribute("data-threshold") || "600", 10);

			function update() {
				if (window.scrollY > threshold) bar.classList.add("is-visible");
				else bar.classList.remove("is-visible");
			}
			update();
			window.addEventListener("scroll", update, { passive: true });

			const close = qs("[data-vc-sticky-cta-close]", bar);
			if (close) close.addEventListener("click", () => {
				bar.classList.remove("is-visible");
				sessionStorage.setItem(key, "1");
				setTimeout(() => bar.remove(), 400);
			});
		});
	}

	/* ================================================================
	 * 5. Cookie Banner — show until accept/decline, persist choice in
	 * localStorage.
	 * ================================================================ */
	function bindCookieBanner() {
		const banner = qs("[data-vc-cookie]");
		if (!banner) return;
		const KEY = "vc_cookie_consent";
		if (localStorage.getItem(KEY)) return; // already decided

		banner.hidden = false;
		requestAnimationFrame(() => banner.classList.add("is-visible"));

		qsa("[data-vc-cookie-decide]", banner).forEach(btn => {
			btn.addEventListener("click", () => {
				localStorage.setItem(KEY, btn.getAttribute("data-vc-cookie-decide") || "decline");
				banner.classList.remove("is-visible");
				setTimeout(() => banner.remove(), 400);
				// Fire a custom event so analytics code can react
				document.dispatchEvent(new CustomEvent("vc-cookie-decision", { detail: { choice: btn.getAttribute("data-vc-cookie-decide") } }));
			});
		});
	}

	/* ================================================================
	 * 6. Configurator — option-picker + live price.
	 * State keys: trim, paint, wheel, interior. Each button has
	 * data-delta (price adjustment). Add-ons are checkboxes.
	 * URL query params mirror the current config so it's shareable.
	 * ================================================================ */
	function bindConfigurator() {
		qsa("[data-vc-cfg]").forEach(root => {
			const base = parseInt(root.getAttribute("data-base") || "0", 10);
			const currency = root.getAttribute("data-currency") || "$";
			const priceEl = qs("[data-vc-cfg-price]", root);
			const rangeEl = qs("[data-vc-cfg-range]", root);
			const zeroEl  = qs("[data-vc-cfg-zero]", root);
			const topEl   = qs("[data-vc-cfg-top]", root);
			const orderBtn = qs("[data-vc-cfg-order]", root);

			const state = { trim: null, paint: null, wheel: null, interior: null, addons: [] };
			let baseRange = 0, rangeAdjust = 0;

			function fmt(n) {
				const rounded = Math.round(n);
				return currency + rounded.toLocaleString();
			}
			function recompute() {
				let total = base;
				qsa("[data-vc-cfg-option].is-active", root).forEach(el => {
					total += parseInt(el.getAttribute("data-delta") || "0", 10);
				});
				state.addons = [];
				qsa("input[data-vc-cfg-option='addon']:checked", root).forEach(el => {
					total += parseInt(el.getAttribute("data-delta") || "0", 10);
					state.addons.push(el.getAttribute("data-label"));
				});
				if (priceEl) priceEl.textContent = fmt(total);
				if (rangeEl) rangeEl.textContent = baseRange ? (baseRange + rangeAdjust) : "—";
				if (orderBtn) {
					const params = new URLSearchParams();
					["trim", "paint", "wheel", "interior"].forEach(k => { if (state[k]) params.set(k, state[k]); });
					if (state.addons.length) params.set("addons", state.addons.join(","));
					params.set("price", total);
					const href = orderBtn.getAttribute("href") || "#";
					const url = new URL(href, window.location.origin);
					url.search = params.toString();
					orderBtn.setAttribute("href", url.pathname + url.search);
				}
			}

			// Button-group options (trim / paint / wheel / interior)
			qsa("[data-vc-cfg-option]", root).forEach(btn => {
				if (btn.tagName !== "BUTTON") return;
				const group = btn.getAttribute("data-vc-cfg-option");
				btn.addEventListener("click", () => {
					qsa(`[data-vc-cfg-option='${group}']`, root).forEach(b => b.classList.remove("is-active"));
					btn.classList.add("is-active");
					state[group] = btn.getAttribute("data-label");
					const lbl = qs(`[data-vc-cfg-label='${group}']`, root);
					if (lbl) lbl.textContent = state[group];
					if (group === "trim") {
						baseRange = parseInt(btn.getAttribute("data-range") || "0", 10);
						if (zeroEl) zeroEl.textContent = btn.getAttribute("data-zero") || "—";
						if (topEl)  topEl.textContent  = btn.getAttribute("data-top") || "—";
					}
					if (group === "wheel") {
						rangeAdjust = parseInt(btn.getAttribute("data-range-adj") || "0", 10);
					}
					recompute();
				});
			});

			// Add-on checkboxes
			qsa("input[data-vc-cfg-option='addon']", root).forEach(cb => {
				cb.addEventListener("change", recompute);
			});

			// Initialise from active defaults
			qsa(".is-active[data-vc-cfg-option]", root).forEach(el => {
				const g = el.getAttribute("data-vc-cfg-option");
				state[g] = el.getAttribute("data-label");
				const lbl = qs(`[data-vc-cfg-label='${g}']`, root);
				if (lbl) lbl.textContent = state[g];
				if (g === "trim") {
					baseRange = parseInt(el.getAttribute("data-range") || "0", 10);
					if (zeroEl) zeroEl.textContent = el.getAttribute("data-zero") || "—";
					if (topEl)  topEl.textContent  = el.getAttribute("data-top") || "—";
				}
			});
			recompute();
		});
	}

	/* ================================================================
	 * 7. Financing calculator — standard amortisation formula.
	 * Monthly = P * r / (1 - (1+r)^-n), where r = apr/12/100, P = financed
	 * amount, n = term in months.
	 * ================================================================ */
	function bindFinancing() {
		qsa("[data-vc-finance]").forEach(root => {
			// noop — each .vc-finance root binds its own inputs below
		});

		qsa(".vc-finance").forEach(root => {
			const currency = root.getAttribute("data-currency") || "$";
			const inputs = {
				price: qs("input[data-vc-finance='price']", root),
				down:  qs("input[data-vc-finance='down']",  root),
				term:  qs("input[data-vc-finance='term']",  root),
				apr:   qs("input[data-vc-finance='apr']",   root),
			};
			if (!inputs.price) return;

			const out = {
				price:    qs("[data-vc-finance-out='price']",    root),
				down:     qs("[data-vc-finance-out='down']",     root),
				term:     qs("[data-vc-finance-out='term']",     root),
				apr:      qs("[data-vc-finance-out='apr']",      root),
				monthly:  qs("[data-vc-finance-out='monthly']",  root),
				financed: qs("[data-vc-finance-out='financed']", root),
				interest: qs("[data-vc-finance-out='interest']", root),
				total:    qs("[data-vc-finance-out='total']",    root),
			};

			function fmt(n) { return currency + Math.max(0, Math.round(n)).toLocaleString(); }

			function recompute() {
				const price = +inputs.price.value;
				const down  = Math.min(+inputs.down.value, price);
				const term  = +inputs.term.value;
				const apr   = +inputs.apr.value;

				const financed = Math.max(0, price - down);
				const r = apr / 100 / 12;
				let monthly;
				if (r === 0) monthly = financed / term;
				else monthly = financed * r / (1 - Math.pow(1 + r, -term));
				const total = monthly * term + down;
				const interest = total - price;

				if (out.price)    out.price.textContent    = fmt(price);
				if (out.down)     out.down.textContent     = fmt(down);
				if (out.term)     out.term.textContent     = term + " mo";
				if (out.apr)      out.apr.textContent      = apr.toFixed(1).replace(/\.0$/, "") + "%";
				if (out.monthly)  out.monthly.textContent  = fmt(monthly);
				if (out.financed) out.financed.textContent = fmt(financed);
				if (out.interest) out.interest.textContent = fmt(interest);
				if (out.total)    out.total.textContent    = fmt(total);
			}

			Object.values(inputs).forEach(i => i && i.addEventListener("input", recompute));
			recompute();
		});
	}

	/* ================================================================
	 * 8. Inventory — chip filters (trim, color), price slider, sort.
	 * Purely DOM-based filtering / sorting on the existing cards.
	 * ================================================================ */
	function bindInventory() {
		qsa("[data-vc-inventory]").forEach(root => {
			const currency = root.getAttribute("data-currency") || "$";
			const grid     = qs("[data-vc-inv-grid]", root);
			const none     = qs("[data-vc-inv-none]", root);
			const count    = qs("[data-vc-inv-count]", root);
			const priceInp = qs("[data-vc-inv-price]", root);
			const priceOut = qs("[data-vc-inv-price-out]", root);
			const sort     = qs("[data-vc-inv-sort]", root);
			if (!grid) return;

			const filters = { trim: "", color: "" };
			let priceMax  = priceInp ? +priceInp.value : Infinity;

			function apply() {
				const cards = qsa(".vc-inv__card", grid);
				let shown = 0;
				cards.forEach(card => {
					const t = card.getAttribute("data-trim")  || "";
					const c = card.getAttribute("data-color") || "";
					const p = +card.getAttribute("data-price") || 0;
					let ok = true;
					if (filters.trim  && t !== filters.trim)  ok = false;
					if (filters.color && c !== filters.color) ok = false;
					if (p > priceMax) ok = false;
					card.classList.toggle("is-hidden", !ok);
					if (ok) shown++;
				});
				if (count) count.textContent = shown;
				if (none) none.hidden = shown !== 0;
			}

			function sortCards(mode) {
				const cards = qsa(".vc-inv__card", grid);
				cards.sort((a, b) => {
					if (mode === "price-asc")  return (+a.dataset.price) - (+b.dataset.price);
					if (mode === "price-desc") return (+b.dataset.price) - (+a.dataset.price);
					if (mode === "range-desc") return (+b.dataset.range) - (+a.dataset.range);
					return 0;
				});
				cards.forEach(c => grid.appendChild(c));
			}

			qsa("[data-vc-inv-chip]", root).forEach(chip => {
				chip.addEventListener("click", () => {
					const key = chip.getAttribute("data-vc-inv-chip");
					const val = chip.getAttribute("data-value") || "";
					filters[key] = val;
					qsa(`[data-vc-inv-chip='${key}']`, root).forEach(c => c.classList.toggle("is-active", c === chip));
					apply();
				});
			});

			if (priceInp) priceInp.addEventListener("input", () => {
				priceMax = +priceInp.value;
				if (priceOut) priceOut.textContent = currency + priceMax.toLocaleString();
				apply();
			});

			if (sort) sort.addEventListener("change", () => sortCards(sort.value));

			if (sort) sortCards(sort.value);
			apply();
		});
	}

	/* ================================================================
	 * 9. Test Drive — progressive 4-step form. Validates each step
	 * before letting the user move forward. Submits via fetch to
	 * admin-ajax and shows an inline confirmation on success.
	 * ================================================================ */
	function bindTestDrive() {
		qsa("[data-vc-test-drive]").forEach(root => {
			const panels = qsa("[data-vc-td-panel]", root);
			const steps  = qsa("[data-vc-td-step]",  root);
			const form   = qs("[data-vc-td-form]", root);
			const done   = qs("[data-vc-td-done]", root);
			const errEl  = qs("[data-vc-td-error]", root);
			if (!form || !panels.length) return;

			let current = 0;

			function show(i) {
				panels.forEach(p => p.classList.toggle("is-active", +p.getAttribute("data-vc-td-panel") === i));
				steps.forEach(s  => s.classList.toggle("is-active",  +s.getAttribute("data-vc-td-step")  === i));
				current = i;
			}
			function validate(panel) {
				const required = qsa("input[required], select[required], textarea[required]", panel);
				for (const f of required) {
					if (f.type === "radio") {
						const group = qsa(`input[name='${f.name}']`, panel);
						if (!group.some(r => r.checked)) { group[0].focus(); return false; }
					} else if (f.type === "checkbox") {
						if (!f.checked) { f.focus(); return false; }
					} else if (!f.value) {
						f.focus();
						return false;
					}
				}
				return true;
			}

			qsa("[data-vc-td-next]", root).forEach(btn => btn.addEventListener("click", () => {
				const panel = panels[current];
				if (!validate(panel)) return;
				if (current < panels.length - 1) show(current + 1);
			}));
			qsa("[data-vc-td-prev]", root).forEach(btn => btn.addEventListener("click", () => {
				if (current > 0) show(current - 1);
			}));

			form.addEventListener("submit", async e => {
				e.preventDefault();
				const panel = panels[current];
				if (!validate(panel)) return;
				const btn = qs("button[type='submit']", form);
				if (btn) { btn.disabled = true; btn.dataset.orig = btn.textContent; btn.textContent = "Sending…"; }
				if (errEl) errEl.hidden = true;

				try {
					const data = new FormData(form);
					const resp = await fetch(form.action, { method: "POST", body: data, credentials: "same-origin" });
					const json = await resp.json().catch(() => ({}));
					if (!resp.ok || !json.success) throw new Error((json && json.data && json.data.message) || "Submission failed.");
					form.hidden = true;
					if (done) done.hidden = false;
				} catch (err) {
					if (errEl) { errEl.textContent = err.message || "Something went wrong. Please try again."; errEl.hidden = false; }
					if (btn) { btn.disabled = false; btn.textContent = btn.dataset.orig || "Submit"; }
				}
			});
		});
	}

	/* ================================================================
	 * 10. Locator — lazy-load Leaflet from CDN on first view, then
	 * render markers and wire card clicks.
	 * ================================================================ */
	function bindLocator() {
		const roots = qsa("[data-vc-locator]");
		if (!roots.length) return;

		function loadLeaflet() {
			return new Promise((resolve, reject) => {
				if (window.L) return resolve(window.L);
				const css = document.createElement("link");
				css.rel = "stylesheet";
				css.href = "https://unpkg.com/leaflet@1.9.4/dist/leaflet.css";
				css.integrity = "sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=";
				css.crossOrigin = "";
				document.head.appendChild(css);
				const s = document.createElement("script");
				s.src = "https://unpkg.com/leaflet@1.9.4/dist/leaflet.js";
				s.integrity = "sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=";
				s.crossOrigin = "";
				s.onload = () => resolve(window.L);
				s.onerror = reject;
				document.head.appendChild(s);
			});
		}

		roots.forEach(root => {
			const mapEl = qs("[data-vc-loc-map]", root);
			const list  = qs("[data-vc-loc-list]", root);
			if (!mapEl || !list) return;

			let state;
			try { state = JSON.parse(mapEl.getAttribute("data-state") || "{}"); }
			catch (e) { state = { center: [0, 0], zoom: 2, places: [] }; }

			let map, markers = [];
			let initialised = false;
			let activeType = "";

			function initMap() {
				if (initialised) return;
				initialised = true;
				loadLeaflet().then(L => {
					map = L.map(mapEl, { scrollWheelZoom: false }).setView(state.center, state.zoom);
					L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
						attribution: "© OpenStreetMap",
						maxZoom: 18,
					}).addTo(map);
					state.places.forEach((p, i) => {
						const m = L.marker([p.lat, p.lng]).addTo(map);
						m.bindPopup(`<strong>${escapeHTML(p.name)}</strong><br>${escapeHTML(p.addr)}`);
						m.placeIndex = i;
						markers.push(m);
					});
					mapEl.setAttribute("data-ready", "true");
				}).catch(() => {
					const fb = qs(".vc-loc__map-fallback", mapEl);
					if (fb) fb.textContent = "Map unavailable.";
				});
			}

			// Lazy-load when the locator scrolls into view
			if ("IntersectionObserver" in window) {
				const io = new IntersectionObserver(entries => {
					entries.forEach(e => { if (e.isIntersecting) { initMap(); io.disconnect(); } });
				}, { rootMargin: "200px" });
				io.observe(root);
			} else {
				initMap();
			}

			// Card clicks
			qsa("[data-vc-loc-item]", list).forEach(btn => {
				btn.addEventListener("click", () => {
					const idx = +btn.getAttribute("data-vc-loc-item");
					qsa("[data-vc-loc-item]", list).forEach(b => b.classList.toggle("is-active", b === btn));
					if (map && markers[idx]) {
						const p = state.places[idx];
						map.setView([p.lat, p.lng], 13, { animate: !prefersReducedMotion });
						markers[idx].openPopup();
					}
				});
			});

			// Filter chips
			qsa("[data-vc-loc-chip]", root).forEach(chip => {
				chip.addEventListener("click", () => {
					activeType = chip.getAttribute("data-vc-loc-chip") || "";
					qsa("[data-vc-loc-chip]", root).forEach(c => c.classList.toggle("is-active", c === chip));
					qsa("[data-vc-loc-item]", list).forEach(b => {
						const t = b.getAttribute("data-type") || "";
						b.classList.toggle("is-filtered-out", activeType && t !== activeType);
					});
					if (map) {
						markers.forEach((m, i) => {
							const t = state.places[i].type || "";
							if (!activeType || t === activeType) m.addTo(map);
							else map.removeLayer(m);
						});
					}
				});
			});
		});
	}

	/* ================================================================
	 * 11. Energy Calculator — solar+battery ROI.
	 * kWh/mo ≈ bill / (rate¢/100). Generation ≈ roof (m²) * 0.18
	 * (efficiency) * sun hours * 30 days.
	 * ================================================================ */
	function bindEnergyCalc() {
		qsa("[data-vc-energy-calc]").forEach(root => {
			const currency = root.getAttribute("data-currency") || "$";
			const install  = +root.getAttribute("data-install") || 0;
			const co2      = +root.getAttribute("data-co2") || 0.42;

			const inputs = {
				bill: qs("input[data-vc-erg='bill']", root),
				rate: qs("input[data-vc-erg='rate']", root),
				roof: qs("input[data-vc-erg='roof']", root),
				sun:  qs("input[data-vc-erg='sun']",  root),
			};
			const out = {
				bill:    qs("[data-vc-erg-out='bill']",    root),
				rate:    qs("[data-vc-erg-out='rate']",    root),
				roof:    qs("[data-vc-erg-out='roof']",    root),
				sun:     qs("[data-vc-erg-out='sun']",     root),
				annual:  qs("[data-vc-erg-out='annual']",  root),
				payback: qs("[data-vc-erg-out='payback']", root),
				twenty:  qs("[data-vc-erg-out='twenty']",  root),
				co2:     qs("[data-vc-erg-out='co2']",     root),
			};
			if (!inputs.bill) return;

			function fmt(n) { return currency + Math.max(0, Math.round(n)).toLocaleString(); }

			function recompute() {
				const bill = +inputs.bill.value;
				const rate = +inputs.rate.value;   // ¢/kWh
				const roof = +inputs.roof.value;   // m²
				const sun  = +inputs.sun.value;    // hrs/day

				const monthlyKwh    = bill / (rate / 100);            // current usage
				const genPerMonth   = roof * 0.18 * sun * 30;         // panel output kWh/mo
				const offset        = Math.min(genPerMonth, monthlyKwh);
				const saveMonth     = offset * (rate / 100);
				const saveYear      = saveMonth * 12;
				const payback       = saveYear > 0 ? install / saveYear : 0;
				const twenty        = saveYear * 20 - install;
				const co2Yr         = offset * 12 * co2;              // kg/yr
				const co2Twenty     = co2Yr * 20 / 1000;              // tonnes

				if (out.bill)    out.bill.textContent    = fmt(bill);
				if (out.rate)    out.rate.textContent    = rate + "¢/kWh";
				if (out.roof)    out.roof.textContent    = roof + " m²";
				if (out.sun)     out.sun.textContent     = sun.toFixed(1);
				if (out.annual)  out.annual.textContent  = fmt(saveYear);
				if (out.payback) out.payback.textContent = payback > 0 ? payback.toFixed(1) + " yr" : "—";
				if (out.twenty)  out.twenty.textContent  = fmt(twenty);
				if (out.co2)     out.co2.textContent     = co2Twenty.toFixed(1) + " t";
			}

			Object.values(inputs).forEach(i => i && i.addEventListener("input", recompute));
			recompute();
		});
	}

	/* ================================================================
	 * 12. Product 360 — draggable image-sequence viewer.
	 * ================================================================ */
	function bindProduct360() {
		qsa("[data-vc-product-360]").forEach(root => {
			const stage = qs("[data-vc-360-stage]", root);
			const img   = qs(".vc-360__img", stage);
			const scrub = qs("[data-vc-360-scrub]", root);
			if (!stage || !img) return;

			let frames = [];
			try { frames = JSON.parse(stage.getAttribute("data-frames") || "[]"); } catch (e) {}
			if (frames.length < 2) return;

			// Preload
			const preloaded = frames.map(src => { const i = new Image(); i.src = src; return i; });

			let idx = 0;
			function setFrame(i) {
				idx = (i + frames.length) % frames.length;
				img.src = frames[idx];
				if (scrub) scrub.value = idx;
			}

			let dragging = false;
			let lastX = 0;
			let acc = 0;

			function start(e) {
				dragging = true;
				lastX = (e.touches ? e.touches[0].clientX : e.clientX);
				acc = 0;
				root.classList.add("is-interacted");
			}
			function move(e) {
				if (!dragging) return;
				const x = (e.touches ? e.touches[0].clientX : e.clientX);
				const dx = x - lastX;
				lastX = x;
				acc += dx;
				const stageW = stage.offsetWidth || 1;
				const frameStep = stageW / frames.length;
				while (acc >=  frameStep) { setFrame(idx + 1); acc -= frameStep; }
				while (acc <= -frameStep) { setFrame(idx - 1); acc += frameStep; }
			}
			function end() { dragging = false; }

			stage.addEventListener("mousedown", start);
			window.addEventListener("mousemove", move);
			window.addEventListener("mouseup", end);
			stage.addEventListener("touchstart", start, { passive: true });
			stage.addEventListener("touchmove",  move,  { passive: true });
			stage.addEventListener("touchend",   end);

			if (scrub) scrub.addEventListener("input", () => { setFrame(+scrub.value); root.classList.add("is-interacted"); });

			// Optional auto-spin once on reveal
			if (root.getAttribute("data-autoplay") === "yes" && "IntersectionObserver" in window && !prefersReducedMotion) {
				const io = new IntersectionObserver(entries => {
					entries.forEach(e => {
						if (!e.isIntersecting) return;
						io.disconnect();
						let step = 0;
						const interval = setInterval(() => {
							setFrame(idx + 1);
							step++;
							if (step >= frames.length) clearInterval(interval);
						}, 55);
					});
				}, { threshold: 0.4 });
				io.observe(root);
			}

			setFrame(0);
		});
	}

	/* ================================================================
	 * Small HTML-escape helper used by Leaflet popups.
	 * ================================================================ */
	function escapeHTML(str) {
		return String(str || "").replace(/[&<>"']/g, c => ({
			"&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;"
		}[c]));
	}

	// Expose a small helper for other modules that may want to lock scroll
	window.VoltCoreV3 = Object.assign(window.VoltCoreV3 || {}, {
		lockScroll, unlockScroll, prefersReducedMotion
	});
})();
