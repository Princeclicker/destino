<?php

namespace SpipLeague\Composer\Command;

use SpipLeague\Composer\Switch\Analyzer;
use SpipLeague\Composer\Switch\Operation\OperationInterface;
use SpipLeague\Composer\Switch\Switcher;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @since 0.7.0
 */
#[AsCommand(
    name: 'spip:extensions:switch-forward',
    description: 'Switch to a SPIP5-Like installation of extensions',
    aliases: ['switch:forward', 'forward'],
)]
class SwitchForwardCommand extends AbstractSwitchCommand
{
    protected function doSwitch(Analyzer $analyzer, Switcher $switcher, OutputInterface $output, callable $reset): int
    {
        /** @var OperationInterface $up */
        foreach ($analyzer->forward() as $up) {
            $output->writeln('<info>' . $up->getMessage() . '</info>');
            $switcher->to($up);
        }
        foreach ($switcher->flush($reset) as $operated) {
            $output->writeln('<info>' . $operated . '</info>');
        }

        return AbstractSpipCommand::SUCCESS;
    }
}
