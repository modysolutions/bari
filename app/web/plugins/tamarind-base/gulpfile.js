const gulp = require('gulp');
const sass = require('gulp-dart-sass');
const concat = require('gulp-concat');
const cleanCSS = require('gulp-clean-css');
const rename = require('gulp-rename');
const terser = require('gulp-terser');
const newer = require('gulp-newer');
const flatten = require('gulp-flatten');
const merge = require('merge-stream');
const sourcemaps = require('gulp-sourcemaps');

function compileSass() {
    return gulp.src('src/scss/main.scss')
        .pipe(sourcemaps.init()) 
        .pipe(sass().on('error', sass.logError))
        .pipe(rename('tamarind-base.css'))
        .pipe(gulp.dest('dist/css/')) 
        .pipe(cleanCSS())
        .pipe(rename({ suffix: '.min' }))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('dist/css/')); 
}

function compileJs() {
    return gulp.src([
            'src/js/main.js',
            'src/lib/*/tm-*.js'
        ])
        .pipe(concat('tamarind-base.js'))
        .pipe(gulp.dest('dist/js/'))
        .pipe(sourcemaps.init()) 
        .pipe(terser())
        .pipe(rename({ suffix: '.min' }))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('dist/js/')); 
}

function copySwiper() {
    return gulp.src([
            'node_modules/swiper/swiper-bundle.min.js',
            'node_modules/swiper/swiper-bundle.min.js.map',
            'node_modules/swiper/swiper-bundle.min.css'
        ])
        .pipe(newer('src/lib/swiper/'))
        .pipe(gulp.dest('src/lib/swiper/'));
}

function copyLibAssets() {
    const jsFiles = gulp.src('src/lib/*/!(*tm-)*.js')
        .pipe(newer('dist/js/'))
        .pipe(flatten())
        .pipe(gulp.dest('dist/js/'));
  
    const jsMapFiles = gulp.src('src/lib/*/!(*tm-)*.js.map')
        .pipe(newer('dist/js/'))
        .pipe(flatten())
        .pipe(gulp.dest('dist/js/'));
  
    const cssFiles = gulp.src('src/lib/*/!(*tm-)*.css')
        .pipe(newer('dist/css/'))
        .pipe(flatten())
        .pipe(gulp.dest('dist/css/'));
  
    return merge(jsFiles, jsMapFiles, cssFiles);
}
  
const build = gulp.series(
    copySwiper,
    gulp.parallel(compileSass, compileJs, copyLibAssets)
  );

function watchFiles() {
    gulp.watch('src/scss/**/*.scss', compileSass);
}

exports.sass = compileSass;
exports.js = compileJs;
exports.swiper = copySwiper;
exports.copy_lib = copyLibAssets;
exports.build = build;
exports.watch = watchFiles;
exports.default = gulp.series(build, watchFiles);
