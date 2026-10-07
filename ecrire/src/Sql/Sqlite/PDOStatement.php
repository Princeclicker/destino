<?php

namespace Spip\Sql\Sqlite;

if (!defined('\_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Pouvoir retrouver le PDO utilisé pour générer un résultat de requête.
 */
final class PDOStatement extends \PDOStatement
{
	private \PDO $PDO;

	private function __construct(\PDO &$PDO) {
		$this->PDO = $PDO;
	}

	public function getPDO(): \PDO {
		return $this->PDO;
	}
}
