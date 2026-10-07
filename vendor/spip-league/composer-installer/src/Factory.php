<?php

namespace SpipLeague\Composer;

use Composer\Composer;
use SpipLeague\Composer\Git\RemoteUrls;
use SpipLeague\Composer\Git\RemoteUrlsInterface;

/**
 * @since 0.7.0
 */
class Factory
{
    public static function createRemoteUrls(Composer $composer): RemoteUrlsInterface
    {
        return new RemoteUrls($composer->getLoop()->getProcessExecutor());
    }
}
