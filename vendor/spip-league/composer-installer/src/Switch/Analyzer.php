<?php

namespace SpipLeague\Composer\Switch;

use Composer\Composer;
use Composer\Util\Filesystem;
use SpipLeague\Composer\Extensions\CollectionInterface;
use SpipLeague\Composer\Extensions\Specification;
use SpipLeague\Composer\SpipPaths;
use SpipLeague\Composer\Switch\Operation\AddSpecification;
use SpipLeague\Composer\Switch\Operation\AddToRequire;
use SpipLeague\Composer\Switch\Operation\AddToSpipExtraExtensions;
use SpipLeague\Composer\Switch\Operation\RemoveDirectory;
use SpipLeague\Composer\Switch\Operation\RemoveFromRequire;
use SpipLeague\Composer\Switch\Operation\RemoveFromSpipExtraExtensions;
use SpipLeague\Composer\Switch\Operation\RemoveSpecification;

/**
 * @since 0.7.0
 */
class Analyzer
{
    private Filesystem $filesystem;

    private CollectionInterface $distribution;

    private Composer $composer;

    public function __construct(
        CollectionInterface $distribution,
        Composer $composer,
    ) {
        $this->distribution = $distribution;
        $this->composer = $composer;
        $this->filesystem = new Filesystem($this->composer->getLoop()->getProcessExecutor());
    }

    /**
     * @return string[]
     */
    private function getExtensions(): array
    {
        $extensions = [];
        /** @var array{spip?:array{extensions?:mixed}} $extra */
        $extra = $this->composer->getPackage()
            ->getExtra();
        if (isset($extra['spip']['extensions']) && \is_array($extra['spip']['extensions'])) {
            $extensions = $extra['spip']['extensions'];
        }

        return $extensions;
    }

    public function forward(): \Generator
    {
        $extensions = $this->getExtensions();
        $vendorNames = \array_keys($this->composer->getPackage()->getRequires());

        foreach ($this->distribution as $specification) {
            if (\is_dir($specification->getPath())) {
                yield new RemoveDirectory($this->filesystem, $specification->getPath());
            }

            if (!\in_array($specification->computeVendorName(), $extensions)) {
                yield new AddToSpipExtraExtensions($specification->computeVendorName());
            }

            if (!\in_array($specification->computeVendorName(), $vendorNames)) {
                yield new AddToRequire($specification->computeVendorName(), $specification->computeConstraint());
            }

            yield new RemoveSpecification($specification->getPrefix());
        }
    }

    public function back(): \Generator
    {
        $extensions = $this->getExtensions();
        $requires = $this->composer->getPackage()
            ->getRequires();
        $vendorNames = \array_keys($requires);
        $vendorDirs = [];

        foreach ($extensions as $extension) {
            $prefix = '';
            if (\in_array($extension, $vendorNames)) {
                $prefix = (string) \preg_replace(',^[^/]+/,', '', $extension);
                yield new RemoveFromRequire($extension);
            }

            if ($prefix) {
                yield new AddSpecification(Specification::createFromComposer($this->composer, $extension));

                yield new RemoveDirectory($this->filesystem, SpipPaths::EXTENSIONS . '/' . $extension);
                $vendorDirs[] = SpipPaths::EXTENSIONS . '/' . (string) \preg_replace(',/.+$,', '', $extension);
            }

            yield new RemoveFromSpipExtraExtensions($extension);
        }

        foreach (\array_unique($vendorDirs) as $vendorDir) {
            yield new RemoveDirectory($this->filesystem, $vendorDir, true);
        }
    }
}
