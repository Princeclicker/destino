<?php

namespace Spip\Compilateur\Iterateur;

if (!defined('\_ECRIRE_INC_VERSION')) {
	return;
}

abstract class AbstractIterateur
{
	protected string $type;

	/**
	 * Calcul du total des elements
	 *
	 * @var int|null
	 */
	public $total;

	/**
	 * Erreur presente ? *
	 */
	public bool $err = false;

	protected array $command = [];

	protected array $info = [];

	public function __construct(array $command, array $info = []) {
		$this->command = $command;
		$this->info = $info;
	}
}
