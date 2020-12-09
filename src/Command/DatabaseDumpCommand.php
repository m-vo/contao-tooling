<?php

declare(strict_types=1);

namespace Mvo\ContaoTooling\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Mvo\ContaoTooling\Database\Config;
use Mvo\ContaoTooling\Database\Processor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DatabaseDumpCommand extends Command
{
    protected static $defaultName = 'database:dump';

    private AbstractSchemaManager $schemaManager;
    private Processor $processor;

    public function __construct(Connection $connection, Processor $processor)
    {
        parent::__construct();

        $this->schemaManager = $connection->getSchemaManager();
        $this->processor = $processor;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Dump the database.')
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Alternative config file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $tables = $this->schemaManager->listTableNames();

        if (empty($tables)) {
            $io->warning('No tables found.');

            return 0;
        }

        $config = new Config($input->getOption('config'));

        $ignoredTables = $config->getIgnoredTables();
        $ignoreDataFrom = $config->getIgnoredData();
        $target = $config->getDirectory();

        $countDumped = $countIgnoredData = 0;

        $io->progressStart(\count($tables));

        foreach ($tables as $table) {
            if (!\in_array($table, $ignoredTables, true)) {
                $ignoreData = \in_array($table, $ignoreDataFrom, true);

                ++$countDumped;
                $countIgnoredData += (int) $ignoreData;

                $this->processor->dumpTable($table, $target, $ignoreData);
            }

            $io->progressAdvance();
        }

        $io->progressFinish();

        $io->success(
            sprintf(
                'Process finished: dumped %d with data | dumped %d without data | skipped %d',
                $countDumped - $countIgnoredData,
                $countIgnoredData,
                \count($tables) - $countDumped
            )
        );

        return 0;
    }
}
