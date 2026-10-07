<?php

namespace SpipLeague\Composer\Git;

use Composer\Util\ProcessExecutor;

/**
 * git utilities
 * @since 0.6.0
 */
class RemoteUrls implements RemoteUrlsInterface
{
    /**
     * @var string[]
     */
    private array $matching = ['github.com', 'gitlab.com', 'git.spip.net'];

    private ?ProcessExecutor $processor;

    public function __construct(?ProcessExecutor $processor)
    {
        $this->processor = $processor;
    }

    protected function regexp(): string
    {
        return '(' . \implode('|', \str_replace('.', '\.', $this->matching)) . ')';
    }

    /**
     * Provides the ssh counterpart of an https matching url
     */
    public function toSsh(string $url): string
    {
        return \preg_replace(',https://' . $this->regexp() . '/,', 'git@$1:', $url) ?? $url;
    }

    /**
     * Provides the https counterpart of an ssh matching url
     */
    public function toHttps(string $url): string
    {
        return \preg_replace(',git@' . $this->regexp() . ':,', 'https://$1/', $url) ?? $url;
    }

    public function getRemote(string $path): string
    {
        return 'git -C ' . $path . ' remote get-url origin';
    }

    public function setRemote(string $path, string $newUrl): string
    {
        return 'git -C ' . $path . ' remote set-url origin ' . $newUrl;
    }

    public function getProcessor(): ?ProcessExecutor
    {
        return $this->processor;
    }
}
