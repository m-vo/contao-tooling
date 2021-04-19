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

class DatabasePayloadImportCommand extends Command
{
    protected static $defaultName = 'database:payload-import';

    private Processor $processor;

    public function __construct(Processor $processor)
    {
        parent::__construct();

        $this->processor = $processor;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Import custom payloads into the database.')
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Alternative config file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $config = new Config($input->getOption('config'));

        $files = $config->getPayloads();
        $count = count($files);

        if (0 === $count) {
            $io->warning('No payloads registered.');

            return 0;
        }

        $io->progressStart($count);

        foreach ($files as $file) {
            $this->processor->runFile($file);

            $io->progressAdvance();
        }

        $io->progressFinish();

        $io->success(
            sprintf(
                "Process finished: imported %d payloads.",
                $count,
            )
        );

        return 0;
    }
}
