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
 * Extrait les blocs <?php ... ?> et <?= ?> d'une page html
 */
class HtmlPhp extends AbstractCollecteur
{
	protected static string $markPrefix = 'HTMLPHP';

	public function __construct() {
	}

	/**
	 * Un detecteur optimisé plus rapide en évitant un appel à PhpToken::tokenize
	 * @param string $texte
	 */
	public function detecter($texte): bool {
		if (!is_string($texte) || !$texte) {
			return false;
		}
		if (!empty($this->markId) && str_contains((string) $texte, $this->markId)) {
			return true;
		}

		// pour des raisons de perf on eviter de lancer \PhpToken::tokenize sur tout le texte

		if (!str_contains($texte, '<?')) {
			return false;
		}

		if (str_contains($texte, '<?xml')) {
			if (!str_contains(str_replace('<?xml', '', $texte), '<?')) {
				return false;
			}
		}

		$tokens = token_get_all($texte);
		return array_any($tokens, fn ($token) => is_array($token) && ($token[0] === \T_OPEN_TAG || $token[0] === \T_OPEN_TAG_WITH_ECHO));
	}

	/**
	 * @param array $options
	 *   bool $detecter_presence
	 *   bool $nb_max
	 */
	public function collecter(string $texte, array $options = []): array {
		if (!$texte || !str_contains($texte, '<?')) {
			return [];
		}

		// Transformer le code en suite d'éléments lexicaux avec l'analyseur
		// de PHP, puis y rechercher les tags PHP ouvrant puis fermant
		$tokens = \PhpToken::tokenize($texte);

		$blocs = [];
		$state = 0;
		$current_bloc = null;
		foreach ($tokens as $token) {
			switch ($state) {
				case 0:
					if ($token->is([\T_OPEN_TAG, \T_OPEN_TAG_WITH_ECHO])) {
						$current_bloc = [
							'pos' => $token->pos,
							'length' => \strlen($token->text),
							'opening' => $token->text,
							'raw' => $token->text,
							'innerHtml' => '',
							'closing' => '',
						];
						$state = 1;
					}
					break;
				case 1:
					if (!empty($current_bloc)) {
						$current_bloc['raw'] .= $token->text;
						$current_bloc['length'] += \strlen($token->text);
						if ($token->is(\T_CLOSE_TAG)) {
							$current_bloc['closing'] = $token->text;
							$blocs[] = $current_bloc;
							if (!empty($options['detecter_presence'])) {
								return $blocs;
							}
							$current_bloc = null;
							$state = 0;
						} else {
							$current_bloc['innerHtml'] .= $token->text;
						}
					}
					break;
			}
		}
		// fichier sans fermeture du dernier tag PHP
		if ($state === 1 && !empty($current_bloc)) {
			$current_bloc['closing'] = '';
			$blocs[] = $current_bloc;
		}

		return $blocs;
	}
}
