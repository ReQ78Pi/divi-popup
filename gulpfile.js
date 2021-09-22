'use strict';
var gulp                    = require('gulp');
var plumber                 = require('gulp-plumber');
var watch                   = require('gulp-watch');
var browserSync             = require('browser-sync').create();
var sassGlob                = require('gulp-sass-glob');
var sass                    = require('gulp-sass');
var autoprefixer            = require('gulp-autoprefixer');
var cssnano                 = require('gulp-cssnano');
var rename                  = require('gulp-rename');
var rigger                  = require('gulp-rigger');
var include                 = require('gulp-include');
var uglify                  = require('gulp-uglify');


/*----------------------------------------------------------------
Path
-----------------------------------------------------------------*/

var path = {
	watch: {
		scss:       'src/**/*.scss',
		js:         'src/js/*.js',
	},
	src: {
		main_scss:  'src/main_scss/main-style.scss',
		admin_scss: 'src/admin_scss/admin-style.scss',
		vb_scss:    'src/admin_scss/vb-style.scss',
		admin_js:   'src/admin_js/main-script.js',
	},
	dest: {
		css:        'assets/css',
		js:         'assets/js',
	},
	server: {
		domain:     'test.local', // Your local server
	},
};


/*----------------------------------------------------------------
Server
-----------------------------------------------------------------*/

gulp.task('browser-sync', function () {
	browserSync.init({
		proxy: (path.server.domain),
		notify: false,
		// online: false, // Work offline without internet connection
		// tunnel: true, tunnel: 'projectname', // Demonstration page: http://projectname.localtunnel.me
	});
});


/*----------------------------------------------------------------
Task: gulp style
-----------------------------------------------------------------*/

gulp.task('style', function () {
	return gulp.src(path.src.main_scss)
	.pipe(plumber())
	.pipe(sassGlob())
	.pipe(sass({
		outputStyle: 'expanded'
	}))
	.pipe(autoprefixer({
		cascade: true,
		grid: true,
	}))
	.pipe(cssnano({
		zindex: false
	}))
	.pipe(rename({ basename: 'divi-popup-style', suffix: '.min' }))
	.pipe(gulp.dest(path.dest.css))
	.pipe(browserSync.reload({ stream: true }));
});


/*----------------------------------------------------------------
Task: gulp admin-style
-----------------------------------------------------------------*/

gulp.task('admin-style', function () {
	return gulp.src(path.src.admin_scss)
	.pipe(plumber())
	.pipe(sassGlob())
	.pipe(sass({
		outputStyle: 'expanded'
	}))
	.pipe(autoprefixer({
		cascade: true,
		grid: true,
	}))
	.pipe(cssnano({
		zindex: false
	}))
	.pipe(rename({ basename: 'divi-popup-admin-style', suffix: '.min' }))
	.pipe(gulp.dest(path.dest.css))
	.pipe(browserSync.reload({ stream: true }));
});


/*----------------------------------------------------------------
Task: gulp vb-style
-----------------------------------------------------------------*/

gulp.task('vb-style', function () {
	return gulp.src(path.src.vb_scss)
	.pipe(plumber())
	.pipe(sassGlob())
	.pipe(sass({
		outputStyle: 'expanded'
	}))
	.pipe(autoprefixer({
		cascade: true,
		grid: true,
	}))
	.pipe(cssnano({
		zindex: false
	}))
	.pipe(rename({ basename: 'divi-popup-vb-style', suffix: '.min' }))
	.pipe(gulp.dest(path.dest.css))
	.pipe(browserSync.reload({ stream: true }));
});


/*----------------------------------------------------------------
Task: gulp admin-script
-----------------------------------------------------------------*/

gulp.task('admin-script', function () {
	return gulp.src(path.src.admin_js)
	.pipe(plumber())
	.pipe(include()).on('error', console.log)
	.pipe(uglify())
	.pipe(rename({ basename: 'divi-popup-admin-js', suffix: '.min' }))
	.pipe(gulp.dest(path.dest.js))
});


/*----------------------------------------------------------------
Task: gulp watch
-----------------------------------------------------------------*/

gulp.task('watch', function () {
	gulp.watch([path.watch.scss], gulp.parallel('style'));
	gulp.watch([path.watch.scss], gulp.parallel('admin-style'));
	gulp.watch([path.watch.scss], gulp.parallel('vb-style'));
	gulp.watch([path.watch.js], gulp.parallel('admin-script'));
});

gulp.task('watch', gulp.parallel('style', 'admin-style', 'vb-style', 'admin-script', 'browser-sync', 'watch'));
