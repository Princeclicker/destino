<?php

namespace SpipLeague\Composer;

use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use SpipLeague\Composer\Command\LocalCommand;
use SpipLeague\Composer\Command\ModeDevCommand;
use SpipLeague\Composer\Command\SwitchBackCommand;
use SpipLeague\Composer\Command\SwitchForwardCommand;

/**
 * @codeCoverageIgnore
 */
class CommandProvider implements CommandProviderCapability
{
    public function getCommands()
    {
        return [new LocalCommand(), new ModeDevCommand(), new SwitchForwardCommand(), new SwitchBackCommand()];
    }
}
