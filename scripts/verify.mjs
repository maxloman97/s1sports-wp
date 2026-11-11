import fs from 'node:fs';
import path from 'node:path';

const root = process.cwd();
const theme = path.join(root, 's1sports-theme');
const manifest = JSON.parse(fs.readFileSync(path.join(theme, 'snapshots', 'manifest.json'), 'utf8'));
const errors = [];
if (manifest.length !== 18) errors.push(`Expected 18 language pages; found ${manifest.length}`);

for (const page of manifest) {
  const snapshot = path.join(theme, 'snapshots', `${page.locale}-${page.slug}.html`);
  if (!fs.existsSync(snapshot)) errors.push(`Missing snapshot: ${snapshot}`);
  for (const css of page.css) {
    if (!fs.existsSync(path.join(theme, css))) errors.push(`Missing CSS: ${css}`);
  }
}

const cssDir = path.join(theme, 'assets', 'css');
for (const file of fs.readdirSync(cssDir).filter(name => name.endsWith('.css'))) {
  const css = fs.readFileSync(path.join(cssDir, file), 'utf8');
  for (const match of css.matchAll(/url\(([^)]+)\)/g)) {
    const url = match[1].replace(/^['"]|['"]$/g, '');
    if (/^(?:data:|https?:|#)/.test(url)) continue;
    if (!fs.existsSync(path.resolve(cssDir, url))) errors.push(`Missing CSS asset: ${file} -> ${url}`);
  }
}

if (errors.length) {
  console.error(errors.join('\n'));
  process.exitCode = 1;
} else {
  console.log(`Verified ${manifest.length} pages and all CSS assets.`);
}
