var gulp = require('gulp');
var react = require('gulp-react');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var minify = require('gulp-minify');
var cleanCSS = require('gulp-clean-css');
var concatCSS = require('gulp-concat-css');
var path = require("path");

var webroot = path.join(__dirname,'/web/');
var jsPath = webroot + 'compiled/js/';
var voterPath = webroot + 'assets/pages/scripts/voter/';
var userAccessPath = webroot + 'assets/pages/scripts/user-access/';


gulp.task('compile-voter',function(){
    return gulp.src([
		voterPath + 'voter.view_modal.react.js',
		voterPath + 'voter.edit_modal.react.js',
		voterPath + 'voter.upload_modal.react.js',
		voterPath + 'voter.create_modal.react.js',
		voterPath + 'voter.datatable.react.js',
        voterPath + 'voter.react.js'
    ])
        .pipe(concat('voter.js'))
        .pipe(react())
        .pipe(uglify().on('error', function(e){
            console.log(e);
        }))
        .pipe(minify({ext:{min:'.min.js'}}))
        .pipe(gulp.dest(jsPath));
});

gulp.task('compile-user-access',function(){
    return gulp.src([
		userAccessPath + 'user-access-create.modal.react.js',
		userAccessPath + 'user-access.datatable.react.js',
		userAccessPath + 'user-access.modal.react.js',
		userAccessPath + 'user.datatable.react.js',
		userAccessPath + 'user-access.react.js'
    ])
        .pipe(concat('user-access.js'))
        .pipe(react())
        .pipe(uglify().on('error', function(e){
            console.log(e);
        }))
        .pipe(minify({ext:{min:'.min.js'}}))
        .pipe(gulp.dest(jsPath));
});

/*
gulp.task('watch-pds', function() {
    gulp.watch(jsPath + 'pds\\**\\*.js', ['pds-bundle'])
});

gulp.task('watch-joborder', function() {
    gulp.watch(jsPath + 'job_order\\*.js', ['joborder-bundle']);
});
gulp.task('default',['joborder-bundle','watch']);*/