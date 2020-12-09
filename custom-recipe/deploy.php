<?php

declare(strict_types=1);

namespace Deployer;

task('deploy:create_initial_dirs', static function (): void {
    foreach (get('initial_dirs') as $dir) {
        // Set dir variable
        set('_dir', '{{release_path}}/'.$dir);

        // Create dir if it does not exist
        run('if [ ! -d "{{_dir}}" ]; then mkdir -p {{_dir}}; fi');

        // Set rights
        run('chmod -R g+w {{_dir}}');
    }
})->desc('Create initial dirs');
