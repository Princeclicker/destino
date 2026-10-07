<?php

declare(strict_types=1);

namespace SpipLeague\Component\Kernel;

interface InstallationDetectorInterface
{
    public static function fromDir(string $directory): self;

    public function getWay(): string;

    public function getDir(string $dir = 'project'): string;
}
