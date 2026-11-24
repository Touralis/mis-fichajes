import fs from 'node:fs';
const [path, url, primary, accent] = process.argv.slice(2);
const source = fs.readFileSync(path, 'utf8');
function replace(name, value) {
  const pattern = new RegExp(`(${name}:\\s*)["'][^"']*["']`);
  if (!pattern.test(sourceValues)) throw new Error(`No se encontró ${name} en ${path}`);
  sourceValues = sourceValues.replace(pattern, (_, prefix) => prefix + JSON.stringify(value));
}
let sourceValues = source;
replace('url', url);
if (primary) { replace('primary', primary); replace('accent', accent); }
fs.writeFileSync(path, sourceValues);
