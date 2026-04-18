#!/usr/bin/env node
/**
 * scripts/img-to-webp.js
 * ---------------------------------------------------------------------------
 * Batch image pipeline. Walks public/assets/images/ and produces, next to
 * every source JPG/PNG, a sibling .webp (and optionally .avif) plus a set
 * of responsive-width variants (e.g. image.jpg → image-640.webp,
 * image-1024.webp, image-1600.webp).
 *
 * The output works cleanly with a <picture> element:
 *
 *     <picture>
 *       <source type="image/avif" srcset="image-640.avif 640w, image-1024.avif 1024w, image-1600.avif 1600w" sizes="(min-width: 992px) 50vw, 100vw">
 *       <source type="image/webp" srcset="image-640.webp 640w, image-1024.webp 1024w, image-1600.webp 1600w" sizes="(min-width: 992px) 50vw, 100vw">
 *       <img src="image.jpg" alt="…" loading="lazy" decoding="async" width="1600" height="900">
 *     </picture>
 *
 * Requires `sharp` (installed lazily so it's not a hard dep for dev):
 *     npm i --save-dev sharp
 *
 * Run:
 *     node scripts/img-to-webp.js                  # default: all widths, WebP + AVIF
 *     node scripts/img-to-webp.js --avif=false     # skip AVIF
 *     node scripts/img-to-webp.js --widths=800,1200
 *     node scripts/img-to-webp.js --dir=public/assets/images/home
 *     node scripts/img-to-webp.js --force          # regenerate even if sibling exists
 */

'use strict';

const fs = require('fs');
const path = require('path');

const ROOT = path.resolve(__dirname, '..');
const DEFAULT_DIR = path.join(ROOT, 'public', 'assets', 'images');

/* ─── CLI args ─────────────────────────────────────────────────────────── */

const args = parseArgs(process.argv.slice(2));
const WIDTHS = (args.widths || '640,1024,1600')
  .split(',').map((n) => parseInt(n, 10)).filter((n) => n > 0).sort((a, b) => a - b);
const DO_AVIF = args.avif !== 'false';
const FORCE = Boolean(args.force);
const ROOT_DIR = args.dir ? path.resolve(args.dir) : DEFAULT_DIR;

const QUALITY = {
  webp: Number(args['q-webp'] || 82),
  avif: Number(args['q-avif'] || 55),
  jpg:  Number(args['q-jpg']  || 82),
};

const EXTENSIONS = new Set(['.jpg', '.jpeg', '.png']);


/* ─── Lazy sharp loader (helpful error if missing) ────────────────────── */

let sharp;
try {
  sharp = require('sharp');
} catch (err) {
  console.error(
    'This script requires the `sharp` package.\n' +
    '  npm i --save-dev sharp\n'
  );
  process.exit(1);
}


/* ─── Walk & process ──────────────────────────────────────────────────── */

function walk(dir, acc = []) {
  if (!fs.existsSync(dir)) return acc;
  for (const e of fs.readdirSync(dir, { withFileTypes: true })) {
    const full = path.join(dir, e.name);
    if (e.isDirectory()) walk(full, acc);
    else if (e.isFile() && EXTENSIONS.has(path.extname(e.name).toLowerCase())) acc.push(full);
  }
  return acc;
}

async function process_one(file) {
  const ext = path.extname(file).toLowerCase();
  const base = file.slice(0, -ext.length);
  const rel = path.relative(ROOT, file);

  const meta = await sharp(file).metadata();
  const srcWidth = meta.width || 0;

  const tasks = [];

  // One full-size WebP (and AVIF) beside the original
  tasks.push(emit(file, `${base}.webp`, 'webp', null));
  if (DO_AVIF) tasks.push(emit(file, `${base}.avif`, 'avif', null));

  // Responsive variants — skip any wider than the source (don't upscale)
  for (const w of WIDTHS) {
    if (w >= srcWidth) continue;
    tasks.push(emit(file, `${base}-${w}.webp`, 'webp', w));
    if (DO_AVIF) tasks.push(emit(file, `${base}-${w}.avif`, 'avif', w));
  }

  const done = await Promise.allSettled(tasks);
  const ok = done.filter((r) => r.status === 'fulfilled' && r.value).length;
  const skipped = done.filter((r) => r.status === 'fulfilled' && !r.value).length;
  const failed = done.filter((r) => r.status === 'rejected');

  console.log(
    `[${rel}]  ${srcWidth}px  →  +${ok} created, ${skipped} skipped` +
    (failed.length ? `, ${failed.length} failed` : '')
  );
  for (const f of failed) console.error('   !', f.reason?.message || f.reason);
}

/**
 * Encode `src` into `out` at the given format and optional width.
 * Returns true if a new file was written, false if we skipped because the
 * sibling already exists (and FORCE is off).
 */
async function emit(src, out, format, width) {
  if (!FORCE && fs.existsSync(out)) return false;

  let pipeline = sharp(src, { sequentialRead: true });
  if (width) pipeline = pipeline.resize({ width, withoutEnlargement: true });

  if (format === 'webp') {
    pipeline = pipeline.webp({ quality: QUALITY.webp, effort: 4 });
  } else if (format === 'avif') {
    pipeline = pipeline.avif({ quality: QUALITY.avif, effort: 4 });
  } else if (format === 'jpg' || format === 'jpeg') {
    pipeline = pipeline.jpeg({ quality: QUALITY.jpg, mozjpeg: true });
  }

  fs.mkdirSync(path.dirname(out), { recursive: true });
  await pipeline.toFile(out);
  return true;
}


/* ─── Helpers ─────────────────────────────────────────────────────────── */

function parseArgs(argv) {
  const out = {};
  for (const a of argv) {
    if (a.startsWith('--')) {
      const [k, v = 'true'] = a.slice(2).split('=');
      out[k] = v;
    }
  }
  return out;
}


/* ─── Main ────────────────────────────────────────────────────────────── */

(async () => {
  if (!fs.existsSync(ROOT_DIR)) {
    console.error(`Directory not found: ${ROOT_DIR}`);
    process.exit(2);
  }

  const files = walk(ROOT_DIR);
  if (files.length === 0) {
    console.log('[img] no JPG/PNG sources found — nothing to do.');
    return;
  }

  console.log(`[img] ${files.length} source image(s) in ${path.relative(ROOT, ROOT_DIR)}`);
  console.log(`[img] widths: ${WIDTHS.join(', ')} · formats: webp${DO_AVIF ? ' + avif' : ''}`);
  console.log(`[img] force: ${FORCE}`);

  const started = Date.now();

  // Limit concurrency to avoid spawning too many threads on small boxes.
  const CONCURRENCY = 4;
  let cursor = 0;
  async function worker() {
    while (cursor < files.length) {
      const mine = files[cursor++];
      try { await process_one(mine); }
      catch (err) { console.error(`[img] failed on ${mine}:`, err); }
    }
  }
  await Promise.all(Array.from({ length: CONCURRENCY }, worker));

  console.log(`[img] done in ${((Date.now() - started) / 1000).toFixed(1)}s`);
})();
