<?php

namespace SpipLeague\Composer\Switch;

use Composer\Composer;
use SpipLeague\Composer\Extensions\CollectionInterface;
use SpipLeague\Composer\Switch\Operation\OperationInterface;

/**
 * @since 0.7.0
 */
class Switcher
{
    private CollectionInterface $distribution;

    private Composer $composer;

    /**
     * @var array<string,OperationInterface[]>
     */
    private array $operations = [
        'distribution' => [],
        'extensions' => [],
        'requires' => [],
        'directories' => [],
    ];

    public function __construct(CollectionInterface $distribution, Composer $composer)
    {
        $this->distribution = $distribution;
        $this->composer = $composer;
    }

    public function to(OperationInterface $operation): void
    {
        $operation = $operation->mark($this->distribution, $this->composer);
        if ($operation !== null) {
            $this->operations[$operation->getType()][] = $operation;
        }
    }

    public function flush(callable $reset): \Generator
    {
        foreach ($this->operations as $type => $operations) {
            yield '<comment>Operating ' . $type . '</comment>';
            foreach ($operations as $operation) {
                $message  = $operation->do($this->distribution, $this->composer);
                yield '<comment>' . $message . '</comment>';
                $this->composer = $reset();
            }
        }
    }
}
