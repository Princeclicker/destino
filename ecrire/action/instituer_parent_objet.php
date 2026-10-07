<?php

/**
 * SPIP, Système de publication pour l'internet
 *
 * Copyright © avec tendresse depuis 2001
 * Arnaud Martin, Antoine Pitrou, Philippe Rivière, Emmanuel Saint-James
 *
 * Ce programme est un logiciel libre distribué sous licence GNU/GPL.
 */

/**
 * Action pour instituer un objet avec les puces rapides
 */
if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Instituer le id_parent d'un objet
 *
 * @param null|string $arg
 *     Chaîne "objet/id". En absence utilise l'argument
 *     de l'action sécurisée.
 */
function action_instituer_parent_objet_dist($arg = null) {

	if ($arg === null) {
		$securiser_action = charger_fonction('securiser_action', 'inc');
		$arg = $securiser_action();
	}

	[$objet, $id_objet] = preg_split('/\W/', $arg);
	$id_parent = _request('id_parent');

	if ($id_parent === null) {
		return;
	}

	if (
		$id_objet = intval($id_objet)
		and autoriser('instituer', $objet, $id_objet)
	) {
		include_spip('action/editer_objet');
		objet_modifier($objet, $id_objet, ['id_parent' => $id_parent]);
	}
}
