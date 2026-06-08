import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const root = path.resolve(__dirname, '..');
const iconDir = path.join(root, 'public', 'images');
const publicDir = path.join(root, 'public');
const icoSizes = [16, 32, 64];

const images = icoSizes.map((size) => ({
    size,
    data: fs.readFileSync(path.join(iconDir, `wiboost-icon-${size}.png`)),
}));

let offset = 6 + images.length * 16;
const header = Buffer.alloc(6);
header.writeUInt16LE(0, 0);
header.writeUInt16LE(1, 2);
header.writeUInt16LE(images.length, 4);

const entries = [];
const payloads = [];

for (const image of images) {
    const entry = Buffer.alloc(16);

    entry.writeUInt8(image.size, 0);
    entry.writeUInt8(image.size, 1);
    entry.writeUInt8(0, 2);
    entry.writeUInt8(0, 3);
    entry.writeUInt16LE(1, 4);
    entry.writeUInt16LE(32, 6);
    entry.writeUInt32LE(image.data.length, 8);
    entry.writeUInt32LE(offset, 12);

    entries.push(entry);
    payloads.push(image.data);
    offset += image.data.length;
}

fs.writeFileSync(path.join(publicDir, 'favicon.ico'), Buffer.concat([header, ...entries, ...payloads]));

for (const size of [16, 32, 180, 192, 512]) {
    fs.copyFileSync(path.join(iconDir, `wiboost-icon-${size}.png`), path.join(publicDir, `wiboost-icon-${size}.png`));
}

fs.copyFileSync(path.join(iconDir, 'wiboost-icon-32.png'), path.join(publicDir, 'favicon-32x32.png'));
fs.copyFileSync(path.join(iconDir, 'wiboost-icon-16.png'), path.join(publicDir, 'favicon-16x16.png'));
fs.copyFileSync(path.join(iconDir, 'wiboost-icon-180.png'), path.join(publicDir, 'apple-touch-icon.png'));

console.log('Rebuilt round favicon.ico and public fallback icons.');
