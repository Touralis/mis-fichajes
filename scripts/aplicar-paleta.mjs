import fs from 'node:fs';

const [primary, accent] = process.argv.slice(2);
if (![primary, accent].every(value => /^#[0-9a-f]{6}$/i.test(value ?? ''))) throw new Error('Los colores deben tener el formato #RRGGBB.');
const cssPath = 'resources/css/app.css';
const css = fs.readFileSync(cssPath, 'utf8');
function rgb(value) { return [1, 3, 5].map(i => parseInt(value.slice(i, i + 2), 16)); }
function luminance(value) {
  return rgb(value).map(c => c / 255).map(c => c <= 0.04045 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4)
    .reduce((sum, c, i) => sum + c * [0.2126, 0.7152, 0.0722][i], 0);
}
function contrast(a, b) { const [x, y] = [luminance(a), luminance(b)].sort((a, b) => b - a); return (x + 0.05) / (y + 0.05); }
function mix(a, b, amount) {
  const left = rgb(a), right = rgb(b);
  return '#' + left.map((v, i) => Math.round(v * (1 - amount) + right[i] * amount).toString(16).padStart(2, '0')).join('');
}
function readableOnWhite(color, minimum = 4.5) {
  let result = color;
  for (let i = 0; i < 100 && contrast(result, '#ffffff') < minimum; i++) result = mix(result, '#000000', 0.05);
  return result;
}
function foreground(background) { return contrast(background, '#ffffff') >= contrast(background, '#111111') ? '#ffffff' : '#111111'; }
function hsl(hex) {
  const [r, g, b] = rgb(hex).map(v => v / 255);
  const max = Math.max(r, g, b), min = Math.min(r, g, b), d = max - min, l = (max + min) / 2;
  let h = 0;
  if (d) { if (max === r) h = ((g - b) / d + (g < b ? 6 : 0)) / 6; else if (max === g) h = ((b - r) / d + 2) / 6; else h = ((r - g) / d + 4) / 6; }
  return [h, d ? d / (1 - Math.abs(2 * l - 1)) : 0, l];
}
// Los colores de superficie y texto cumplen funciones distintas: no desplazar su
// luminosidad según el color de marca evita convertir grises en blanco ilegible.
const themed = css.replace(/([\w-]+)\s*:\s*([^;{}]+)/g, (declaration, property, value) => {
  const changed = value.replace(/#[0-9a-f]{8}\b|#[0-9a-f]{6}\b/gi, color => {
    const base = color.slice(0, 7), [h, s, l] = hsl(base);
    if (h < 0.19 || h > 0.52 || s < 0.06) return color;
    let result;
    if (property === 'color') {
      result = readableOnWhite(mix('#333333', primary, Math.min(0.65, s)));
    } else if (property.includes('border') || property === 'outline') {
      result = mix('#ffffff', primary, l > 0.7 ? 0.18 : 0.5);
    } else if (property.includes('background')) {
      if (base.toLowerCase() === '#1e4037' || base.toLowerCase() === '#21473a') result = primary;
      else if (l >= 0.8) result = mix('#ffffff', primary, Math.max(0.03, Math.min(0.15, (1 - l) * 0.6)));
      else result = mix('#ffffff', primary, Math.max(0.25, 1 - l));
    } else {
      result = readableOnWhite(primary);
    }
    return result + color.slice(7);
  });
  return declaration.replace(value, changed);
});
const primaryText = foreground(primary), accentText = foreground(accent);
const primaryMuted = mix(primaryText, primary, 0.18);
const primaryBorder = mix(primaryText, primary, 0.45);
const accentHover = mix(accent, accentText, 0.1);
// Los componentes de marca tienen su propio contexto de contraste.
const overrides = `\n/* Paleta personalizada: contraste independiente para superficies de marca. */
.hero-card,.login-aside{background:${primary};color:${primaryText}}
.hero-card h2,.hero-time strong,.login-aside .brand,.login-copy h1,.art-clock{color:${primaryText}}
.hero-card p,.hero-time small,.hero-card .eyebrow,.login-aside .eyebrow,.login-copy p,.aside-footer,.art-clock small,.art-label{color:${primaryMuted}}
.hero-time,.aside-art{border-color:${primaryBorder}}
.status{color:${primaryText};background:${primary};border-color:${primaryBorder}}
.accent,.brand-dot{color:${readableOnWhite(accent)}}
.online,.login-copy em,.art-clock span{color:${accent}}
.online{background:${accent}}
.clock-button:not(.clock-out),.primary{background:${accent};color:${accentText}}
.clock-button:not(.clock-out):hover{background:${accentHover};color:${accentText}}
.wide{background:${primary};color:${primaryText}}
.login-aside .accent,.login-aside .brand-dot,.login-copy em,.art-clock span{color:${contrast(accent, primary) >= 4.5 ? accent : primaryText}}
`;
fs.writeFileSync(cssPath, themed + overrides);
