<?php

namespace SpipLeague\Component\Kernel;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Raccourci temporaire pour accéder à l’application SPIP.
 */
function app(): Kernel
{
    static $installation = null;
    static $container = null;
    static $kernel = null;

    if (\is_null($installation)) {
        $installation = InstallationDetector::fromComposer();
    }

    if (is_null($container)) {
        $container = new ContainerBuilder();

        foreach (['dirs', 'routes'] as $config) {
            $file = $installation->getDir('config') . 'spip/' . $config . '.php';
            if (\file_exists($file)) {
                $fromFile = require_once $file;
                foreach ($fromFile as $key => $value) {
                    $container->setParameter('spip.' . $config . '.' . $key, $value);
                }
            }
        }

        $container->set('spip.installation', $installation);
    }

    if (is_null($kernel)) {
        $kernel = new Kernel($container, $_SERVER['REQUEST_URI'] ?? '/', $_SERVER['SCRIPT_FILENAME'], (string) \getcwd());
    }

    return $kernel;
}

/**
 * Raccourci temporaire pour accéder à un paramètre du container symfony.
 *
 * @return mixed
 */
function param(string $name)
{
    return app()->getContainer()->getParameter($name);
}

/**
 * Raccourci temporaire pour accéder à un service du container symfony.
 */
function service(string $id): object
{
    return app()->getContainer()->get($id);
}
