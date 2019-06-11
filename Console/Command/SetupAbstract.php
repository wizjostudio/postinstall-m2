<?php

namespace Wizjo\Postinstall\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

abstract class SetupAbstract extends Command
{
    private $requiredOptions = [];
    private $skippableOptions = [];

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $skip = [];
        foreach ($this->skippableOptions as $switch => $options) {
            if ($input->getOption($switch) === true) {
                $skip[] = $options;
            }
        }

        if ($skip) {
            $skip = array_merge(...$skip);
        }

        $helper = $this->getHelper('question');
        foreach ($input->getOptions() as $option => $value) {
            if ($value !== null || in_array($option, $skip, true) || $this->getDefinition()->getOption($option)->isValueRequired() === false) {
                continue;
            }

            $question = new Question(sprintf(
                'Option "%s" is missing. Please provide a value: ',
                $option
            ));

            if (preg_match('/.+\-(pass|password)$/', $option) === 1) {
                $question->setHidden(true);
                $question->setHiddenFallback(false);
            }

            $value = $helper->ask($input, $output, $question);
            $input->setOption($option, $value);
        }
    }

    protected function setOptionsToAsk(array $options): void
    {
        $this->requiredOptions = $options;
    }

    protected function setSkippableOptions(string $optionSwitch, array $options): void
    {
        $this->skippableOptions[$optionSwitch] = $options;
    }

    protected function printCommandLine(InputInterface $input, OutputInterface $output, array $commands): void
    {
        $magento = $input->getOption('magento');

        $outputCommands = [];
        foreach ($commands as $command) {
            $args = [];
            $options = [];

            if (array_key_exists('args', $command)) {
                foreach ($command['args'] as $arg) {
                    $args[] = escapeshellarg($arg);
                }
            }

            if (array_key_exists('options', $command)) {
                foreach ($command['options'] as $option => $value) {
                    $options[] = escapeshellarg($option) . ' ' . escapeshellarg($value);
                }
            }

            $outputCommands[]= sprintf(
                '%s/bin/magento %s %s %s',
                $magento,
                $command['command'],
                implode(' ', $options),
                implode(' ', $args)
            );
        }

        $output->writeln(implode(' && ', $outputCommands));
    }
}
