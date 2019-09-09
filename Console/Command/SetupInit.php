<?php

namespace Wizjo\Postinstall\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SetupInit extends SetupAbstract
{
    protected function configure()
    {
        $this->setName('setup:init');
        $this->setDescription('Sets sane post-install default values');

        $this->setDefinition([
            new InputOption('skip-setup', null, InputOption::VALUE_NONE, 'Skip database setup'),
            new InputOption('skip-baseurl', null, InputOption::VALUE_NONE, 'Skip base url setup'),
            new InputOption('no-cache-disable', null, InputOption::VALUE_NONE, 'Do not disable cache'),

            new InputOption('base-url', null, InputOption::VALUE_REQUIRED, 'Store base url'),
            new InputOption('admin-path', null, InputOption::VALUE_REQUIRED, 'Admin path', 'admin'),

            new InputOption('admin-login', null, InputOption::VALUE_REQUIRED, 'Admin login'),
            new InputOption('admin-password', null, InputOption::VALUE_REQUIRED, 'Admin password'),
            new InputOption('admin-email', null, InputOption::VALUE_REQUIRED, 'Admin email-address'),
            new InputOption('admin-name', null, InputOption::VALUE_REQUIRED, 'Admin name'),
            new InputOption('admin-lastname', null, InputOption::VALUE_REQUIRED, 'Admin lastname'),

            new InputOption('db-host', null, InputOption::VALUE_REQUIRED, 'Database host', 'localhost'),
            new InputOption('db-name', null, InputOption::VALUE_REQUIRED, 'Database name'),
            new InputOption('db-user', null, InputOption::VALUE_REQUIRED, 'Database user'),
            new InputOption('db-password', null, InputOption::VALUE_REQUIRED, 'Database password'),

            new InputOption('timezone', null, InputOption::VALUE_REQUIRED, 'Default store timezone', 'Europe/Warsaw'),
            new InputOption('locale', null, InputOption::VALUE_REQUIRED, 'Default store locale code', 'pl_PL'),
            new InputOption('currency', null, InputOption::VALUE_REQUIRED, 'Default store currency', 'PLN'),

            new InputOption('dev', null, InputOption::VALUE_NONE, 'Sets store in dev mode'),
        ]);

        $this->setSkippableOptions('skip-setup', [
            'db-host',
            'db-name',
            'db-user',
            'db-password',

            'admin-name',
            'admin-lastname',
            'admin-email',
            'admin-login',
            'admin-password',

            'admin-path',
        ]);

        $this->setSkippableOptions('skip-baseurl', ['base-url']);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $commands = [];

        if (!$input->getOption('skip-setup')) {
            $initParams = sprintf('MAGE_MODE=%s', $input->getOption('dev') ? 'development' : 'default');

            //initial setup
            $commands[] = [
                'command' => 'setup:install',
                'options' => [
                    '--db-host' => $input->getOption('db-host'),
                    '--db-name' => $input->getOption('db-name'),
                    '--db-user' => $input->getOption('db-user'),
                    '--db-password' => $input->getOption('db-password'),

                    '--admin-firstname' => $input->getOption('admin-name'),
                    '--admin-lastname' => $input->getOption('admin-lastname'),
                    '--admin-email' => $input->getOption('admin-email'),
                    '--admin-user' => $input->getOption('admin-login'),
                    '--admin-password' => $input->getOption('admin-password'),

                    '--backend-frontname' => $input->getOption('admin-path'),

                    '--magento-init-params' => $initParams,
                ],
                'type' => self::COMMAND_TYPE_MAGENTO,
            ];
        }

        //default options
        $currency = $input->getOption('currency');
        $allowedCurrencies = [$currency];
        if ($currency !== 'USD') {
            $allowedCurrencies[] = 'USD';
        }

        $configOptions = [
            'general/locale/timezone' => $input->getOption('timezone'),
            'general/locale/code' => $input->getOption('locale'),
            'currency/options/base' => $currency,
            'currency/options/allow' =>  implode(',', $allowedCurrencies),
            'currency/options/default' => $currency,
            'web/seo/use_rewrites' => 1
        ];

        if (!$input->getOption('skip-baseurl')) {
            $baseUrl = rtrim($input->getOption('base-url'), '/').'/';

            if (filter_var($baseUrl, FILTER_VALIDATE_URL) === false) {
                throw new \RuntimeException(sprintf(
                    'Base url "%s" is not valid url',
                    $baseUrl
                ));
            }

            $configOptions['web/unsecure/base_url'] = $baseUrl;
        }

        foreach ($configOptions as $option => $value) {
            $commands[] = [
                'command' => 'config:set',
                'args' => [
                    $option,
                    $value,
                ],
                'type' => self::COMMAND_TYPE_MAGENTO,
            ];
        }

        $commands[] = [
            'command' => 'setup:upgrade',
            'type' => self::COMMAND_TYPE_MAGENTO,
        ];

        if ($input->getOption('no-cache-disable')) {
            $commands[] = [
                'command' => 'cache:disable',
                'type' => self::COMMAND_TYPE_MAGENTO,
            ];
        }

        $commands[] = [
            'command' => 'index:reindex',
            'type' => self::COMMAND_TYPE_MAGENTO,
        ];
        $this->printCommandLine($input, $output, $commands);
    }
}
