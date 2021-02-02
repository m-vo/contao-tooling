<?php

declare(strict_types=1);

namespace Deployer;

task('database:dump', static function (): void {
    runLocally('symfony php vendor/bin/contao-console database:dump');
})->desc('Dump database');

task('database:backup', static function (): void {
    if (!get('backup_database', true)) {
        writeln("\r\033[1A\033[32C … skipped");

        return;
    }

    run('cd {{release_path}} && {{bin/php}} {{bin/console}} database:backup');
})->desc('Backup database');

task('database:import', static function (): void {
    if (!get('import_database', false)) {
        writeln("\r\033[1A\033[32C … skipped");

        return;
    }

    run('cd {{release_path}} && {{bin/php}} {{bin/console}} database:import');
})->desc('Import database');
