<?php

namespace SpipLeague\Composer\Extensions;

use SpipLeague\Composer\Git\RemoteUrlsInterface;
use SpipLeague\Composer\SpipPaths;

/**
 * Déduite du contenu du fichier `./plugins-dist.json`
 *
 * @since 0.7.0
 */
class Collection implements CollectionInterface
{
    use CollectionTrait;

    private ?string $file;

    /**
     * @param SpecificationInterface[] $distribution
     */
    public function __construct(array $distribution, ?string $file = \null)
    {
        $originalCount = \count($distribution);
        $distribution = \array_filter(
            $distribution,
            fn($specification) => $specification instanceof SpecificationInterface && !empty($specification->getPrefix()),
        );
        $count = \count($distribution);
        if ($originalCount != 0 && ($count < 1 || $originalCount != $count)) {
            throw new InvalidSpecificationException('A collection must contain at least one valid specification.', 2);
        }

        $this->collection = [];
        foreach ($distribution as $specification) {
            $this->collection[$specification->getPrefix()] = $specification;
        }
        $this->keys = \array_keys($this->collection);
        $this->file = $file;
    }

    public static function fromJsonFile(RemoteUrlsInterface $changer, ?string $file = null): self
    {
        if ($file === null) {
            $file = SpipPaths::LOCAL_PLUGINS_DIST;
        }

        if (!(\file_exists($file) || \file_put_contents($file, '{}'))) {
            throw new \LogicException(
                '<error>File "' . $file . '" is missing and can\' be created. Can\'t upgrade/downgrade.</error>',
            );
        }

        $pluginsDist = \json_decode(\file_get_contents($file) ?: '', \true);
        if (!\is_array($pluginsDist)) {
            throw new \LogicException('<error>File "' . $file . '" malformed. Can\'t upgrade/downgrade.</error>');
        }

        $distribution = [];
        foreach ($pluginsDist as $prefix => $fromJson) {
            $distribution[] = (new Specification($prefix, $fromJson))->setChanger($changer);
        }

        return new self($distribution, $file);
    }

    /**
     * @codeCoverageIgnore
     */
    public function getFile(): ?string
    {
        return $this->file;
    }
}
