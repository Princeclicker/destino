<?php

declare(strict_types=1);

namespace SpipLeague\Component\Kernel;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

class Kernel
{
    private bool $relativePath = false;

    private ?InstallationDetectorInterface $installation = null;
    private ContainerInterface $container;
    private string $requestUri;
    private string $scriptFilename;
    private string $cwd;

    public function __construct(
        ContainerInterface $container,
        string $requestUri,
        string $scriptFilename,
        string $cwd
    ) {
        $this->container = $container;
        $this->requestUri = $requestUri;
        $this->scriptFilename = $scriptFilename;
        $this->cwd = $cwd;

        $this->boot();
    }

    private function boot(): void
    {
        $installation = \null;

        if ($this->getContainer()->has('spip.installation')) {
            $installation = $this->getContainer()->get('spip.installation');
        }
        if (!$installation instanceof InstallationDetectorInterface) {
            throw new \RuntimeException('SPIP Not fully installed (service spip.installation missing or invalid)');
        }

        $this->installation = $installation;

        if (!(
            $this->getContainer()->hasParameter('spip.dirs.core')
            && $this->getContainer()->hasParameter('spip.routes.back_office')
        )) {
            throw new \RuntimeException('SPIP Kernel Not fully configured (missing parameters)');
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public function getScriptFilename(): string
    {
        return $this->scriptFilename;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getCwd(): string
    {
        return $this->cwd;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @codeCoverageIgnore
     */
    public function isFrontOffice(): bool
    {
        return !$this->isBackOffice();
    }

    public function isBackOffice(): bool
    {
        /**
         * Transformer les chemins historiques en route valide.
         *
         * 'ecrire/' => '/ecrire'
         */
        $backOffice = $this->container->getParameter('spip.routes.back_office');
        if (!\is_string($backOffice)) {
            throw new \RuntimeException('SPIP Kernel Not fully configured');
        }
        $path = strval(parse_url($this->requestUri, PHP_URL_PATH));
        return \str_ends_with($path, '/' . $backOffice);
    }

    public function relative(): self
    {
        $this->relativePath = true;

        return $this;
    }

    private function getDir(string $dir = '', string $base = 'project'): string
    {
        $base = !\is_null($this->installation) ? $this->installation->getDir($base) : '';

        if ($dir) {
            $dir = $this->getContainer()->getParameter('spip.dirs.' . $dir);
        }
        if (!\is_string($dir)) {
            throw new \RuntimeException('SPIP Not fully installed');
        }

        $dir = Path::join($base, $dir);
        if ($this->relativePath) {
            $dir = Path::makeRelative($dir, $this->cwd);
        }
        $this->relativePath = false;

        return $dir ? $dir . \DIRECTORY_SEPARATOR : $dir;
    }

    public function getRootDir(): string
    {
        return $this->getdir();
    }

    public function getCoreDir(): string
    {
        return $this->getdir('core');
    }

    public function getTmpDir(): string
    {
        return $this->getDir('tmp', 'site');
    }

    /**
     * @codeCoverageIgnore
     */
    public function getEtcDir(): string
    {
        return $this->getDir('etc', 'site');
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDocDir(): string
    {
        return $this->getDir('doc', 'web');
    }

    /**
     * @codeCoverageIgnore
     */
    public function getVarDir(): string
    {
        return $this->getDir('var', 'web');
    }

    /**
     * @codeCoverageIgnore
     */
    public function getExtensionsDir(): string
    {
        return $this->getDir('extensions');
    }

    /**
     * @codeCoverageIgnore
     */
    public function getPluginsDir(): string
    {
        return $this->getDir('plugins');
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateDir(): string
    {
        return $this->getDir('template');
    }

    /**
     * @codeCoverageIgnore
     */
    public function getPrivateTemplateDir(): string
    {
        return $this->getDir('private_template');
    }

    /**
     * @codeCoverageIgnore
     */
    public function getCustomDir(): string
    {
        return $this->getDir('custom', 'site');
    }

    public function getRootRelative(string $absolutePath): ?string
    {
        $fs = new Filesystem();

        if (Path::isAbsolute($absolutePath) && $fs->exists($absolutePath)) {
            $relativePath = Path::makeRelative($absolutePath, $this->getRootDir());

            if (!\str_starts_with($relativePath, '../')) {
                return $relativePath;
            }
        }

        return \null;
    }
}
