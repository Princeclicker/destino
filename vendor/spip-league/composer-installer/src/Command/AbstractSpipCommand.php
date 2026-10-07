<?php

namespace SpipLeague\Composer\Command;

use Composer\Command\BaseCommand;

abstract class AbstractSpipCommand extends BaseCommand
{
    protected function getRootDir(): string
    {
        $composer = $this->requireComposer();
        $vendorDir = $composer->getConfig()
            ->get('vendor-dir');

        return \realpath($vendorDir . '/..') ?: '';
    }
}
