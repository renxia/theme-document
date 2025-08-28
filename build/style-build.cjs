const fs = require('node:fs');
const path = require('node:path');
const sass = require('sass');
const baseDir = path.resolve(__dirname, '..');
const scssFilename = path.resolve(baseDir, 'style.scss');
const result = sass.compile(scssFilename, {  });
const outputFilepath = path.resolve(baseDir, 'style.css');
fs.writeFileSync(outputFilepath, result.css);
console.log('style.css created.', outputFilepath);
