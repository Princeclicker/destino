<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Les champs globaux gérés par le formulaire de réglages Destino.
 * Chaque nom correspond à une meta spip_meta du même nom.
 *
 * @return array<string>
 */
function destino_form_champs() {
	return [
		'destino_adresse',
		'destino_email',
		'destino_telephone',
		'destino_cta_titre',
		'destino_footer_about',
		'destino_youtube',
		'destino_copyright',
		'destino_carte',
	];
}

function formulaires_configurer_destino_charger_dist() {
	include_spip('inc/config');
	if (!isset($GLOBALS['meta']) || empty($GLOBALS['meta'])) {
		lire_metas();
	}
	$contexte = ['editable' => true];
	foreach (destino_form_champs() as $champ) {
		$contexte[$champ] = $GLOBALS['meta'][$champ] ?? '';
	}

	return $contexte;
}

function formulaires_configurer_destino_verifier_dist() {
	$erreurs = [];
	$email = _request('destino_email');
	if ($email && !email_valide($email)) {
		$erreurs['destino_email'] = _T('form_petit_probleme_email');
	}

	return $erreurs;
}

function formulaires_configurer_destino_traiter_dist() {
	include_spip('inc/config');
	foreach (destino_form_champs() as $champ) {
		ecrire_meta($champ, trim((string) _request($champ)));
	}

	return ['message_ok' => _T('config_info_enregistree')];
}