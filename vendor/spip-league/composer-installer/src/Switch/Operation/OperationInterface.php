<?php

namespace SpipLeague\Composer\Switch\Operation;

use Composer\Composer;
use SpipLeague\Composer\Extensions\CollectionInterface;

/**
 * @since 0.7.0
 */
interface OperationInterface
{
    public function getMessage(): string;

    public function getType(): string;

    public function mark(CollectionInterface $distribution, Composer $composer): ?self;

    public function do(CollectionInterface $distribution, Composer $composer): string;
}
