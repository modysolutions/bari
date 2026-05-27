const { src, dest, series, parallel, watch } = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const jsmin = require('gulp-jsmin');
const sourcemaps = require('gulp-sourcemaps');
const rename = require('gulp-rename');

const sassConfig = {
    outputStyle: 'compressed',
};

function css(cb) {
    return src('./assets/css/src/**/*.scss')
    //return src('./assets/scss/*.scss')
        .pipe(sass.sync(sassConfig).on('error', sass.logError))
        .pipe(dest('./assets/css/dist'));
        //.pipe(dest('./assets/css/'));
}

function javascript(cb) {
    return src('./assets/js/src/*.js')
        .pipe(jsmin())
        .pipe(rename({suffix: '.min'}))
        .pipe(dest('./assets/js/dist'));
}

function clean(cb) {
    cb();
}

function watchCSS(cb) {
    return watch([ './assets/css/src/**/*.scss' ], { ignoreInitial: false }, css);
}

exports.default = series(clean, parallel(css, javascript));
exports.build = series(clean, parallel(css, javascript));
//exports.svg = parallel(svgSocial);
exports.watch   = watchCSS;
exports.js = javascript;
