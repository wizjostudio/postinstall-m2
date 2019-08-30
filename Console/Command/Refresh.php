<?php

namespace Wizjo\Postinstall\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Refresh extends SetupAbstract
{
    protected function configure()
    {
        $this->setName('refresh');
        $this->setDescription('Refresh Magento eg. after branch switch');

        $this->setDefinition([
            new InputOption('no-recompile', 'r', InputOption::VALUE_NONE, 'Do not run setup:di:compile command'),
        ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $magento = $input->getOption('magento');

        $commands = [];
        $composerCommand = [
            'command' => 'composer',
            'args' => ['dump-autoload'],
            'type' => self::COMMAND_TYPE_SYSTEM,
        ];

        foreach (['code', 'metadata'] as $subdir) {
            $path = sprintf(
                '%s/generated/%s',
                $magento,
                $subdir
            );

            if (file_exists($path) && is_dir($path)) {
                $commands[] = [
                    'command' => 'rm',
                    'args' => ['-rf', $path],
                    'type' => self::COMMAND_TYPE_SYSTEM,
                ];
            }
        }

        $commands[] = $composerCommand;

        $commands[] = [
            'command' => 'setup:upgrade',
            'type' => self::COMMAND_TYPE_MAGENTO,
        ];

        if (!$input->getOption('no-recompile')) {
            $commands[] = [
                'command' => 'setup:di:compile',
                'type' => self::COMMAND_TYPE_MAGENTO,
            ];

            $commands[] = $composerCommand;
        }

        $commands[] = [
            'command' => 'cache:flush',
            'type' => self::COMMAND_TYPE_MAGENTO,
        ];

        $this->printCommandLine($input, $output, $commands);
    }
}
