<?php

/**
 * Gestion du formulaire de téléchargement de plugin via une URL
 *
 * @plugin SVP pour SPIP
 * @license GPL
 * @package SPIP\SVP\Formulaires
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

include_spip('inc/plugin');

/**
 * Crée une valeur d'action pour l'attribut 'name' d'une saisie de formulaire
 *
 * @example
 *     [(#ID_PAQUET|svp_nom_action{on})]
 *     écrit : actions[on][24]
 * @param int $id_paquet
 *     Identifiant du paquet
 * @param string $action
 *     Une action possible (on, off, stop, up, on, upon, kill)
 */
function filtre_svp_nom_action($id_paquet, $action) {
	return "actions[$action][$id_paquet]";
}

function svp_tester_compatibilite(
	string $prefixe,
	string $compatibilite_spip,
	?string $dependances_serialisees
): string {
	static $core_procure;

	include_spip('inc/svp_rechercher');
	$compatible = ' ';
	if (!svp_verifier_compatibilite_spip($compatibilite_spip)) {
		if (defined('_DEV_VERSION_SPIP_COMPAT')
			&& svp_verifier_compatibilite_spip($compatibilite_spip, _DEV_VERSION_SPIP_COMPAT)) {
			$compatible = 'force';
		} else {
			return '';
		}
	}

	if (empty($dependances_serialisees)) {
		return $compatible;
	}

	// Vérifier la compatibilité PHP ou modules PHP:
	$dependances = unserialize($dependances_serialisees);
	if (!empty($dependances['necessite'])) {
		if ($core_procure === null) {
			include_spip('inc/svp_decider');
			$decideur = new Decideur();
			$core_procure = $decideur->liste_plugins_procure();
		}
		// verifier les dependances à PHP ou modules PHP
		foreach ($dependances['necessite'] as $necessites) {
			$necessites = array_filter(
				$necessites,
				fn ($k) => $k === 'php' || str_starts_with($k, 'php:'),
				ARRAY_FILTER_USE_KEY
			);
			if (!empty($necessites)) {
				foreach ($necessites as $quoi => $details) {
					$quoi = strtoupper($quoi);
					if (empty($core_procure[$quoi])
						|| (
							isset($details['compatibilite'])
							&& !plugin_version_compatible($details['compatibilite'], $core_procure[$quoi])
						)
					) {
						return '';
					}
				}
			}
		}
	}

	return $compatible;
}
