import fs from 'node:fs';

const [primary, accent] = process.argv.slice(2);
if (![primary, accent].every(value => /^#[0-9a-f]{6}$/i.test(value ?? ''))) throw new Error('Los colores deben tener el formato #RRGGBB.');
const cssPath = 'resources/css/app.css';
const css = fs.readFileSync(cssPath, 'utf8');
function hsl(hex) {
  const [r, g, b] = [1, 3, 5].map(i => parseInt(hex.slice(i, i + 2), 16) / 255);
  const max = Math.max(r, g, b), min = Math.min(r, g, b), d = max - min, l = (max + min) / 2;
  let h = 0;
  if (d) { if (max === r) h = ((g - b) / d + (g < b ? 6 : 0)) / 6; else if (max === g) h = ((b - r) / d + 2) / 6; else h = ((r - g) / d + 4) / 6; }
  return [h, d ? d / (1 - Math.abs(2 * l - 1)) : 0, l];
}
function hex([h, s, l]) {
  const f = n => { const k = (n + h * 12) % 12; return l - s * Math.min(l, 1 - l) * Math.max(-1, Math.min(k - 3, 9 - k, 1)); };
  return '#' + [f(0), f(8), f(4)].map(v => Math.round(Math.max(0, Math.min(1, v)) * 255).toString(16).padStart(2, '0')).join('');
}
const dark = hsl(primary), bright = hsl(accent), originalDark = hsl('#1e4037'), originalBright = hsl('#a8d563');
const themed = css.replace(/#[0-9a-f]{8}\b|#[0-9a-f]{6}\b/gi, value => {
  const [h, s, l] = hsl(value);
  // Solo se transforman los verdes existentes; los avisos rojos y naranjas se mantienen.
  if (h < 0.19 || h > 0.52 || s < 0.06) return value;
  const isAccent = l > 0.55 && s > 0.35;
  const target = isAccent ? bright : dark, base = isAccent ? originalBright : originalDark;
  const mapped = hex([target[0], Math.max(0, Math.min(1, s * (target[1] / base[1]))), Math.max(0, Math.min(1, l + target[2] - base[2]))]);
  return mapped + value.slice(7);
});
fs.writeFileSync(cssPath, themed);
