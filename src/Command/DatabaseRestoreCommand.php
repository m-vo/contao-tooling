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

class DatabaseRestoreCommand extends Command
{
    protected static $defaultName = 'database:restore';

    private Processor $processor;

    public function __construct(Processor $processor)
    {
        parent::__construct();

        $this->processor = $processor;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Restore the latest database backup.')
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Alternative config file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $config = new Config($input->getOption('config'));

        $files = (new Finder())
            ->in($config->getDirectory())
            ->files()
            ->name('*.sql.gz')
            ->sortByName()
            ->reverseSorting();

        if (!$files->hasResults()) {
            $io->warning('No backups found.');

            return 1;
        }

        $source = $files->getIterator()->current()->getPathname();

        $this->processor->restore($source);

        $io->success("Restore of '$source' completed");

        return 0;
    }
}
