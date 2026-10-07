<?php

/**
 * L'objectif de ce formulaire de test est de vérifier
 * le fonctionnement de la drop-zone-extended de bgup
 *
 * @package SPIP\Bigup\Formulaires
 */

function formulaires_tester_bigup_extended_charger_dist($id = 0, $target = '', $bloc = '') {
	include_spip('inc/autoriser');
	if (!autoriser('configurer', '_bigup')) {
		return false;
	}

	$valeurs = [
		'_id' => 'form_tester_bigup_extended_' . $id,
		'titre' => '',
		'drop_zone_extended' => '',
	];
	if ($target === 'form') {
		$valeurs['drop_zone_extended'] = '#' . $valeurs['_id'];
	} elseif ($target === 'bloc') {
		$valeurs['drop_zone_extended'] = $bloc;
	}

	// demander la gestion de fichiers d'upload
	$valeurs['_bigup_rechercher_fichiers'] = true;

	spip_log('> charger tester_bigup_extended', 'bigup');

	return $valeurs;
}

function formulaires_tester_bigup_extended_verifier_dist($id = 0, $target = '', $bloc = '') {
	$erreurs = [];

	spip_log('> verifier tester_bigup_extended', 'bigup');

	// ceux là sont obligatoires
	foreach (['titre'] as $obli) {
		if (!_request($obli)) {
			$erreurs[$obli] = _T('info_obligatoire');
		}
	}

	return $erreurs;
}

function formulaires_tester_bigup_extended_traiter_dist($id = 0, $target = '', $bloc = '') {
	spip_log('> traiter tester_bigup_extended', 'bigup');

	$retours = [
		'message_ok' => 'Formulaire pris en compte',
		'editable' => true,
	];

	return $retours;
}
