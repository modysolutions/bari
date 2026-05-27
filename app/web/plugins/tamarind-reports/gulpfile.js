const gulp = require('gulp');
const { exec } = require('child_process');

function runNpmBuild(cb) {
    exec('npm run build', (err, stdout, stderr) => {
        console.log(stdout);
        console.error(stderr);
        cb(err);
    });
}

exports.build = runNpmBuild;
exports.default = runNpmBuild;