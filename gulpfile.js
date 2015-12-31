'use strict';

const path    = require('path');
const gulp    = require('gulp');
const filter  = require('gulp-filter');
const replace = require('gulp-replace');
const semver  = require('semver');
const pkg     = require('./package.json');
const extra   = require('./composer.json').extra;

/**
 * Creates bump task.
 *
 * @param {string} type a semver type.
 *
 * @return {Function} A bump task.
 */
function bumpTaskFactory(type) {
    return () => {
        const packages       = ['composer.json', 'package.json'];
        const plugin         = (pkg.main) ? [path.normalize(pkg.main)] : [];
        const loader         = (extra && extra['installer-loader']) ? [path.normalize(extra['installer-loader'])] : [];
        const plugins        = plugin.concat(loader);
        const target         = packages.concat(plugins);
        const packageFilter  = filter(packages, {restore: true});
        const pluginFilter   = filter(plugins, {restore: true});
        const version        = pkg.version;
        const newVersion     = semver.inc(version, type);
        const versionPattern = (version + '').replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
        const packageRegExp  = new RegExp('^(\\s*"version"\\s*:)\\s*"' + versionPattern + '"\\s*(,?)\\s*$', 'mg');
        const pluginRegExp   = new RegExp('^([ \\t\\/*#@]*Version:)\\s*' + versionPattern + '\\s*$', 'mg');

        gulp.src(target, {base: './'})
            .pipe(packageFilter)
            .pipe(replace(packageRegExp, '$1 "' + newVersion + '"$2'))
            .pipe(packageFilter.restore)
            .pipe(pluginFilter)
            .pipe(replace(pluginRegExp, '$1 ' + newVersion))
            .pipe(pluginFilter.restore)
            .pipe(gulp.dest('./'));
    };
}

gulp.task('bump', ['bump:patch']);

gulp.task('bump:patch', bumpTaskFactory('patch'));

gulp.task('bump:minor', bumpTaskFactory('minor'));

gulp.task('bump:major', bumpTaskFactory('major'));

gulp.task('bump:prerelease', bumpTaskFactory('prerelease'));
