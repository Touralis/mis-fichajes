import fs from 'node:fs';

const [path, identifier] = process.argv.slice(2);
if (!/^[A-Za-z][A-Za-z0-9]*(\.[A-Za-z][A-Za-z0-9]*)+$/.test(identifier ?? '')) {
  throw new Error('Identificador de aplicación inválido.');
}
const config = JSON.parse(fs.readFileSync(path, 'utf8'));
config.expo.ios.bundleIdentifier = identifier;
config.expo.android.package = identifier;
fs.writeFileSync(path, JSON.stringify(config, null, 4) + '\n');
