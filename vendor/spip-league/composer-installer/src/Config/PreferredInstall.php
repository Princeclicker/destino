<?php

namespace SpipLeague\Composer\Config;

use Composer\Composer;
use Composer\InstalledVersions;
use SpipLeague\Composer\SpipPaths;

class PreferredInstall
{
    private Composer $composer;

    public function __construct(Composer $composer)
    {
        $this->composer = $composer;
    }

    /**
     * Provides the directories where to check source preferred-install
     *
     * @return string[]
     */
    public function getFromConfig(): array
    {
        if (!\is_array($this->composer->getConfig()->get('preferred-install'))) {
            return [];
        }

        $toCheck = array_keys(array_filter(
            $this->composer->getConfig()
                ->get('preferred-install'),
            fn($install) => $install === 'source',
        ));

        // packages to change by mode-dev
        $rootPackage = $this->composer->getPackage();
        /** @var array{spip?:array{extensions?:string[],template?:string,private_template?:string,back_office?:string}} */
        $extra = $rootPackage->getExtra();
        $extensions = $extra['spip']['extensions'] ?? []; // -> SpipPaths::EXTENSIONS/
        $template = $extra['spip']['template'] ?? ''; // -> SpipPaths::TEMPLATE/
        $privateTemplate = $extra['spip']['private_template'] ?? ''; // -> SpipPaths::PRIVATE_TEMPLATE/
        $backOffice = $extra['spip']['back_office'] ?? ''; // -> SpipPaths::BACK_OFFICE/
        $vendorDir = $this->composer->getConfig()
            ->get('vendor-dir'); // -> vendor/
        $rootDir = realpath($vendorDir . '/..');
        // -> plugins/ ?

        $requires = InstalledVersions::getInstalledPackages();
        $toChange = array_reduce($requires, function (array $list, string $packageName) use (
            $toCheck,
            $extensions,
            $template,
            $vendorDir,
            $rootDir,
            $privateTemplate,
            $backOffice,
            $rootPackage
        ) {
            foreach ($toCheck as $packageFamily) {
                if (str_starts_with($packageName, rtrim($packageFamily, '*'))) {
                    if (in_array($packageName, $extensions)) {
                        $dir = $rootDir . '/' . SpipPaths::EXTENSIONS . '/' . $packageName;
                    } elseif ($packageName === $template) {
                        $dir = $rootDir . '/' . SpipPaths::TEMPLATE;
                    } elseif ($packageName === $privateTemplate) {
                        $dir = $rootDir . '/' . SpipPaths::PRIVATE_TEMPLATE;
                    } elseif ($packageName === $backOffice) {
                        $dir = $rootDir . '/' . SpipPaths::BACK_OFFICE;
                    } elseif ($packageName === $rootPackage->getName()) {
                        $dir = $rootDir;
                    } else {
                        $dir = $vendorDir . '/' . $packageName;
                    }
                    $list[] = $dir;
                }
            }

            return $list;
        }, []);

        return $toChange;
    }
}
