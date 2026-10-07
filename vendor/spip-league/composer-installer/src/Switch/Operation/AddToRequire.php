<?php

namespace SpipLeague\Composer\Switch\Operation;

use Composer\Composer;
use SpipLeague\Composer\Extensions\CollectionInterface;

class AddToRequire implements OperationInterface
{
    private string $vendorName;

    private string $constraint;

    public function __construct(string $vendorName, string $constraint)
    {
        $this->vendorName = $vendorName;
        $this->constraint = $constraint;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getMessage(): string
    {
        return 'AddToRequire ' . $this->vendorName . ':' . $this->constraint;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getType(): string
    {
        return 'requires';
    }

    /**
     * @codeCoverageIgnore
     */
    public function mark(CollectionInterface $distribution, Composer $composer): ?self
    {
        return $this;
    }

    public function do(CollectionInterface $distribution, Composer $composer): string
    {
        $composer->getConfig()
            ->getConfigSource()
            ->addLink('require', $this->vendorName, $this->constraint);

        return 'require ' . $this->vendorName . ':' . $this->constraint . ' added.';
    }
}
