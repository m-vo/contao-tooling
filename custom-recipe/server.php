<?php

declare(strict_types=1);

namespace Deployer;

task('deploy:restart_fpm', static function (): void {
    if (!has('php-fpm')) {
        writeln("\r\033[1A\033[32C … skipped");

        return;
    }

    $phpFpm = get('php-fpm');

    run("sudo service $phpFpm restart");
})->desc('restart php-fpm');
