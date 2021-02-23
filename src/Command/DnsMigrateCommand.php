<?php

declare(strict_types=1);

namespace Mvo\ContaoTooling\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DnsMigrateCommand extends Command
{
    protected static $defaultName = 'contao:dns-migrate';

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        parent::__construct();

        $this->connection = $connection;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Migrate root page DNS entries.')
            ->addArgument('transformations', InputArgument::IS_ARRAY, 'transformations in the form "example.wip->example.com"')
            ->addOption('rootProtection', 'p', InputOption::VALUE_REQUIRED, 'enable/disable root protection', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $affectedRows = 0;

        foreach ($input->getArgument('transformations') as $transformation) {
            $parts = explode('->', $transformation);

            if (2 !== \count($parts)) {
                throw new InvalidArgumentException("Bad transformation format '$transformation'.");
            }

            $affectedRows += $this->executeTransformation($parts[0], $parts[1]);
        }

        $io->success(
            $affectedRows > 0 ? "Updated $affectedRows records." : 'Nothing changed.'
        );

        if (null !== ($rootProtection = $input->getOption('rootProtection'))) {
            $rootProtection = (bool) $rootProtection;

            $this->setRootProtection($rootProtection);

            $io->success(
                ($rootProtection ? 'Enabled' : 'Disabled').' root protection.'
            );
        }

        return 0;
    }

    private function executeTransformation(string $from, string $to): int
    {
        return $this->connection->update(
            'tl_page',
            ['dns' => $to],
            ['dns' => $from]
        );
    }

    private function setRootProtection(bool $state): void
    {
        $this->connection->update(
            'tl_page',
            ['rootProtection' => $state ? '1' : ''],
            ['type' => 'root'],
        );
    }
}
