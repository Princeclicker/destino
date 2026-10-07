<?php

namespace SpipLeague\Composer;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;

class SpipInstaller extends LibraryInstaller
{
    public function getInstallPath(PackageInterface $package)
    {
        if ($package->getType() === 'spip-ecrire') {
            return './' . SpipPaths::BACK_OFFICE;
        }

        if ($package->getType() === 'spip-prive') {
            return './' . SpipPaths::PRIVATE_TEMPLATE;
        }

        if ($package->getType() === 'spip-plugin') {
            /** @var array{spip?:array{extensions?:string[],template?:string,private_template?:string,back_office?:string}} */
            $extra = $this->composer->getPackage()
                ->getExtra();
            $template = $extra['spip']['template'] ?? '';
            $extensions = $extra['spip']['extensions'] ?? [];
            $name = $package->getName();

            if ($template === $name) {
                return './' . SpipPaths::TEMPLATE;
            }

            if (in_array($name, $extensions)) {
                return './' . SpipPaths::EXTENSIONS . '/' . $name;
            }

            return './' . SpipPaths::PLUGINS . '/' . $name;
        }

        if (in_array($package->getType(), ['spip-lang', 'spip-theme'])) {
            throw new \InvalidArgumentException(
                'Unable to install this package as its type is not supported for now.' .
                    ' Wait for a future SPIP version for using it.',
            );
        }

        return parent::getInstallPath($package);
    }

    public function supports(string $packageType)
    {
        return in_array($packageType, [
            //Since SPIP 4.4 some packages need to be installed in some particular places
            'spip-ecrire',  //SPIP CMS Component a.k.a. spip/ecrire v4.4
            'spip-prive',   //SPIP CMS Component a.k.a. spip/prive v1.0
            'spip-lang',    //SPIP CMS Component a.k.a. spip/lang
            'spip-theme',   //SPIP CMS Component a.k.a. spip/theme
            'spip-plugin',  //an optional or required SPIP plugin or the default template
        ]);
    }
}
