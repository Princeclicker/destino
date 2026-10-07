<?php

/**
 * Fonctions utiles pour la wheel echappe-js
 *
 * @SPIP\Textwheel\Wheel\SPIP\Fonctions
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function echappe_anti_xss($match) {
	if (!is_array($match) || !strlen((string) $match[0])) {
		return '';
	}
	$texte = (string) $match[0];
	unset($match);
	$tag = null;
	if (preg_match(',^<(\w+)\b,', $texte, $match)) {
		$tag = $match[1];
		unset($match);
	}

	$texte_clair = $texte;
	if (str_contains($texte_clair, '&')) {
		$texte_clair = html_entity_decode($texte, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
	}

	// on echappe les urls data: javascript: et tout ce qui ressemble
	if (
		str_contains($texte_clair, ':')
		&& preg_match(',(data|script)\s*:,iS', $texte_clair)
	) {
		if (!function_exists('afficher_html_suspect')) {
			include_spip('inc/texte_mini');
		}
		return afficher_html_suspect($texte);
	}

	// on echappe si on a possiblement un attribut onxxx/formaction et que ca passe pas dans safehtml
	if (
		(
			stripos($texte, 'on') !== false
			&& preg_match(",<\w+.*\bon\w+\s*=,UimsS", $texte)
		)
		|| (
			stripos($texte, 'formaction') !== false
			&& preg_match(",<\w+.*\bformaction\s*=,UimsS", $texte)
		)
	) {
		if (!function_exists('is_html_safe')) {
			include_spip('inc/texte_mini');
		}
		if (!is_html_safe($texte, ['ignore_echappe_js' => true]) && (!$tag || !is_html_safe($texte . "</$tag>", ['ignore_echappe_js' => true]))) {
			return afficher_html_suspect($texte);
		}
	}

	return $texte;
}
