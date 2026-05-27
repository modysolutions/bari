const gulp = require('gulp');
const sass = require('gulp-dart-sass');
const concat = require('gulp-concat');
const cleanCSS = require('gulp-clean-css');
const rename = require('gulp-rename');
const terser = require('gulp-terser');

function compileSass() {
    return gulp.src('src/scss/main.scss') 
      .pipe(sass().on('error', sass.logError))
      .pipe(rename('tamarind-notifications.css'))
      .pipe(gulp.dest('dist/css/')) 
      .pipe(cleanCSS())
      .pipe(rename({ suffix: '.min' }))
      .pipe(gulp.dest('dist/css/')); 
}

function compileJs() {
    return gulp.src([
        'src/js/main.js'
      ])
      .pipe(concat('tamarind-notifications.js'))
      .pipe(gulp.dest('dist/js/')) 
      .pipe(terser())
      .pipe(rename({ suffix: '.min' }))
      .pipe(gulp.dest('dist/js/')); 
}
  
const build = gulp.parallel(compileSass, compileJs);

function watchFiles() {
    gulp.watch('src/scss/**/*.scss', compileSass);
}

exports.sass = compileSass;
exports.js = compileJs;
exports.build = build;
exports.watch = watchFiles;
exports.default = gulp.series(build, watchFiles);
