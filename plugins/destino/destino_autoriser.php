<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Pipeline autoriser du plugin Destino.
 */
function destino_autoriser() {
}

/**
 * Autoriser la consultation des messages reçus (espace privé).
 *
 * @param string $faire
 * @param string $type
 * @param int|string $id
 * @param array $qui
 * @param array $opt
 * @return bool
 */
function autoriser_destino_messages_dist($faire, $type = '', $id = null, $qui = null, $opt = null) {
	$qui = is_array($qui) ? $qui : [];
	$statut = $qui['statut'] ?? '';
	return $statut === '0minirezo' || ($qui['webmestre'] ?? 'non') === 'oui';
}
