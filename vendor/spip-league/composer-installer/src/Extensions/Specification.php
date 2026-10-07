<?php

namespace SpipLeague\Composer\Extensions;

use Composer\Composer;
use Composer\Factory;
use SpipLeague\Composer\Git\RemoteUrlsInterface;
use SpipLeague\Composer\SpipPaths;

/**
 * Spécifiations d'installation d'un "plugins-dist" en SPIP4.4.
 *
 * Déduites du contenu du fichier `./plugins-dist.json`
 *
 * @since 0.7.0
 */
class Specification implements SpecificationInterface
{
    private string $prefix;

    private string $path;

    private string $source;

    private string $branch;

    private string $tag;

    /**
     * @var array<int,array{name:string,version:string,source?:array{url?:string}}>|null
     */
    private static ?array $packages = \null;

    protected ?RemoteUrlsInterface $changer = null;

    private string $validationError = '';

    /**
     * @param array{path:string,source:string,branch?:string,tag?:string} $fromJson
     */
    public function __construct(string $prefix, array $fromJson)
    {
        if (!$this->validate($prefix, $fromJson)) {
            throw new InvalidSpecificationException($this->validationError, 1);
        }

        $this->prefix = $prefix;
        $this->path = $fromJson['path'];
        $this->source = $fromJson['source'];
        $this->branch = $fromJson['branch'] ?? '';
        $this->tag = $fromJson['tag'] ?? '';
    }

    /**
     * @param array{path?:string,source?:string,branch?:string,tag?:string} $fromJson
     */
    private function validate(string $prefix, array $fromJson): bool
    {
        if (\strlen($prefix) == 0) {
            $this->validationError = 'empty prefix is invalid';

            return false;
        }

        if (!(isset($fromJson['path']) &&  \strlen($fromJson['path']) > 0)) {
            $this->validationError = 'empty path for "' . $prefix . '" is invalid';

            return false;
        }

        if (!(isset($fromJson['source']) &&  \strlen($fromJson['source']) > 0)) {
            $this->validationError = 'empty source for "' . $prefix . '" is invalid';

            return false;
        }

        $this->validationError  = '';

        return true;
    }

    /**
     * Détermine le `prefix` équivalent.
     *
     * Par convention, celui-ci est égal au name du `vendor/name`.
     * -> `git@url-git-server:vendor/name.git` ou `https://url-git-server/vendor/name.git`
     *
     * Limitation: l'extension DOIT être composerisée et être
     * distribuée dans un dépôt composer accessible.
     */
    public static function createFromComposer(Composer $composer, string $vendorName): self
    {
        if (self::$packages === null) {
            $lockFile = Factory::getLockFile(Factory::getComposerFile());
            /** @var array{packages?:array<int,array{name:string,version:string,source?:array{url?:string}}>} $lockFileContent */
            $lockFileContent = \json_decode(\file_get_contents($lockFile) ?: 'null', \true);

            if (!isset($lockFileContent['packages'])) {
                throw new InvalidSpecificationException('composer.lock error', 5);
            }

            self::$packages = $lockFileContent['packages'];
        }

        $tag = $branch = \null;
        $links = $composer->getPackage()
            ->getRequires();
        $require = $links[$vendorName] ?? null;

        if ($require) {
            $prefix = (string) \preg_replace(',^[^/]+/,', '', $vendorName);
            $search = \array_filter(self::$packages, fn($package) => $vendorName === $package['name']);
            $sourceUrl = '';
            if (\count($search) > 0) {
                $package = \array_shift($search);
                $sourceUrl = $package['source']['url'] ?? '';
            }
            $json = [
                'path' => SpipPaths::EXTENSIONS . '/' . $prefix,
                'source' => $sourceUrl,
            ];
            $constraint = $require->getPrettyConstraint();
            if (\preg_match(',^[\^]?(?<branch>\d+(\.\d+)?)\.x-dev$,', $constraint, $matches)) {
                $branch = $matches['branch'];
            } elseif (\preg_match(',^[\^]?[v|V]?(?<branch>\d+(\.\d+)?),', $constraint, $matches)) {
                $branch = $matches['branch'];
                $tag = $package['version'] ?? '';
            }
            if ($branch) {
                $json['branch'] = $branch;
            }
            if ($tag) {
                $json['tag'] = $tag;
            }

            return new self($prefix, $json);
        }

        throw new InvalidSpecificationException('Package "' . $vendorName . '" is not present.', 4);
    }

    public function setChanger(RemoteUrlsInterface $changer): self
    {
        $this->changer = $changer;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Détermine le `vendor/name` équivalent.
     *
     * Par convention, celui-ci est égal au nom du  dépôt git.
     * -> `git@url-git-server:vendor/name.git` ou `https://url-git-server/vendor/name.git`
     *
     * Limitation: l'extension DOIT être composerisée et être
     * distribuée dans un dépôt composer accessible.
     */
    public function computeVendorName(): string
    {
        if ($this->changer === null) {
            return '';
        }

        $source = $this->changer->toHttps($this->source);
        $p = parse_url($source);
        $vendorName = $p['path'] ?? '';

        return (string) \preg_replace([',^/+,', ',\.git$,'], ['', ''], $vendorName);
    }

    /**
     * Détermine la `constraint` équivalente.
     *
     * Le `tag` est facultatif, mais prévaut sur la `branch`.
     * Il indique l'existence d'un niveau de stabilité permettant
     * une `constraint` de type `^M.m`
     *
     * La `branch` est facultative.
     * Elle indique une version de `dev` permettant
     * une `constraint` de type `^M.m.x-dev` ou `^M.x-dev`
     */
    public function computeConstraint(): string
    {
        if ($this->tag) {
            return '^' . \preg_replace(',^[v|V]?(\d+\.\d+).*$,', '$1', $this->tag);
        }

        if ($this->branch) {
            return '^' . $this->branch . '.x-dev';
        }

        return '';
    }

    public function jsonSerialize(): mixed
    {
        $json = [
            'path' => $this->getPath(),
            'source' => $this->getSource(),
        ];
        if (!empty($this->branch)) {
            $json['branch']  = $this->branch;
        }
        if (!empty($this->tag)) {
            $json['tag']  = $this->tag;
        }

        return $json;
    }
}
