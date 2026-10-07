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

function liste_extensions_utilisees($media) {
	return $media ? array_column(
		sql_allfetsel(
			'extension',
			'spip_documents',
			'media=' . sql_quote($media)
		),
		'extension'
	) : [];
}

function liste_medias_disponibles() {
	return array_column(
		sql_allfetsel(
			'DISTINCT media_defaut',
			'spip_types_documents',
			'',
			'',
			'CASE media_defaut
				WHEN ' . sql_quote('image') . 'THEN 1
				WHEN ' . sql_quote('audio') . 'THEN 2
				WHEN ' . sql_quote('video') . 'THEN 3
				WHEN ' . sql_quote('file') . 'THEN 9
				ELSE 4
			 END ASC'
		) ?? [],
		'media_defaut'
	);
}

function medias_verifier_galerie($fond, $sinon) {
	if (empty($fond)) {
		return $sinon;
	}
	$base = basename($fond);
	if (!preg_match(",^mediatheque-[\w-]+$,", $base)) {
		return $sinon;
	}
	return dirname($sinon) . '/' . $base;
}
