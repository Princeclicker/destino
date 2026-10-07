<?php

namespace SpipLeague\Composer\Command;

use SpipLeague\Composer\Extensions\Collection;
use SpipLeague\Composer\Extensions\CollectionInterface;
use SpipLeague\Composer\Factory;
use SpipLeague\Composer\Switch\Analyzer;
use SpipLeague\Composer\Switch\Switcher;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @since 0.7.0
 */
abstract class AbstractSwitchCommand extends AbstractSpipCommand
{
    abstract protected function doSwitch(
        Analyzer $analyzer,
        Switcher $switcher,
        OutputInterface $output,
        callable $reset,
    ): int;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $composer = $this->requireComposer();
            $distribution = Collection::fromJsonFile(Factory::createRemoteUrls($composer));
            $analyzer = new Analyzer($distribution, $composer);
            $switcher = new Switcher($distribution, $composer);
        } catch (\Throwable $th) {
            $output->writeln($th->getMessage());

            return AbstractSpipCommand::FAILURE;
        }

        $output->writeln([
            '<info>' . $this->getDescription() . '</info>',
            '<comment>Composer file : ' . $composer->getConfig()->getConfigSource()->getName() . '</comment>',
            '<comment>Distribution file : ' . $distribution->getFile() . '</comment>',
        ]);

        $cmd = $this;
        $exitCode = $this->doSwitch($analyzer, $switcher, $output, function () use ($cmd) {
            $cmd->resetComposer();

            return $cmd->requireComposer();
        });

        $exitCode &= $this->flushDistributionfile($distribution);

        if (
            $exitCode === AbstractSpipCommand::SUCCESS
        ) {
            $exitCode = $this->getApplication()
                ->doRun(new ArrayInput(['command' => 'update']), $output);
            if (
                $exitCode === AbstractSpipCommand::SUCCESS
                && $this->getApplication()
                    ->has('normalize')
            ) {
                $exitCode = $this->getApplication()
                    ->doRun(new ArrayInput(['command' => 'normalize']), $output);
            }
        }

        return $exitCode;
    }

    protected function flushDistributionfile(CollectionInterface $distribution): int
    {
        $distributionFile = $distribution->getFile();

        if ($distributionFile) {
            if (!\count($distribution) && \file_exists($distributionFile)) {
                \unlink($distributionFile);
            } else {
                \file_put_contents(
                    $distributionFile,
                    \json_encode($distribution, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES),
                );
            }
        }

        return AbstractSpipCommand::SUCCESS;
    }
}
