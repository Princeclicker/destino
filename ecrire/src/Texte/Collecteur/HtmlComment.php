<?php

/**
 * SPIP, Système de publication pour l'internet
 *
 * Copyright © avec tendresse depuis 2001
 * Arnaud Martin, Antoine Pitrou, Philippe Rivière, Emmanuel Saint-James
 *
 * Ce programme est un logiciel libre distribué sous licence GNU/GPL.
 */

namespace Spip\Texte\Collecteur;

if (!defined('\_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Extrait les commentaires HTML d'un contenu html
 */
class HtmlComment extends AbstractCollecteur
{
	protected static string $markPrefix = 'HTMLCOMMENT';

	/**
	 * La preg pour découper et collecter les commentaires
	 */
	protected string $preg_comment;

	public function __construct(?string $preg = null) {

		$this->preg_comment = ($preg ?: '@<!--(.*?)-->@isS');
	}

	/**
	 * @param array{detecter_presence?: bool} $options
	 */
	public function collecter(string $texte, array $options = []): array {
		if (!$texte) {
			return [];
		}

		// collecter les matchs de la preg
		$comments = $this->collecteur($texte, '!', '<!--', $this->preg_comment, empty($options['detecter_presence']) ? 0 : 1);

		// si on veut seulement detecter la présence, on peut retourner tel quel
		if (empty($options['detecter_presence'])) {

			foreach ($comments as $k => &$comment) {
				$comment['innerHtml'] = $comment['match'][1];
			}
		}

		return $comments;
	}
}
