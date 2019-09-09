<?php

namespace Wizjo\Postinstall\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SetupRedis extends SetupAbstract
{
    protected function configure()
    {
        $this->setName('setup:redis');
        $this->setDescription('Sets redis as cache backend');

        $this->setDefinition([
            new InputOption('host', null, InputOption::VALUE_REQUIRED, 'Redis server', 'localhost'),
            new InputOption('port', null, InputOption::VALUE_OPTIONAL, 'Redis port'),
            new InputOption('password', null, InputOption::VALUE_OPTIONAL, 'Redis password'),

            new InputOption('cache-db', null, InputOption::VALUE_REQUIRED, 'Default cache database id', '0'),
            new InputOption('page-db', null, InputOption::VALUE_REQUIRED, 'Page cache database id', '1'),
            new InputOption('session-db', null, InputOption::VALUE_REQUIRED, 'Session database id', '2'),
        ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $host = (string) $input->getOption('host');
        $password = (string) $input->getOption('password');
        $port = (string) $input->getOption('port');

        $options = [
            '--cache-backend' => 'redis',
            '--cache-backend-redis-server' => $host,
            '--cache-backend-redis-db' => (string) $input->getOption('cache-db'),

            '--page-cache' => 'redis',
            '--page-cache-redis-server' => $host,
            '--page-cache-redis-db' => (string) $input->getOption('page-db'),

            '--session-save' => 'redis',
            '--session-save-redis-host' => $host,
            '--session-save-redis-db' => (string) $input->getOption('session-db'),
        ];

        if ($port) {
            $this->appendRedisOption($options, 'port', $port);
        }

        if ($password) {
            $this->appendRedisOption($options, 'password', $password);
        }

        $commands = [
            [
                'command' => 'setup:config:set',
                'options' => $options,
                'type' => self::COMMAND_TYPE_MAGENTO,
            ]
        ];

        $this->printCommandLine($input, $output, $commands);
    }

    private function appendRedisOption(array &$options, string $option, string $value): void
    {
        foreach (['cache-backend-redis-', 'page-cache-redis-', 'session-save-redis-'] as $prefix) {
            $options[$prefix.$option] = $value;
        }
    }
}
