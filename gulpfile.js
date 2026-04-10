/*
 * Created Date: Monday March 18th 2024
 * Author: Durugy Attila
 * -----
 * Gulpfile
 * -----
 * Copyright (c) 2024 WebGear
 */


const gulp = require('gulp'),
      plugins = require('gulp-load-plugins')(),
      autoprefixer = require('autoprefixer'),
      sass = require('gulp-sass')(require('sass')),
      browserSync = require('browser-sync').create();

/**
 * 
 * SCSS -> CSS
 * 
 */
gulp.task('sass', () => {
    return gulp.src('./public/scss/**/*.scss')
    .pipe(plugins.wait(200))    
    .pipe(sass({ outputStyle: 'compressed' }).on('error', sass.logError))
    .pipe(plugins.rename({suffix:'.min'}))
    .pipe(plugins.postcss([ autoprefixer ]))
    .pipe(gulp.dest('./public/css'))
    .pipe(browserSync.stream());
});

/**
 * 
 * Minify JS files
 * 
 */
 gulp.task('minifyjs', () => {
    return gulp.src('./public/js/main.js')
    .pipe(plugins.wait(200))
    .pipe(plugins.uglify())
    .pipe(plugins.rename({suffix:'.min'}))
    .pipe(gulp.dest('./public/js'))
    .pipe(browserSync.stream());
});


/**
 * 
 * Watch file changes
 * 
 */
gulp.task('watch', () => {

    browserSync.init({
        cors : true,
        browser: "chrome",
        proxy: {
            target: "http://dev.jbx.hu",
            proxyReq: [
                function(proxyReq) {
                    proxyReq.setHeader('Access-Control-Allow-Origin', '*');
                }
            ]
        }
    });

    gulp.watch('public/scss/**/*.scss', gulp.series('sass')); //'concatcss'
    gulp.watch('./public/js/main.js', gulp.series('minifyjs'));
    gulp.watch(['app/**/*.php']).on('change', browserSync.reload);
    
});

/**
 * 
 * Default task
 * 
 */
gulp.task('default', gulp.parallel('watch'));