<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Les champs globaux gérés par le formulaire de réglages Destino.
 * Chaque champ correspond à une meta spip_meta du même nom.
 *
 * @return array<string>
 */
function destino_champs() {
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

/**
 * Raccourcis Destino sur la page d'accueil de l'espace privé.
 *
 * @pipeline affiche_milieu
 * @param array $flux
 * @return array
 */
function destino_affiche_milieu($flux) {
	if (($flux['args']['exec'] ?? '') === 'accueil') {
		$flux['data'] .= recuperer_fond('prive/squelettes/inclure/destino_accueil', []);
	}

	return $flux;
}
