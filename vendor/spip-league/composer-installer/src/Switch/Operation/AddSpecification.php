<?php

namespace SpipLeague\Composer\Switch\Operation;

use Composer\Composer;
use SpipLeague\Composer\Extensions\CollectionInterface;
use SpipLeague\Composer\Extensions\SpecificationInterface;

class AddSpecification implements OperationInterface
{
    private SpecificationInterface $specification;

    public function __construct(SpecificationInterface $specification)
    {
        $this->specification = $specification;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getMessage(): string
    {
        return 'AddSpecification ' . $this->specification->getPrefix();
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
        if (isset($distribution[$this->specification->getPrefix()])) {
            return \null;
        }

        return $this;
    }

    public function do(CollectionInterface $distribution, Composer $composer): string
    {
        $distribution[] = $this->specification;

        return $this->specification->getPrefix() . ' added to ' . $distribution->getFile();
    }
}
