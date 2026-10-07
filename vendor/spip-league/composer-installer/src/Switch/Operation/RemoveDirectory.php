<?php

namespace SpipLeague\Composer\Switch\Operation;

use Composer\Composer;
use Composer\Util\Filesystem;
use SpipLeague\Composer\Extensions\CollectionInterface;

/**
 * @since 0.7.0
 */
class RemoveDirectory implements OperationInterface
{
    private Filesystem $filesystem;

    private string $directory;

    private bool $ifEmpty;

    public function __construct(Filesystem $filesystem, string $directory, bool $ifEmpty = false)
    {
        $this->filesystem = $filesystem;
        $this->directory = $directory;
        $this->ifEmpty = $ifEmpty;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getMessage(): string
    {
        return 'RemoveDirectory ' . $this->directory;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getType(): string
    {
        return 'directories';
    }

    public function mark(CollectionInterface $distribution, Composer $composer): ?self
    {
        return \is_dir($this->directory) ? $this : \null;
    }

    public function do(CollectionInterface $distribution, Composer $composer): string
    {
        if ($this->ifEmpty) {
            if ($this->filesystem->isDirEmpty($this->directory)) {
                $removed = $this->filesystem->removeDirectory($this->directory);
            } else {
                $removed = false;
            }
        } else {
            $removed = $this->filesystem->removeDirectory($this->directory);
        }

        return $this->directory . ($removed ? ' removed' : ' error on removing ...');
    }
}
