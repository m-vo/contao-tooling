<?php

declare(strict_types=1);

namespace Deployer;

task('contao:validate', static function (): void {
    run('symfony php ./vendor/bin/contao-console contao:version');
})->desc('Validate local Contao setup')->local();

task('contao:migrate', static function (): void {
    run('{{bin/php}} {{bin/console}} contao:migrate --with-deletes {{console_options}}');
})->desc('Run Contao migrations');

task('maintenance:enable', static function (): void {
    run('{{bin/php}} {{bin/console}} lexik:maintenance:lock {{console_options}}');
})->desc('Enable maintenance mode');

task('maintenance:disable', static function (): void {
    run('{{bin/php}} {{bin/console}} lexik:maintenance:unlock {{console_options}}');
})->desc('Disable maintenance mode');

task('install:lock', static function (): void {
    run('{{bin/php}} {{bin/console}} contao:install:lock {{console_options}}');
})->desc('Lock the install tool');
