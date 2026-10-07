<?php

/**
 * SPIP, Système de publication pour l'internet
 *
 * Copyright © avec tendresse depuis 2001
 * Arnaud Martin, Antoine Pitrou, Philippe Rivière, Emmanuel Saint-James
 *
 * Ce programme est un logiciel libre distribué sous licence GNU/GPL.
 */

use Spip\Afficher\Minipage\Admin as MinipageAdmin;

function exec_base_delete_stats_dist() {
	include_spip('inc/autoriser');
	if (!autoriser('detruire', '_statistiques')) {
		$minipage = new MinipageAdmin();
		echo $minipage->page('');
		exit;
	} else {
		include_spip('inc/headers');
		$admin = charger_fonction('admin', 'inc');
		$res = $admin('delete_stats', _T('statistiques:bouton_effacer_statistiques'), '');
		if ($res) {
			echo $res;
		} else {
			redirige_url_ecrire('stats_visites', '');
		}
	}
}
