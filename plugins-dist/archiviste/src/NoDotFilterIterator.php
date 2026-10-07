<?php

namespace Spip\Archiver;

/**
 * @extends \FilterIterator<int,\SplFileInfo,\Iterator<int,\SplFileInfo>>
 */
class NoDotFilterIterator extends \FilterIterator
{
	public function accept(): bool {
		return !in_array($this->current()->getFilename(), ['.', '..']);
	}
}
