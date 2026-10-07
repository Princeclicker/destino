<?php

namespace SpipLeague\Composer\Switch\Operation;

use Composer\Composer;
use SpipLeague\Composer\Extensions\CollectionInterface;

class RemoveSpecification implements OperationInterface
{
    private string $prefix;

    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getMessage(): string
    {
        return 'RemoveSpecification ' . $this->prefix;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getType(): string
    {
        return 'distribution';
    }

    public function mark(CollectionInterface $distribution, Composer $composer): ?self
    {
        return isset($distribution[$this->prefix]) ? $this : \null;
    }

    public function do(CollectionInterface $distribution, Composer $composer): string
    {
        if (!isset($distribution[$this->prefix])) {
            return 'nothing to do';
        }

        unset($distribution[$this->prefix]);

        return 'prefix ' . $this->prefix . ' removed';
    }
}
