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

class DatabaseBackupCommand extends Command
{
    protected static $defaultName = 'database:backup';

    private Processor $processor;

    public function __construct(Processor $processor)
    {
        parent::__construct();

        $this->processor = $processor;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Backup the database.')
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Alternative config file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->processor->backup(
            (new Config($input->getOption('config')))->getDirectory()
        );

        (new SymfonyStyle($input, $output))->success('Backup completed');

        return 0;
    }
}
