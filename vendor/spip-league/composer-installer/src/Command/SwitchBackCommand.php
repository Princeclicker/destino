<?php

namespace SpipLeague\Composer\Command;

use SpipLeague\Composer\Switch\Analyzer;
use SpipLeague\Composer\Switch\Operation\OperationInterface;
use SpipLeague\Composer\Switch\Switcher;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @since 0.7.0
 */
#[AsCommand(
    name: 'spip:extensions:switch-back',
    description: 'Switch back to a SPIP4-Like installation of extensions',
    aliases: ['switch:back', 'back'],
)]
class SwitchBackCommand extends AbstractSwitchCommand
{
    protected function doSwitch(Analyzer $analyzer, Switcher $switcher, OutputInterface $output, callable $reset): int
    {
        $section = \null;
        if ($output instanceof ConsoleOutputInterface) {
            $section = $output->section();
        }

        /** @var OperationInterface $down */
        foreach ($analyzer->back() as $down) {
            $output->writeln('<info>' . $down->getMessage() . '</info>');
            $switcher->to($down);
        }
        foreach ($switcher->flush($reset) as $operated) {
            $output->writeln('<info>' . $operated . '</info>');
        }

        return AbstractSpipCommand::SUCCESS;
    }
}
