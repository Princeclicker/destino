<?php

namespace SpipLeague\Composer;

use Composer\Script\Event;
use Composer\Util\Filesystem;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

class WheelsClearCache
{
    public static function clearCache(Event $event): void
    {
        $event->getIO()
            ->write('Clearing the Textwheel cache ...');
        $vendorDir = $event->getComposer()
            ->getConfig()
            ->get('vendor-dir') . '/';

        $fs = new Filesystem($event->getComposer()->getLoop()->getProcessExecutor());
        $sffs = new SymfonyFilesystem();
        $fs->emptyDirectory($vendorDir . '../' . SpipPaths::interpolate(SpipPaths::WHEELS_CACHE));
        $sffs->chmod(
            $vendorDir . '../' . SpipPaths::interpolate(SpipPaths::WHEELS_CACHE),
            SpipPaths::CHMOD,
            SpipPaths::UMASK,
        );

        $event->getIO()
            ->write('Done.');
    }
}
