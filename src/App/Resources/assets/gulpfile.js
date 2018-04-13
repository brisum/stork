/*jslint node: true */
"use strict";

var $           = require('gulp-load-plugins')();
var argv        = require('yargs').argv;
var gulp        = require('gulp');
var browserSync = require('browser-sync').create();
var merge       = require('merge-stream');
var sequence    = require('run-sequence');
var colors      = require('colors');
var dateFormat  = require('dateformat');
var del         = require('del');
var cleanCSS    = require('gulp-clean-css');
var inky        = require('inky');

// Enter URL of your local server here
// Example: 'http://localwebsite.dev'
var URL = '';

// Check for --production flag
var isProduction = !!(argv.production);

// Browsers to target when prefixing CSS.
var COMPATIBILITY = [
  'last 2 versions',
  'ie >= 9',
  'Android >= 2.3'
];

// File paths to various assets are defined here.
var PATHS = {
  sass: [
    'components/foundation-sites/scss',
    'components/motion-ui/src',
    'components/fontawesome/scss'
  ],
  sassEmails: [
    'components/foundation-emails/scss'
  ],
  javascript: [
    'components/jquery/dist/jquery.min.js',

    'components/what-input/what-input.js',
    'components/foundation-sites/js/foundation.core.js',
    'components/foundation-sites/js/foundation.util.*.js',

    // Paths to individual JS components defined below
    'components/foundation-sites/js/foundation.abide.js',
    'components/foundation-sites/js/foundation.accordion.js',
    'components/foundation-sites/js/foundation.accordionMenu.js',
    'components/foundation-sites/js/foundation.drilldown.js',
    'components/foundation-sites/js/foundation.dropdown.js',
    'components/foundation-sites/js/foundation.dropdownMenu.js',
    'components/foundation-sites/js/foundation.equalizer.js',
    'components/foundation-sites/js/foundation.interchange.js',
    'components/foundation-sites/js/foundation.magellan.js',
    'components/foundation-sites/js/foundation.offcanvas.js',
    'components/foundation-sites/js/foundation.orbit.js',
    'components/foundation-sites/js/foundation.responsiveMenu.js',
    'components/foundation-sites/js/foundation.responsiveToggle.js',
    'components/foundation-sites/js/foundation.reveal.js',
    'components/foundation-sites/js/foundation.slider.js',
    'components/foundation-sites/js/foundation.sticky.js',
    'components/foundation-sites/js/foundation.tabs.js',
    'components/foundation-sites/js/foundation.toggler.js',
    'components/foundation-sites/js/foundation.tooltip.js',

    'components/motion-ui/motion-ui.js',
    'components-manual/jquery.cookie.js',

    'javascript/partial/**/*.js',

    './../../../BSMAstuteFormBundle/Resources/public/javascript/jquery.serializeJSON.js',
    './../../../BSMAstuteFormBundle/Resources/public/javascript/jquery.bsm.astute-form.js'
  ],
  phpcs: [
    '**/*.php',
    '!wpcs',
    '!wpcs/**',
  ],
  pkg: [
    '**/*',
    '!**/node_modules/**',
    '!**/components/**',
    '!**/scss/**',
    '!**/bower.json',
    '!**/gulpfile.js',
    '!**/package.json',
    '!**/composer.json',
    '!**/composer.lock',
    '!**/codesniffer.ruleset.xml',
    '!**/packaged/*',
  ]
};

// Browsersync task
gulp.task('browser-sync', ['build'], function() {

  var files = [
            '**/*.php',
            'images/**/*.{png,jpg,gif}',
          ];

  browserSync.init(files, {
    // Proxy address
    proxy: URL,

    // Port #
    // port: PORT
  });
});

// Compile Sass into CSS
// In production, the CSS is compressed
gulp.task('sass', function() {
  return gulp.src('scss/style.scss')
    .pipe($.sourcemaps.init())
    .pipe($.sass({
      includePaths: PATHS.sass
    }))
    .on('error', $.notify.onError({
        message: "<%= error.message %>",
        title: "Sass Error"
    }))
    .pipe($.autoprefixer({
      browsers: COMPATIBILITY
    }))
    // Minify CSS if run with --production flag
    .pipe($.if(isProduction, cleanCSS()))
    .pipe($.if(!isProduction, $.sourcemaps.write('.')))
    .pipe(gulp.dest('../public/css'))
    .pipe(browserSync.stream({match: '**/*.css'}))

      && gulp.src('../public/css/**/*.*')
        .pipe($.flatten())
        .pipe(gulp.dest('../../../../www/bundles/app/css'));
});

