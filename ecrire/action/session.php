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
 * Gestion d'une action ajoutant une variable dans une session SPIP
 */
if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Action pour poser une variable de session SPIP
 *
 * Poster sur cette action en indiquant les clés `var` et `val`
 *
 * Utilisé par exemple par le script javascript 'autosave' pour sauvegarder
 * les formulaires en cours d'édition
 *
 * @todo
 *   Envoyer en réponse : json contenant toutes les variables publiques de la session
 */
function action_session_dist() {
	if (
		($cle_autosave = _request('var'))
		&& preg_match(',^[a-z_0-9-]+$,i', $cle_autosave)
	) {
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

			$val = _request('val');
			parse_str($val, $vars);
			$vars = array_filter($vars, fn ($k) => !str_starts_with($k, '_'), ARRAY_FILTER_USE_KEY);
			$vars = json_encode($vars);
			include_spip('inc/session');
			session_set('session_' . $cle_autosave, $vars);
			# spip_log("autosave:$cle_autosave:$vars",'autosave' . _LOG_DEBUG);
		}
	}

	# TODO: mode lecture de session ; n'afficher que ce qu'il faut
	# echo json_encode($GLOBALS['visiteur_session']);
}
