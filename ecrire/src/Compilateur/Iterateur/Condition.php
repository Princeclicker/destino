<?php

namespace Spip\Compilateur\Iterateur;

if (!defined('\_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Iterateur CONDITION pour itérer sur des données.
 *
 * La boucle condition n'a toujours qu'un seul élément.
 */
class Condition extends Data
{
	/**
	 * Obtenir les données de la boucle CONDITION.
	 *
	 * @param array $command
	 */
	protected function select($command) {
		$this->tableau = [0 => 1];
	}
}