// Lint all JS files in custom directory
gulp.task('lint', function() {
  return gulp.src('javascript/custom/*.js')
    .pipe($.jshint())
    .pipe($.notify(function (file) {
      if (file.jshint.success) {
        return false;
      }

      var errors = file.jshint.results.map(function (data) {
        if (data.error) {
          return "(" + data.error.line + ':' + data.error.character + ') ' + data.error.reason;
        }
      }).join("\n");
      return file.relative + " (" + file.jshint.results.length + " errors)\n" + errors;
    }));
});

// Combine JavaScript into one file
// In production, the file is minified
gulp.task('javascript', function() {
  var uglify = $.uglify()
    .on('error', $.notify.onError({
      message: "<%= error.message %>",
      title: "Uglify JS Error"
    }));

  return gulp.src(PATHS.javascript)
    .pipe($.sourcemaps.init())
    .pipe($.babel())
    .pipe($.concat('script.js', {
      newLine:'\n;'
    }))
    .pipe($.if(isProduction, uglify))
    .pipe($.if(!isProduction, $.sourcemaps.write()))
    .pipe(gulp.dest('../public/javascript'))
    .pipe(browserSync.stream())

    && gulp.src('../public/javascript/**/*.*')
        .pipe($.flatten())
        .pipe(gulp.dest('../../../../www/bundles/app/javascript'));
});

// Copy task
gulp.task('copy', function() {
    var css = gulp.src('../public/css/**/*.*')
        .pipe($.flatten())
        .pipe(gulp.dest('../../../../www/bundles/app/css'));
    var javascripts = gulp.src('../public/javascript/**/*.*')
        .pipe($.flatten())
        .pipe(gulp.dest('../../../../www/bundles/app/javascript'));

  return merge(css, javascripts);
});

// Copy task
gulp.task('copy-fontawesome', function() {
    var css = gulp.src('components/fontawesome/css/**/*.*')
        .pipe(gulp.dest('../public/css'));
    var font = gulp.src('components/fontawesome/fonts/**/*.*')
        .pipe(gulp.dest('../public/fonts'));
  return merge(css, font);
});

// Package task
gulp.task('package', ['build'], function() {
  var fs = require('fs');
  var time = dateFormat(new Date(), "yyyy-mm-dd_HH-MM");
  var pkg = JSON.parse(fs.readFileSync('./package.json'));
  var title = pkg.name + '_' + time + '.zip';

  return gulp.src(PATHS.pkg)
    .pipe($.zip(title))
    .pipe(gulp.dest('packaged'));
});

// Build task
gulp.task('build', ['clean'], function(done) {
  sequence(
      ['sass', 'javascript', 'lint'],
      'copy',
      done
  );
});

// PHP Code Sniffer task
gulp.task('phpcs', function() {
  return gulp.src(PATHS.phpcs)
    .pipe($.phpcs({
      bin: 'wpcs/vendor/bin/phpcs',
      standard: './codesniffer.ruleset.xml',
      showSniffCode: true,
    }))
    .pipe($.phpcs.reporter('log'));
});

// PHP Code Beautifier task
gulp.task('phpcbf', function () {
  return gulp.src(PATHS.phpcs)
  .pipe($.phpcbf({
    bin: 'wpcs/vendor/bin/phpcbf',
    standard: './codesniffer.ruleset.xml',
    warningSeverity: 0
  }))
  .on('error', $.util.log)
  .pipe(gulp.dest('.'));
});

// Clean task
gulp.task('clean', function(done) {
  sequence(['clean:javascript', 'clean:css'],
            done);
});

// Clean JS
gulp.task('clean:javascript', function() {
  return del([
      '../public/assets/javascript/script.js'
    ], {force: true});
});

// Clean CSS
gulp.task('clean:css', function() {
  return del([
      '../public/assets/css/style.css',
      '../public/assets/css/style.css.map'
    ], {force: true});
});

gulp.task('inky', function () {
    inky({
        src: '../module/Mailing/template/src/*.tpl.php',
        dest: '../module/Mailing/template/dist'
    }, function() {
        console.log('Done parsing.');
    });
});

// Default gulp task
// Run build task and watch for file changes
gulp.task('default', ['build' /*, 'browser-sync'*/], function() {
  // Log file changes to console
  function logFileChange(event) {
    var fileName = require('path').relative(__dirname, event.path);
    console.log('[' + 'WATCH'.green + '] ' + fileName.magenta + ' was ' + event.type + ', running tasks...');
  }

  // Sass Watch
  gulp.watch(
      [
          'scss/**/*.scss'
      ],
      ['clean:css', 'sass'])
    .on('change', function(event) {
      logFileChange(event);
    });

  // JS Watch
  gulp.watch(
      [
        'javascript/partial/**/*.js'
        // 'thirdparty/**/*.js',
        // '../vendor/brisum/wordpress/VisualComponent/LargeContent/Skin/Default/assets/js/large-content.js'
      ],
      ['clean:javascript', 'javascript', 'lint']
    )
    .on('change', function(event) {
      logFileChange(event);
    });
});
