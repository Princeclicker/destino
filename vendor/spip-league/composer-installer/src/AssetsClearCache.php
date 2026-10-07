<?php

namespace SpipLeague\Composer;

use Composer\Script\Event;
use Composer\Util\Filesystem;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

/**
 * Script for post-install-cmd and post-update-cmd.
 */
class AssetsClearCache
{
    /**
     * @var string[]
     */
    private static array $dirs = [SpipPaths::DIR_ASSETS_CSS, SpipPaths::DIR_ASSETS_JS];

    /**
     * To delete the cache files dedicated for public compiled assets
     */
    public static function clearCache(Event $event)
    {
        $event->getIO()
            ->write('Clearing the assets cache ...');
        $vendorDir = $event->getComposer()
            ->getConfig()
            ->get('vendor-dir') . '/';

        $fs = new Filesystem($event->getComposer()->getLoop()->getProcessExecutor());
        $sffs = new SymfonyFilesystem();
        foreach (self::$dirs as $dir) {
            $fs->emptyDirectory($vendorDir . '../' . SpipPaths::interpolate($dir));
            $sffs->chmod($vendorDir . '../' . SpipPaths::interpolate($dir), SpipPaths::CHMOD, SpipPaths::UMASK);
        }

        $event->getIO()
            ->write('Done.');
    }
}
