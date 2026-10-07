<?php

namespace SpipLeague\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;

/**
 * @codeCoverageIgnore
 */
class SpipInstallerPlugin implements PluginInterface, EventSubscriberInterface, Capable
{
    public function activate(Composer $composer, IOInterface $io)
    {
        $installer = new SpipInstaller($io, $composer);
        $composer->getInstallationManager()
            ->addInstaller($installer);
    }

    public function deactivate(Composer $composer, IOInterface $io) {}

    public function uninstall(Composer $composer, IOInterface $io) {}

    public static function getSubscribedEvents()
    {
        return [
            'post-create-project-cmd' => ['onCreateProject'],
            'post-install-cmd' => ['onInstall'],
            'post-update-cmd' => ['onUpdate'],
        ];
    }

    public function onCreateProject(Event $event): void
    {
        BaseDirectories::createBaseDirectories($event);
        Security::copySafetyScreen($event);
    }

    public function onInstall(Event $event): void
    {
        BaseDirectories::createBaseDirectories($event, true);
        Security::copySafetyScreen($event);
        PluginsClearCache::clearCache($event);
        AssetsClearCache::clearCache($event);
        WheelsClearCache::clearCache($event);
    }

    public function onUpdate(Event $event): void
    {
        BaseDirectories::createBaseDirectories($event, true);
        Security::copySafetyScreen($event);
        PluginsClearCache::clearCache($event);
        AssetsClearCache::clearCache($event);
        WheelsClearCache::clearCache($event);
    }

    /**
     * @deprecated 0.8
     */
    public function updateSafetyScreen(Event $event): void
    {
        Security::copySafetyScreen($event);
    }

    /**
     * @deprecated 0.8
     */
    public function clearCache(Event $event): void
    {
        PluginsClearCache::clearCache($event);
        AssetsClearCache::clearCache($event);
        WheelsClearCache::clearCache($event);
    }

    public function getCapabilities()
    {
        return [
            \Composer\Plugin\Capability\CommandProvider::class => \SpipLeague\Composer\CommandProvider::class,
        ];
    }
}
