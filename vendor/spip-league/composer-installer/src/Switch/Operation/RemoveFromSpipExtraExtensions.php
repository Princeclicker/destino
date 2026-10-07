<?php

namespace SpipLeague\Composer\Switch\Operation;

use Composer\Composer;
use SpipLeague\Composer\Extensions\CollectionInterface;

/**
 * @since 0.7.0
 */
class RemoveFromSpipExtraExtensions implements OperationInterface
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
        return 'RemoveFromSpipExtraExtensions ' . $this->vendorName;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getType(): string
    {
        return 'extensions';
    }

    public function mark(CollectionInterface $distribution, Composer $composer): ?self
    {
        $extensions = [];
        /** @var array{spip?:array{extensions?:mixed}} $extra */
        $extra = $composer->getPackage()
            ->getExtra();
        if (isset($extra['spip']['extensions']) && \is_array($extra['spip']['extensions'])) {
            $extensions = $extra['spip']['extensions'];
        }

        return \in_array($this->vendorName, $extensions) ? $this : \null;
    }

    public function do(CollectionInterface $distribution, Composer $composer): string
    {
        $extensions = [];
        /** @var array{spip?:array{extensions?:mixed}} $extra */
        $extra = $composer->getPackage()
            ->getExtra();
        if (isset($extra['spip']['extensions']) && \is_array($extra['spip']['extensions'])) {
            $extensions = $extra['spip']['extensions'];
        }
        if (!\in_array($this->vendorName, $extensions)) {
            return 'nothing to do';
        }

        $composer->getConfig()
            ->getConfigSource()
            ->addProperty('extra.spip.extensions', \array_diff($extensions, [$this->vendorName]));

        return 'extensions ' . $this->vendorName . ' removed';
    }
}
