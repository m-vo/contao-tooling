<?php

declare(strict_types=1);

namespace Mvo\ContaoTooling\Database;

use Symfony\Component\Process\Process;
use Webmozart\PathUtil\Path;

class Processor
{
    public function dumpTable(string $table, string $targetDirectory, bool $ignoreData): void
    {
        $this->ensureDirectoryExists($targetDirectory);

        $target = Path::join($targetDirectory, "$table.sql");

        $data = $this->parseDatabaseUrl();

        $this->runShellCommand(
            [
                ...$this->mysqlDumpCmd(),
                $data['name'],
                '--opt', '--skip-dump-date', '--skip-comments', '--skip-extended-insert',
                '--no-create-db', '--hex-blob',
                ($ignoreData ? '--no-data' : ''),
                $table,
                "| sed 's/ AUTO_INCREMENT=[0-9]*//'",
                '>', $target,
            ]
        );
    }

    public function importTable(string $source): void
    {
        $this->ensureDatabaseExists();

        $data = $this->parseDatabaseUrl();

        $this->runShellCommand(
            [
                ...$this->mysqlCmd(),
                '--init-command="SET AUTOCOMMIT=0; SET SESSION FOREIGN_KEY_CHECKS=0;"',
                $data['name'],
                '<', $source,
            ],
            $this->getCredentialsEnv()
        );
    }

    public function backup(string $targetDirectory): void
    {
        $this->ensureDirectoryExists($targetDirectory);

        $data = $this->parseDatabaseUrl();

        $target = Path::join(
            $targetDirectory,
            sprintf('%s_%s.sql.gz', date('Y_m_d_Hi'), $data['name'])
        );

        $this->runShellCommand(
            [
                ...$this->mysqlDumpCmd(),
                '--opt',
                $data['name'],
                '| gzip -c',
                '>', $target,
            ],
            $this->getCredentialsEnv()
        );
    }

    public function restore(string $source): void
    {
        $this->ensureDatabaseExists();

        $data = $this->parseDatabaseUrl();

        $this->runShellCommand(
            [
                'gunzip -c', $source, '|',
                ...$this->mysqlCmd(),
                $data['name'],
            ],
            $this->getCredentialsEnv()
        );
    }

    private function ensureDatabaseExists(): void
    {
        $data = $this->parseDatabaseUrl();

        $this->runShellCommand(
            [
                ...$this->mysqlCmd(),
                '-e', sprintf('"CREATE DATABASE IF NOT EXISTS \`%s\`;"', $data['name']),
            ],
            $this->getCredentialsEnv()
        );
    }

    private function getCredentialsEnv(): array
    {
        $data = $this->parseDatabaseUrl();

        return [
            'MYSQL_PWD' => $data['password'],
        ];
    }

    private function mysqlCmd(): array
    {
        $data = $this->parseDatabaseUrl();

        return ['/usr/bin/mysql', '-h', $data['host'], '-u', $data['user']];
    }

    private function mysqlDumpCmd(): array
    {
        $data = $this->parseDatabaseUrl();

        return ['/usr/bin/mysqldump', '-h', $data['host'], '-u', $data['user']];
    }

    private function ensureDirectoryExists(string $path): void
    {
        (new Process(['mkdir', '-p', $path]))->mustRun();
    }

    private function runShellCommand(array $command, array $env = []): void
    {
        $process = Process::fromShellCommandline(implode(' ', $command));
        $process->setEnv($env);

        $process->mustRun();
    }

    private function parseDatabaseUrl(): array
    {
        $data = parse_url($_ENV['DATABASE_URL']);

        if (false === $data) {
            throw new \RuntimeException('Could not parse DATABASE_URL.');
        }

        return [
            'host' => $data['host'] ?? 'localhost',
            'port' => $data['port'] ?? 3306,
            'name' => ltrim($data['path'], '/'),
            'user' => $data['user'] ?? '',
            'password' => $data['pass'] ?? '',
        ];
    }
}
