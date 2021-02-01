<?php

declare(strict_types=1);

namespace Mvo\ContaoTooling\Command;

use Mvo\ContaoTooling\Database\Config;
use Mvo\ContaoTooling\Database\Processor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class DatabaseImportCommand extends Command
{
    protected static $defaultName = 'database:import';

    private Processor $processor;

    public function __construct(Processor $processor)
    {
        parent::__construct();

        $this->processor = $processor;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Import the database.')
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Alternative config file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $config = new Config($input->getOption('config'));

        $files = (new Finder())
            ->in($config->getDirectory())
            ->files()
            ->name('*.sql');

        if (!$files->hasResults()) {
            $io->warning(
                sprintf(
                    "No dumps found in '%s'.",
                    $config->getDirectory(),
                )
            );

            return 0;
        }

        $io->progressStart($files->count());

        foreach ($files as $file) {
            $this->processor->importTable($file->getPathname());

            $io->progressAdvance();
        }

        $io->progressFinish();

        $io->success(
            sprintf(
                "Process finished: imported %d dumps from '%s'",
                $files->count(),
                $config->getDirectory(),
            )
        );

        return 0;
    }
}
