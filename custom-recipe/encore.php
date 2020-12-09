<?php

declare(strict_types=1);

namespace Deployer;

task('encore:build', static function (): void {
    $encoreBin = './node_modules/.bin/encore';

    if (!file_exists($encoreBin)) {
        writeln("\r\033[1A\033[32C … skipped");

        return;
    }

    runLocally($encoreBin.' prod');
})->desc('Build assets')->local();
