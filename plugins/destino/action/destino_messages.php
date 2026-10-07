<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Action de suppression d'un message reçu via le formulaire de contact.
 * URL signée : #URL_ACTION_AUTEUR{destino_messages,#ID_MESSAGE}
 */
function action_destino_messages_dist() {
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();

	if (!autoriser('destino_messages')) {
		include_spip('inc/minipres');
		minipres();
		exit;
	}

	$id = intval($arg);
	if ($id > 0) {
		sql_delete('spip_destino_messages', 'id_message=' . $id);
	}

	redirige_url_ecrire('destino_messages');
}