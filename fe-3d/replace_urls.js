import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const targetDir = path.join(__dirname, 'src');

function walkDir(dir, callback) {
    fs.readdirSync(dir).forEach(f => {
        let dirPath = path.join(dir, f);
        let isDirectory = fs.statSync(dirPath).isDirectory();
        if (isDirectory) {
            walkDir(dirPath, callback);
        } else {
            callback(dirPath);
        }
    });
}

console.log('Starting URL replacement in Vue/JS files...');
let count = 0;

walkDir(targetDir, (filePath) => {
    if (filePath.endsWith('.vue') || filePath.endsWith('.js')) {
        let content = fs.readFileSync(filePath, 'utf8');
        if (content.includes('http://127.0.0.1:8000')) {
            console.log(`Replacing in: ${filePath}`);
            let updated = content.replaceAll('http://127.0.0.1:8000', '');
            fs.writeFileSync(filePath, updated, 'utf8');
            count++;
        }
    }
});

console.log(`Done! Replaced URLs in ${count} files.`);
