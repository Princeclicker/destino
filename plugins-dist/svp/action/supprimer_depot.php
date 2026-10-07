<?php

/**
 * Gestion de l'action supprimer_depot
 *
 * @plugin SVP pour SPIP
 * @license GPL
 */

use Spip\Afficher\Minipage\Admin as MinipageAdmin;

/**
 * Action de suppression en base de données d'un dépot et de ses plugins
 *
 * @uses  svp_supprimer_depot()
 */
function action_supprimer_depot_dist() {

	// Securisation: aucun argument attendu
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();

	// Verification des autorisations
	if (!autoriser('webmestre')) {
		$minipage = new MinipageAdmin();
		echo $minipage->page('');
		exit;
	}

	// Suppression du depot et de ses plugins
	if ($id_depot = intval($arg)) {
		include_spip('inc/svp_depoter_distant');
		svp_supprimer_depot($id_depot);
		spip_log('ACTION SUPPRIMER DEPOT (manuel) : id_depot = ' . $id_depot, 'svp_actions.' . _LOG_INFO);
	}
}
