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

/**
 * Gère un cas d'upload trop gros
 *
 * Fichier obsolète, à supprimer.
 * Mais fonction utilisée encore dans medias_detecter_fond_par_defaut()
 *
 * @package SPIP\Medias\Upload
 */

#
# Fichier obsolete, a supprimer
#

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

// inclure les fonctions bases du core
include_once _DIR_RESTREINT . 'inc/documents.php';

/**
 * Traite l'erreur d'un upload trop gros
 *
 * L'erreur est appelée depuis public.php et medias_detecter_fond_par_defaut
 * et affiche un minipres avec la taille limite de documents possibles
 */
function erreur_upload_trop_gros(): never {
	include_spip('inc/filtres');

	$msg = '<p>'
		. taille_en_octets($_SERVER['CONTENT_LENGTH'])
		. '<br />'
		. _T(
			'medias:upload_limit',
			['max' => ini_get('upload_max_filesize')]
		)
		. '</p>';

	$minipage = new MinipageAdmin();
	echo $minipage->page('<div class="upload_answer upload_error">' . $msg . '</div>', ['titre' => _T('pass_erreur')]);
	exit;
}
