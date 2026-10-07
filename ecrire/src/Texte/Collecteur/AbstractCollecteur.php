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

abstract class AbstractCollecteur
{
	protected static string $markPrefix = 'COLLECT';

	protected string $markId;

	public function collecter(string $texte, array $options = []): array {
		return [];
	}

	public function detecter($texte): bool {
		if (!empty($this->markId) && str_contains((string) $texte, $this->markId)) {
			return true;
		}
		return !empty($this->collecter($texte, ['detecter_presence' => true]));
	}

	/**
	 * Remplacer les occurrences de la collecte par un contenu donné en callback
	 *
	 * Chaque occurrence est passée en callback avec son contenu brut et les informations de la collecte
	 * la callback doit retourner le contenu à remplacer pour l'occurrence.
	 */
	public function remplacer(string $texte, callable $callback, array $options = []): string {
		$collection = $this->collecter($texte, $options);
		return $this->remplacer_collection($texte, $collection, $callback);
	}

	/**
	 * Echapper les occurences de la collecte par un texte neutre du point de vue HTML
	 *
	 * @see retablir()
	 *
	 * @param array{sanitize_callback?:string} $options
	 *
	 * @return string texte, marqueur utilise pour echapper les modeles
	 */
	public function echapper(string $texte, array $options = []): string {
		if (!function_exists('creer_uniqid')) {
			include_spip('inc/acces');
		}

		$collection = $this->collecter($texte, $options);
		if (!empty($options['sanitize_callback']) && is_callable($options['sanitize_callback'])) {
			$collection = $this->sanitizer_collection($collection, $options['sanitize_callback']);
		}

		if ($collection !== []) {
			if (empty($this->markId)) {
				// generer un marqueur qui n'existe pas dans le texte
				do {
					$this->markId = substr(md5(uniqid(static::class, 1)), 0, 7);
					$this->markId = '@|' . static::$markPrefix . $this->markId . '|';
				} while (str_contains($texte, $this->markId));
			}

			$callback = fn ($raw) => $this->markId . base64_encode((string) $raw) . '|@';
			$texte = $this->remplacer_collection($texte, $collection, $callback);
		}

		return $texte;
	}

	/**
	 * Retablir les occurences échappées précédemment
	 *
	 * @see echapper()
	 */
	public function retablir(string $texte): string {

		if (!empty($this->markId)) {
			$lm = strlen($this->markId);
			$pos = 0;
			while (
				($p = strpos($texte, $this->markId, $pos)) !== false
				&& ($end = strpos($texte, '|@', $p + $lm))
			) {
				$base64 = substr($texte, $p + $lm, $end - ($p + $lm));
				if ($c = base64_decode($base64, true)) {
					$texte = substr_replace($texte, $c, $p, $end + 2 - $p);
					$pos = $p + strlen($c);
				} else {
					$pos = $end;
				}
			}
		}

		return $texte;
	}

	/**
	 * Collecteur générique des occurences d'une preg dans un texte avec leurs positions et longueur
	 * @param string $texte
	 *   texte à analyser pour la collecte
	 * @param string $if_chars
	 *   caractere(s) à tester avant de tenter la preg
	 * @param string $start_with
	 *   caractere(s) par lesquels commencent l'expression recherchée (permet de démarrer la preg à la prochaine occurence de cette chaine)
	 * @param string $preg
	 *   preg utilisée pour la collecte
	 * @param int $max_items
	 *   pour limiter le nombre de preg collectée (pour la detection simple de présence par exemple)
	 */
	protected static function collecteur(
		string $texte,
		string $if_chars,
		string $start_with,
		string $preg,
		int $max_items = 0
	): array {

		$collection = [];
		$pos = 0;
		if (!$if_chars || str_contains($texte, $if_chars)) {
			$ifchars_last_pos = ($if_chars ? strrpos($texte, $if_chars) : strlen($texte) - 1);
			while (
				$pos <= $ifchars_last_pos
				&& ($next = ($start_with ? strpos($texte, $start_with, $pos) : $pos)) !== false
			) {
				if (preg_match($preg, $texte, $r, PREG_OFFSET_CAPTURE, $next)) {
					$found_pos = $r[0][1];
					$found_length = strlen($r[0][0]);
					$match = [
						'raw' => $r[0][0],
						'match' => array_column($r, 0),
						'pos' => $found_pos,
						'length' => $found_length,
					];

					$collection[] = $match;

					if ($max_items && count($collection) === $max_items) {
						break;
					}

					$pos = $match['pos'] + $match['length'];
				} else {
					if ($start_with || preg_last_error()) {
						$pos = $next + 1; // continuer le reste du texte même si la preg a echoué
					} else {
						break;
					}
				}
			}
		}

		return $collection;
	}

	/**
	 * Sanitizer une collection d'occurences
	 *
	 * @note Cette méthode est souvent surchargée pour pouvoir sanitizer une partie texte sur un collecteur spécifique,
	 * par exemple le texte d’un lien, le texte des multis... Cela ne traite pas forcément donc l’ensemble de la chaine brute.
	 * Le collecteur de modèle ne sanitise rien par exemple (sous entendu que les modèles s’en occupent eux-mêmes)
	 */
	protected function sanitizer_collection(array $collection, string $sanitize_callback): array {
		foreach ($collection as &$c) {
			$c['raw'] = $sanitize_callback($c['raw']);
		}

		return $collection;
	}

	/**
	 * Remplacer les occurrences de la collection d’un texte par un contenu donné en callback
	 *
	 * @note On n’altère pas actuellement le texte (raw) et longeurs (length), du tableau de collecte ; ça pourrait être une option.
	 */
	private function remplacer_collection(string $texte, array $collection, callable $callback): string {
		$offset_pos = 0;
		foreach ($collection as $c) {
			$rempl = $callback($c['raw'], $c);
			if ($rempl !== null) {
				$texte = substr_replace($texte, $rempl, $c['pos'] + $offset_pos, $c['length']);
				$offset_pos += \strlen($rempl) - $c['length'];
			}
		}
		return $texte;
	}
}
