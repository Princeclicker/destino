<?php

namespace SpipLeague\Composer\Switch\Operation;

use Composer\Composer;
use SpipLeague\Composer\Extensions\CollectionInterface;

/**
 * @since 0.7.0
 */
class RemoveFromRequire implements OperationInterface
{
    private string $vendorName;

    public function __construct(string $vendorName)
    {
        $this->vendorName = $vendorName;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getMessage(): string
    {
        return 'RemoveFromRequire ' . $this->vendorName;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getType(): string
    {
        return 'requires';
    }

    public function mark(CollectionInterface $distribution, Composer $composer): ?self
    {
        return \in_array($this->vendorName, \array_keys($composer->getPackage()->getRequires())) ? $this : \null;
    }

    public function do(CollectionInterface $distribution, Composer $composer): string
    {
        $composer->getConfig()
            ->getConfigSource()
            ->removeLink('require', $this->vendorName);

        return 'require ' . $this->vendorName . ' removed';
    }
}
