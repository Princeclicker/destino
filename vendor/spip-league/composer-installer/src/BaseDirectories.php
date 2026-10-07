<?php

namespace SpipLeague\Composer;

use Composer\Script\Event;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Script for post-install-cmd and post-update-cmd.
 */
class BaseDirectories
{
    /**
     * @var string[]
     */
    private static array $readOnlyDirs = [SpipPaths::ETC];

    /**
     * @var string[]
     */
    private static array $writeableDirs = [
        SpipPaths::VAR,
        SpipPaths::TMP,
        SpipPaths::DOC,
        SpipPaths::DIR_CACHE,
        SpipPaths::DIR_LOG,
    ];

    /**
     * To create base directories
     */
    public static function createBaseDirectories(Event $event, bool $check = false)
    {
        if ($check) {
            $event->getIO()
                ->write('Checking base directories ...');
        } else {
            $event->getIO()
                ->write('Creating base directories ...');
        }

        $vendorDir = $event->getComposer()
            ->getConfig()
            ->get('vendor-dir') . '/';
        $fs = new Filesystem();

        foreach (self::$readOnlyDirs as $dir) {
            $toCreate = $vendorDir . '../' . SpipPaths::interpolate($dir);
            if (!$fs->exists($toCreate)) {
                $event->getIO()
                    ->write('Creating ' . SpipPaths::interpolate($dir) . ' ...');
                $fs->mkdir($toCreate);
            } else {
                $event->getIO()
                    ->write(SpipPaths::interpolate($dir) . ' OK');
            }
            $event->getIO()
                ->write(
                    \sprintf('Applying chmod %04o on ', SpipPaths::CHMOD & ~0022) . SpipPaths::interpolate(
                        $dir,
                    ) . ' ...',
                );
            $fs->chmod($toCreate, SpipPaths::CHMOD, SpipPaths::UMASK | 0022);
        }

        foreach (self::$writeableDirs as $dir) {
            $toCreate = $vendorDir . '../' . SpipPaths::interpolate($dir);
            if (!$fs->exists($toCreate)) {
                $event->getIO()
                    ->write('Creating ' . SpipPaths::interpolate($dir) . ' ...');
                $fs->mkdir($toCreate);
            } else {
                $event->getIO()
                    ->write(SpipPaths::interpolate($dir) . ' OK');
            }
            $event->getIO()
                ->write(\sprintf('Applying chmod %04o on ', SpipPaths::CHMOD) . SpipPaths::interpolate($dir) . ' ...');
            $fs->chmod($toCreate, SpipPaths::CHMOD, SpipPaths::UMASK);
        }

        $event->getIO()
            ->write('Done.');
    }
}
