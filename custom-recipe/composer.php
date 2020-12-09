<?php

declare(strict_types=1);

namespace Deployer;

task('composer:validate', static function (): void {
    runLocally('symfony composer validate --no-check-all || composer validate --no-check-all');
})->desc('Build assets')->local();
