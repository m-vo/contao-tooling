<?php

declare(strict_types=1);

namespace Mvo\ContaoTooling\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
            ->addArgument('transformations', InputArgument::IS_ARRAY, 'transformations in the form "example.wip->example.com"');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $affectedRows = 0;

        foreach ($input->getArgument('transformations') as $transformation) {
            $parts = explode('->', $transformation);

            if (2 !== \count($parts)) {
                throw new InvalidArgumentException("Bad transformation format '$transformation'.");
            }

            $affectedRows += $this->executeTransformation($parts[0], $parts[1]);
        }

        (new SymfonyStyle($input, $output))->success(
            $affectedRows > 0 ? "Updated $affectedRows records." : 'Nothing changed.'
        );

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
}
