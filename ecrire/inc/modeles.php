<?php

/**
 * SPIP, Système de publication pour l'internet
 *
 * Copyright © avec tendresse depuis 2001
 * Arnaud Martin, Antoine Pitrou, Philippe Rivière, Emmanuel Saint-James
 *
 * Ce programme est un logiciel libre distribué sous licence GNU/GPL.
 */
if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Traiter les modeles d'un texte
 * @param string $texte
 * @param bool|array $doublons
 * @param string $echap
 * @param array $env
 * @return string
 */
function traiter_modeles($texte, $doublons = false, $echap = '', string $connect = '', ?Spip\Texte\Collecteur\Liens $collecteurLiens = null, $env = []) {

	include_spip('src/Texte/Collecteur/AbstractCollecteur');
	include_spip('src/Texte/Collecteur/Modeles');
	$collecteurModeles = new Spip\Texte\Collecteur\Modeles();

	$options = [
		'doublons' => $doublons,
		'echap' => $echap,
		'connect' => $connect,
		'collecteurLiens' => $collecteurLiens,
		'env' => $env,
	];
	return $collecteurModeles->traiter($texte ?? '', $options);
}

/**
 * Modèles toujours neutralisés dans un texte public, même s'ils figurent dans l'allowlist.
 *
 * @return string[]
 */
function modeles_publics_interdits(): array {
	return ['formulaire'];
}

/**
 * Liste des modèles autorisés dans un texte soumis ou prévisualisé (contributions publiques, formulaires CVT).
 *
 * Par défaut vide (aucun modèle). Extension via la constante `_MODELES_PUBLICS_AUTORISES`
 * (liste plate ou indexée par contexte) et/ou le pipeline `modeles_publics_autorises`.
 *
 * @param string $contexte Ex. 'forum', nom de formulaire CVT, plugin…
 * @param array{modeles_autorises?: string[]} $options Allowlist explicite (court-circuite constante et pipeline)
 * @return string[]
 */
function modeles_publics_autorises(string $contexte = '', array $options = []): array {
	if (isset($options['modeles_autorises']) && is_array($options['modeles_autorises'])) {
		$modeles = $options['modeles_autorises'];
	} else {
		$modeles = [];
		if (defined('_MODELES_PUBLICS_AUTORISES') && is_array(_MODELES_PUBLICS_AUTORISES)) {
			if ($contexte !== '' && isset(_MODELES_PUBLICS_AUTORISES[$contexte]) && is_array(_MODELES_PUBLICS_AUTORISES[$contexte])) {
				$modeles = _MODELES_PUBLICS_AUTORISES[$contexte];
			} elseif (array_is_list(_MODELES_PUBLICS_AUTORISES)) {
				$modeles = _MODELES_PUBLICS_AUTORISES;
			}
		}
	}

	$data = pipeline('modeles_publics_autorises', [
		'contexte' => $contexte,
		'modeles' => $modeles,
	]);

	$modeles = is_array($data['modeles'] ?? null) ? $data['modeles'] : [];

	return array_values(array_unique(array_map('strtolower', $modeles)));
}

/**
 * Neutralise les modèles non autorisés dans un texte public.
 *
 * @param array{modeles_autorises?: string[], remplacer?: string} $options
 */
function neutraliser_modeles_publics(string $texte, string $contexte = '', array $options = []): string {
	include_spip('src/Texte/Collecteur/Modeles');

	$remplacer = $options['remplacer'] ?? '&lt;';

	if (strlen($texte) > 8000) {
		$texte = str_replace('<', $remplacer, $texte);
		return $texte;
	}

	// pour les modèles interdits on ne laisse rien passer qui ressemble
	$interdits = modeles_publics_interdits();
	foreach ($interdits as $interdit) {
		$texte = str_ireplace("<{$interdit}", $remplacer . $interdit, $texte);
	}

	$autorises = array_flip(array_diff(
		modeles_publics_autorises($contexte, $options),
		$interdits
	));

	$collecteurModeles = new Spip\Texte\Collecteur\Modeles();

	return $collecteurModeles->remplacer(
		$texte,
		function (string $found, array $occurrence) use ($autorises, $remplacer): ?string {
			$type = strtolower($occurrence['type'] ?? '');
			if (isset($autorises[$type])) {
				return null;
			}
			return str_replace('<', $remplacer, $found);
		}
	);
}
