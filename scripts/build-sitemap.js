#!/usr/bin/env node
/**
 * scripts/build-sitemap.js
 * ---------------------------------------------------------------------------
 * Generates public/sitemap.xml by scanning every .html file under public/en/
 * (and public/zh/ once the Chinese translations land) and emitting a
 * Sitemap 0.9 XML document with:
 *
 *   - <loc>              canonical URL on the configured origin
 *   - <lastmod>          ISO date from git log (falls back to file mtime)
 *   - <changefreq>       heuristic based on path
 *   - <priority>         heuristic based on path depth
 *   - <xhtml:link hreflang="…">  alt-language siblings (en ↔ zh)
 *
 * Run:
 *   node scripts/build-sitemap.js                 # default origin
 *   SITE_ORIGIN=https://www.07691688.xyz node scripts/build-sitemap.js
 *
 * No npm dependencies — uses only Node's standard library.
 */

'use strict';

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const ROOT = path.resolve(__dirname, '..');
const PUBLIC_DIR = path.join(ROOT, 'public');
const OUT = path.join(PUBLIC_DIR, 'sitemap.xml');

const ORIGIN = (process.env.SITE_ORIGIN || 'https://www.07691688.xyz').replace(/\/+$/, '');
const LOCALES = ['en', 'zh'];        // URL prefixes
const DEFAULT_LOCALE = 'en';

// URLs never included in the sitemap even if a matching file exists.
const EXCLUDES = new Set([
  '/_template.html',
  '/404.html',
  '/50x.html',
]);


/* ─── Walk public/{en,zh}/ and collect pages ─────────────────────────── */

function walk(dir, acc = []) {
  if (!fs.existsSync(dir)) return acc;
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      walk(full, acc);
    } else if (entry.isFile() && entry.name.endsWith('.html')) {
      acc.push(full);
    }
  }
  return acc;
}

function pathnameOf(file) {
  const rel = '/' + path.relative(PUBLIC_DIR, file).split(path.sep).join('/');
  // "/en/products/index.html"  →  "/en/products/"
  return rel.replace(/\/index\.html$/, '/');
}

function localeOf(pathname) {
  const m = pathname.match(/^\/(\w+)\//);
  return m && LOCALES.includes(m[1]) ? m[1] : null;
}

function urlOf(pathname) {
  return ORIGIN + pathname;
}


/* ─── lastmod: prefer git commit date, fall back to file mtime ────────── */

let gitOK = true;
function gitLastMod(file) {
  if (!gitOK) return null;
  try {
    const out = execSync(
      `git log -1 --pretty=format:%cI -- "${path.relative(ROOT, file)}"`,
      { cwd: ROOT, stdio: ['ignore', 'pipe', 'ignore'] }
    );
    const s = String(out).trim();
    return s || null;
  } catch (_) {
    gitOK = false;
    return null;
  }
}

function lastModOf(file) {
  const g = gitLastMod(file);
  if (g) return g.slice(0, 10);                  // YYYY-MM-DD is enough
  try { return fs.statSync(file).mtime.toISOString().slice(0, 10); }
  catch (_) { return new Date().toISOString().slice(0, 10); }
}


/* ─── Heuristics for changefreq + priority ────────────────────────────── */

function heuristicsFor(pathname) {
  // Homepage
  if (/^\/[a-z]{2}\/$/.test(pathname)) {
    return { changefreq: 'weekly',  priority: '1.0' };
  }
  // Blog & case indices and their entries
  if (/\/(blog|cases)\/$/.test(pathname)) {
    return { changefreq: 'weekly',  priority: '0.8' };
  }
  if (/\/(blog|cases)\//.test(pathname)) {
    return { changefreq: 'monthly', priority: '0.6' };
  }
  // Product index + applications index
  if (/\/(products|applications)\/$/.test(pathname)) {
    return { changefreq: 'monthly', priority: '0.9' };
  }
  // Product + application detail pages
  if (/\/(products|applications)\//.test(pathname)) {
    return { changefreq: 'monthly', priority: '0.8' };
  }
  // Top-level pages (contact, about, careers, privacy)
  if (/^\/[a-z]{2}\/[a-z-]+\.html$/.test(pathname)) {
    if (/\/privacy\.html$/.test(pathname)) {
      return { changefreq: 'yearly', priority: '0.3' };
    }
    return { changefreq: 'monthly', priority: '0.7' };
  }
  return { changefreq: 'monthly', priority: '0.5' };
}


/* ─── hreflang siblings: pair /en/foo and /zh/foo ─────────────────────── */

function groupByCanonical(pages) {
  // Canonical key = path with locale stripped (so en/about.html == zh/about.html)
  const groups = new Map();
  for (const p of pages) {
    const locale = localeOf(p.pathname);
    if (!locale) continue;
    const key = p.pathname.replace(/^\/\w+/, '');   // "/about.html", "/products/", ...
    if (!groups.has(key)) groups.set(key, []);
    groups.get(key).push(p);
  }
  return groups;
}


/* ─── Render ───────────────────────────────────────────────────────────── */

function render(pages) {
  const groups = groupByCanonical(pages);
  const lines = [
    '<?xml version="1.0" encoding="UTF-8"?>',
    '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"',
    '        xmlns:xhtml="http://www.w3.org/1999/xhtml">',
  ];

  // Sort for deterministic output (stable diffs in git)
  const sorted = [...pages].sort((a, b) => a.pathname.localeCompare(b.pathname));

  for (const p of sorted) {
    const { changefreq, priority } = heuristicsFor(p.pathname);
    lines.push('  <url>');
    lines.push(`    <loc>${escapeXml(urlOf(p.pathname))}</loc>`);
    lines.push(`    <lastmod>${p.lastmod}</lastmod>`);
    lines.push(`    <changefreq>${changefreq}</changefreq>`);
    lines.push(`    <priority>${priority}</priority>`);

    // Emit xhtml:link siblings for every locale present in the same group
    const key = p.pathname.replace(/^\/\w+/, '');
    const siblings = groups.get(key) || [];
    if (siblings.length > 1) {
      for (const s of siblings) {
        const loc = localeOf(s.pathname);
        lines.push(
          `    <xhtml:link rel="alternate" hreflang="${loc}" href="${escapeXml(urlOf(s.pathname))}"/>`
        );
      }
      // x-default points at the DEFAULT_LOCALE version
      const def = siblings.find((s) => localeOf(s.pathname) === DEFAULT_LOCALE) || siblings[0];
      lines.push(
        `    <xhtml:link rel="alternate" hreflang="x-default" href="${escapeXml(urlOf(def.pathname))}"/>`
      );
    }

    lines.push('  </url>');
  }

  lines.push('</urlset>', '');
  return lines.join('\n');
}

function escapeXml(s) {
  return String(s)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}


/* ─── Main ─────────────────────────────────────────────────────────────── */

function main() {
  const files = [];
  for (const loc of LOCALES) {
    files.push(...walk(path.join(PUBLIC_DIR, loc)));
  }

  const pages = files
    .map((f) => ({ file: f, pathname: pathnameOf(f) }))
    .filter((p) => !EXCLUDES.has(p.pathname))
    .map((p) => ({ ...p, lastmod: lastModOf(p.file) }));

  const xml = render(pages);
  fs.writeFileSync(OUT, xml, 'utf8');
  console.log(`[sitemap] wrote ${pages.length} URLs → ${path.relative(ROOT, OUT)}`);
}

main();
