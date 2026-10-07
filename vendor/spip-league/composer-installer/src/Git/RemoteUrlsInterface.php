<?php

namespace SpipLeague\Composer\Git;

use Composer\Util\ProcessExecutor;

/**
 * git utilities interface.
 *
 * @since 0.7.0
 */
interface RemoteUrlsInterface
{
    /**
     * Provides the ssh counterpart of an https matching url
     */
    public function toSsh(string $url): string;

    /**
     * Provides the https counterpart of an ssh matching url
     */
    public function toHttps(string $url): string;

    public function getProcessor(): ?ProcessExecutor;

    public function getRemote(string $path): string;

    public function setRemote(string $path, string $newUrl): string;
}
