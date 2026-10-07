<?php

namespace SpipLeague\Composer\Extensions;

/**
 * Spécifiations d'installation d'un "plugins-dist" en SPIP4.4.
 *
 * @since 0.7.0
 */
interface SpecificationInterface extends \JsonSerializable
{
    public function getPrefix(): string;

    public function getPath(): string;

    /**
     * Détermine le `vendor/name` équivalent.
     */
    public function computeVendorName(): string;

    /**
     * Détermine la `constraint` équivalente.
     */
    public function computeConstraint(): string;
}
