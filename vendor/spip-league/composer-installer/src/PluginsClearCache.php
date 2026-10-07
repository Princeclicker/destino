<?php

namespace SpipLeague\Composer;

use Composer\Script\Event;
use Composer\Util\Filesystem;

/**
 * Script for post-install-cmd and post-update-cmd.
 */
class PluginsClearCache
{
    /**
     * @var string[]
     */
    private static array $files = [
        '%spip.dirs.tmp%/plugin_xml_cache.gz',
        '%spip.dirs.cache%/charger_pipelines.php',
        '%spip.dirs.cache%/charger_plugins_chemins.php',
        '%spip.dirs.cache%/charger_plugins_fonctions.php',
        '%spip.dirs.cache%/charger_plugins_options.php',
        /**
         * Present here because it may contain plugin paths
         */
        '%spip.dirs.cache%/chemin.txt',
    ];

    /**
     * To delete the cache files dedicated of the activated plugins
     */
    public static function clearCache(Event $event)
    {
        $event->getIO()
            ->write('Clearing the plugins cache ...');
        $vendorDir = $event->getComposer()
            ->getConfig()
            ->get('vendor-dir') . '/';

        $fs = new Filesystem($event->getComposer()->getLoop()->getProcessExecutor());
        foreach (self::$files as $file) {
            $fs->remove($vendorDir . '../' . SpipPaths::interpolate($file));
        }

        $event->getIO()
            ->write('Done.');
    }
}
