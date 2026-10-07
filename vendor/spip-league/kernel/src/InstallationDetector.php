<?php

declare(strict_types=1);

namespace SpipLeague\Component\Kernel;

use Composer\InstalledVersions;

class InstallationDetector implements InstallationDetectorInterface
{
    /** @var array<string,string> */
    private static array $ways = [
        'legacy' => '/spip.php',
        'standalone' => '/public/index.php',
        'mutualized' => '/sites/default/public/index.php',
    ];

    /** @var string[] */
    private array $dirs = [
        'project' => 'getProjectDir',
        'config' => 'getProjectConfigDir',
        'site' => 'getSiteDir',
        'web' => 'getWebDir',
    ];

    protected string $way;

    protected string $projectDir;

    private function __construct(string $way, string $projectDir)
    {
        $this->way = $way;
        $this->projectDir = $projectDir;
    }

    private static function getComposerInstallPath(): string
    {
        return (string) realpath(InstalledVersions::getRootPackage()['install_path']);
    }

    public static function fromDir(string $directory): self
    {
        foreach (self::$ways as $way => $file) {
            if (\file_exists($directory . $file)) {
                return new self($way, \realpath($directory) . '/');
            }
        }

        throw new \RuntimeException('SPIP Not fully installed in "'.$directory.'" (Entrypoint missing)');
    }

    public static function fromComposer(): self
    {
        return self::fromDir(self::getComposerInstallPath());
    }

    public function getWay(): string
    {
        return $this->way;
    }

    protected function getProjectDir(): string
    {
        return $this->projectDir;
    }

    protected function getProjectConfigDir(): string
    {
        return $this->projectDir . 'config/';
    }

    protected function getSiteDir(): string
    {
        if ($this->way == 'mutualized') {
            return $this->projectDir . trim(\dirname(self::$ways[$this->way], 2), '/') . '/';
        }

        return $this->projectDir;
    }

    protected function getWebDir(): string
    {
        if ($this->way == 'legacy') {
            return $this->projectDir;
        }

        return $this->projectDir . trim(\dirname(self::$ways[$this->way]), '/') . '/';
        ;
    }

    public function getDir(string $dir = 'project'): string
    {
        if (\in_array($dir, \array_keys($this->dirs))) {
            $callback = $this->dirs[$dir];

            return $this->$callback();
        }

        return $this->getProjectDir();
    }
}
