<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Déclaration de la table spip_destino_messages (messages du formulaire contact).
 *
 * @pipeline declarer_tables_auxiliaires
 * @param array $tables_auxiliaires
 * @return array
 */
function destino_declarer_tables_auxiliaires($tables_auxiliaires) {
	$spip_destino_messages = [
		'id_message' => 'bigint(20) NOT NULL auto_increment',
		'date_message' => "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL",
		'nom' => "varchar(100) DEFAULT '' NOT NULL",
		'email' => "varchar(190) DEFAULT '' NOT NULL",
		'telephone' => "varchar(50) DEFAULT '' NOT NULL",
		'message' => 'text DEFAULT NULL',
	];

	$spip_destino_messages_key = [
		'PRIMARY KEY' => 'id_message',
	];

	$tables_auxiliaires['spip_destino_messages'] = [
		'field' => &$spip_destino_messages,
		'key' => &$spip_destino_messages_key,
	];

	return $tables_auxiliaires;
}
