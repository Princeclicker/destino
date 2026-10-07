<?php

/**
 *  SPIP, Système de publication pour l'internet
 *
 *  Copyright (c) avec tendresse depuis 2001
 *  Arnaud Martin, Antoine Pitrou, Philippe Rivière, Emmanuel Saint-James
 *
 *  Ce programme est un logiciel libre distribué sous licence GNU/GPL.
 */

/**
 * Définit les fonctions utiles du plugin forum
 *
 * @package SPIP\Forum\Fonctions
 */

use Spip\Texte\Collecteur\HtmlTag as CollecteurHtmlTag;

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

include_spip('public/forum');

/**
 * Un filtre appliqué à `#PARAMETRES_FORUM`, qui donne l'adresse de la page
 * de réponse
 *
 * @example
 *     ```
 *     [<p class="repondre">
 *          <a href="(#PARAMETRES_FORUM|url_reponse_forum)">
 *          <:repondre_article:>
 *          </a>
 *      </p>]
 *      ```
 *
 * @filtre
 * @see balise_PARAMETRES_FORUM_dist()
 *
 * @param string $parametres
 * @return string URL de la page de réponse
 */
function filtre_url_reponse_forum($parametres) {
	if (!$parametres) {
		return '';
	}

	return generer_url_public('forum', $parametres);
}

/**
 * Un filtre qui, étant donné un `#PARAMETRES_FORUM`, retourne une URL de suivi rss
 * dudit forum
 *
 * Attention : appliqué à un `#PARAMETRES_FORUM` complexe (`id_article=x&id_forum=y`)
 * ça retourne une URL de suivi du thread `y` (que le thread existe ou non)
 *
 * @filtre
 * @see balise_PARAMETRES_FORUM_dist()
 *
 * @param string $param
 * @return string URL pour le suivi RSS
 */
function filtre_url_rss_forum($param) {
	if (!preg_match(',.*(id_(\w*?))=([0-9]+),S', $param, $regs)) {
		return '';
	}
	[, $k, $t, $v] = $regs;
	if ($t == 'forum') {
		$k = 'id_' . ($t = 'thread');
	}

	return generer_url_public("rss_forum_$t", [$k => $v]);
}

/**
 * Neutralise les modèles non autorisés dans un texte de forum.
 *
 * @deprecated 4.4 Utiliser neutraliser_modeles_publics($texte, 'forum')
 */
function forum_desactiver_modeles(string $texte): string {
	trigger_deprecation('spip/forum', '4.4', 'Using "%s" is deprecated, use "%s" instead', __FUNCTION__, 'neutraliser_modeles_publics');

	return neutraliser_modeles_publics($texte, 'forum');
}

/**
 * Empêche l'exécution de code HTML
 *
 * Permet si la constante `_INTERDIRE_TEXTE_HTML`  est définie
 * (ce n'est pas le cas par défaut) d'échapper les balises HTML
 * d'un texte (de sorte qu'elles seront affichées et non traitées par
 * le navigateur).
 *
 * @see forum_declarer_tables_interfaces()
 *
 * @param string $texte
 */
function interdit_html($texte): string {
	if (!$texte) {
		return $texte;
	}

	// si html interdit, tous les < sont neutralisés, pas la peine de faire de la dentelle avec les modèles
	if (defined('_INTERDIRE_TEXTE_HTML')) {
		$texte = str_replace('<', '&lt;', $texte);
	} else {
		// enlever tout ZERO WIDTH SPACE qui aurait été dans l'entrée initiale
		if (str_contains($texte, "\u{200b}")) {
			$texte = str_replace("\u{200b}", '', $texte);
		}
		include_spip('inc/modeles');
		// on insère un U+200B ZERO WIDTH SPACE derrière le < pour désactiver la détection des modèles
		$texte = neutraliser_modeles_publics($texte ??= '', 'forum', ['remplacer' => "<\u{200b}"]);
	}

	return $texte;
}

function interdit_html_nettoyer($texte): string {
	if ($texte && str_contains($texte, "\u{200b}")) {
		$texte = str_replace(["<\u{200b}", "\u{200b}"], ['&lt;', ''], $texte);
	}
	return $texte;
}

/**
 * Nettoyer les balises <form> et leurs input hidden dans la previsu
 */
function forum_previsu_nettoyer_forms(string $texte): string {
	if (!$texte) {
		return $texte;
	}
	// si l'interprétation du message a produit un formulaire, on nettoie les balises <form>
	// et les balises <input type='hidden'>
	include_spip('inc/filtres');
	$collecteurForm = new CollecteurHtmlTag('form');
	$texte = $collecteurForm->remplacer($texte, function ($form, $bloc) {
		$opening = '<div' . substr($bloc['opening'], 5);
		$opening = vider_attribut($opening, 'action');
		$opening = vider_attribut($opening, 'method');
		$inner = $bloc['innerHtml'];
		if (str_contains($inner, 'hidden')) {
			$collecteurInput = new CollecteurHtmlTag('input');
			$inner = $collecteurInput->remplacer($inner, function ($input) {
				if (extraire_attribut($input, 'type') === 'hidden') {
					return '';
				}
				return null;
			});
		}
		return $opening . $inner . '</div>';
	});

	return $texte;
}
