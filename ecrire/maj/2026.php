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
 * Gestion des mises à jour de SPIP, version >= 2021000000
 *
 * Gestion des mises à jour du cœur de SPIP par un tableau global `maj`
 * indexé par la date du changement YYYYMMDDXX
 */
if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

$GLOBALS['maj'][2026_08_03_00] = [
	['sql_alter', "TABLE spip_jobs ADD signature CHAR(64) NOT NULL DEFAULT ''"],
	['maj2026_generer_signature_jobs'],
];

/**
 * Génère la signature des jobs
 */
function maj2026_generer_signature_jobs() {
	include_spip('inc/queue');
	do {
		$jobs = sql_allfetsel('*', 'spip_jobs', 'signature = ""', '', '', '0,1000');
		foreach ($jobs as $job) {
			$signature = queue_sign_job($job);
			sql_updateq('spip_jobs', ['signature' => $signature], 'id_job = ' . $job['id_job']);
			if (time() >= _TIME_OUT) {
				return;
			}
		}
	} while (!empty($jobs));
}
