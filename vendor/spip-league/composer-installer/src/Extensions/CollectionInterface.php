<?php

namespace SpipLeague\Composer\Extensions;

/**
 * Collection des spécifiations d'installation des "plugins-dist" en SPIP4.4.
 *
 * @extends \ArrayAccess<string,SpecificationInterface>
 * @extends \Iterator<string,SpecificationInterface>
 *
 * @since 0.7.0
 */
interface CollectionInterface extends \Countable, \Iterator, \JsonSerializable, \ArrayAccess
{
    public function getFile(): ?string;
}
