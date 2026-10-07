<?php

namespace SpipLeague\Composer;

use Composer\Script\Event;
use Composer\Package\Link;

/**
 * Script for post-install-cmd and post-update-cmd.
 */
class Security
{
    /**
     * To move the safety screen after download in the config directory of the project.
     *
     * @deprecated 0.8
     */
    public static function copySafetyScreen(Event $event): void
    {
        $search = array_filter(
            $event->getComposer()
                ->getPackage()
                ->getRequires(),
            fn(Link $link) => $link->getTarget() === 'spip/security',
        );
        if (empty($search)) {
            $event->getIO()
                ->warning('spip/security is not installed.');
            return;
        }

        $event->getIO()
            ->write('Updating the safety screen ...');
        $vendorDir = $event->getComposer()
            ->getConfig()
            ->get('vendor-dir');

        try {
            \copy($vendorDir . '/spip/security/ecran_securite.php', $vendorDir . '/../config/ecran_securite.php');
            $event->getIO()
                ->write('Done.');
        } catch (\Throwable $th) {
            $event->getIO()
                ->error($th->getMessage());
        }
    }
}
