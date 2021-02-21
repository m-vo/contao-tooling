<?php

declare(strict_types=1);

namespace Deployer;

use Webmozart\PathUtil\Path;

require 'recipe/common.php';
require 'recipe/symfony4.php';
require 'recipe/rsync.php';

require 'custom-recipe/composer.php';
require 'custom-recipe/contao.php';
require 'custom-recipe/database.php';
require 'custom-recipe/deploy.php';
require 'custom-recipe/encore.php';
require 'custom-recipe/server.php';

// General
set('git_tty', true);
set('ssh_multiplexing', true);
set('keep_releases', 5);
set('allow_anonymous_stats', false);

// Environment
set('symfony_env', 'prod');
set('bin/console', '{{release_path}}/vendor/bin/contao-console');
set('console_options', '--no-interaction --env={{symfony_env}}');

// Files
add('rsync', [
    'exclude' => [
        // shared
        '.env.local',
        '/files',

        // files only needed for test & build process
        '.eslintrc',
        '.gitignore',
        '.nvmrc',
        '.phpunit*',
        '.php-version',
        '.php_cs',
        '.php_cs.cache',
        '.stylelint*',
        'composer.json~',
        'deploy.php',
        'deploy-hosts.yml',
        'package.json',
        'package.lock',
        'phpstan.neon',
        'phpunit.*',
        'postcss.config.js',
        'tsconfig.json',
        'webpack.config.json',
        'yarn.local',

        // sources, cache and generated resources
        '/.database/*.gz.sql',
        '/config/parameters.yml',
        '/config/parameters.yml.dist',
        '/contao-manager',
        '/app',
        '/var',
        '/vendor',
        '/node_modules',
        '/tools',
        '/tests',
        '/assets',
        '/system',
        '/files',
        '/web/assets',
        '/web/bundles',
        '/web/files',
        '/web/share',
        '/web/system',
        '/web/app.php',
        '/web/index.php',
        '/web/preview.php',
    ],
]);

set('rsync_src', getcwd());

set('initial_dirs', [
    'assets',
    'backup',
    'system',
    'var',
    'web',
]);

set('shared_files', [
    '.env.local',
]);

set('shared_dirs', [
    'assets/images',
    'backup',
    'files',
    'var/logs',
    'web/share',
]);

set('writable_dirs', [
    'var',
]);

// Hosts
foreach (['deploy-hosts.yml', '../deploy-hosts.yml'] as $candidate) {
    if (file_exists($path = Path::canonicalize(getcwd()."/$candidate"))) {
        inventory($path);
        break;
    }
}

desc('Deploy project');
task('deploy', [
    // Prepare
    'composer:validate',
    'contao:validate',
    'encore:build',
    'database:dump',

    // Deploy
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'rsync',
    'deploy:create_initial_dirs',
    'deploy:shared',
    'deploy:vendors',
    //'deploy:writable',

    // Release
    'maintenance:enable',
    'deploy:symlink',
    'deploy:restart_fpm',
    'database:backup',
    'contao:migrate',
    'database:migrate',
    'database:import',
    'maintenance:disable',

    // Cleanup
    'deploy:unlock',
    'cleanup',
    'success',
]);

after('deploy:failed', 'deploy:unlock');
