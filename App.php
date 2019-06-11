<?php
namespace Wizjo\Postinstall;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputOption;

class App extends Application
{
    protected function getDefaultInputDefinition()
    {
        $inputDefinition = parent::getDefaultInputDefinition();
        $inputDefinition->addOption(new InputOption(
            'magento',
            'm',
            InputOption::VALUE_REQUIRED,
            'Path to magento installation dir',
            getcwd()
        ));

        return $inputDefinition;
    }
}
