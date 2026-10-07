<?php

namespace SpipLeague\Composer;

class SpipPaths
{
    public const CHMOD = 0777;

    public const UMASK = 0000;

    public const PLUGINS = 'plugins';

    public const EXTENSIONS = 'plugins-dist';

    public const TEMPLATE = 'squelettes-dist';

    public const PRIVATE_TEMPLATE = 'prive';

    public const BACK_OFFICE = 'ecrire';

    public const TMP = 'tmp';

    public const ETC = 'config';

    public const VAR = 'local';

    public const DOC = 'IMG';

    public const LOCAL_COMPOSER = 'composer.local.json';

    public const LOCAL_PLUGINS_DIST = 'plugins-dist.json';

    public const DIR_CACHE = '%spip.dirs.tmp%/cache';

    public const DIR_LOG = '%spip.dirs.tmp%/log';

    public const DIR_ASSETS_CSS = '%spip.dirs.var%/cache-css';

    public const DIR_ASSETS_JS = '%spip.dirs.var%/cache-js';

    public const WHEELS_CACHE = '%spip.dirs.cache%/wheels';

    /**
     * @var array<string,string>
     */
    protected static array $interpolation = [
        '%spip.dirs.tmp%' => self::TMP,
        '%spip.dirs.var%' => self::VAR,
        '%spip.dirs.cache%' => self::DIR_CACHE,
        '%spip.dirs.log%' => self::DIR_LOG,
    ];

    public static function interpolate(string $path): string
    {
        while (\preg_match(',(?<match>%[^%]+%),', $path, $matches)) {
            $path = \str_replace($matches['match'], self::$interpolation[$matches['match']], $path);
        }

        return $path;
    }
}
