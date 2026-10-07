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
 * Extrait une langue des extraits polyglottes (`<multi>`)
 *
 * Retrouve les balises `<multi>` d'un texte et remplace son contenu
 * par l'extrait correspondant à la langue demandée.
 *
 * Si la langue demandée n'est pas trouvée dans le multi, ni une langue
 * approchante (exemple `fr` si on demande `fr_TU`), on retourne l'extrait
 * correspondant à la langue par défaut (option 'lang_defaut'), qui est
 * par défaut la langue du site. Et si l'extrait n'existe toujours pas
 * dans cette langue, ça utilisera la première langue utilisée
 * dans la balise `<multi>`.
 *
 * Ne pas mettre de span@lang=fr si on est déjà en fr.
 */
class HtmlTag extends AbstractCollecteur
{
	protected static string $markPrefix = 'HTMLTAG';

	/**
	 * La preg pour découper et collecter les modèles
	 */
	protected string $preg_openingtag;

	protected string $preg_closingtag;

	protected string $tag;

	protected string $lowercase_tag;

	public function __construct(string $tag, ?string $preg_openingtag = null, ?string $preg_closingtag = null) {

		$this->tag = $tag;
		$tag = strtolower($tag);
		$this->lowercase_tag = $tag;
		$this->preg_openingtag = ($preg_openingtag ?: "@<{$tag}\b([^>]*?)(/?)>@isS");
		$this->preg_closingtag = ($preg_closingtag ?: "@</{$tag}\b[^>]*>@isS");
	}

	/**
	 * @param array{detecter_presence?: bool, nb_max?: int, profondeur?: int} $options
	 */
	public function collecter(string $texte, array $options = []): array {
		if (!$texte) {
			return [];
		}

		// si on cherche au départ une balise qui n'est pas en minuscule,
		// considérer qu'on ne peut pas se limiter à la recherche du tag en minuscules
		$hasUpperCaseTags = ($this->tag !== $this->lowercase_tag);
		if (!$hasUpperCaseTags) {
			$upperTag = strtoupper($this->tag);
			$hasUpperCaseTags = ($upperTag !== $this->lowercase_tag && (str_contains($texte, '<' . $upperTag) || str_contains(
				$texte,
				'</' . $upperTag
			)));
		}

		// inactiver les balises dans les commentaires html (on remplace les <)
		if (str_contains($texte, '<!--')) {
			$texte_sans_commentaire_actif = $texte;
			$htmlCommentCollecteur = new HtmlComment();
			$commentsCollection = $htmlCommentCollecteur->collecter($texte, []);
			if (!empty($commentsCollection)) {
				foreach ($commentsCollection as $comment) {
					// on remplace simplement par un espace pour ne pas changer la longueur des commentaires
					$escaped = '<!--' . str_replace('<', ' ', $comment['innerHtml']) . '-->';
					$texte_sans_commentaire_actif = substr_replace(
						$texte_sans_commentaire_actif,
						$escaped,
						$comment['pos'],
						$comment['length']
					);
				}
			}
		} else {
			$texte_sans_commentaire_actif = &$texte;
		}

		// collecter les balises ouvrantes
		$opening = static::collecteur(
			$texte_sans_commentaire_actif,
			'>',
			$hasUpperCaseTags ? '<' : '<' . $this->lowercase_tag,
			$this->preg_openingtag,
			empty($options['detecter_presence']) ? 0 : 1
		);

		if (!$opening) {
			return [];
		}

		// collecter les balises fermantes
		$closing = static::collecteur(
			$texte_sans_commentaire_actif,
			'>',
			$hasUpperCaseTags ? '</' : '</' . $this->lowercase_tag,
			$this->preg_closingtag
		);

		$profondeur = ($options['profondeur'] ?? 1);
		$tags = [];
		while (!empty($opening)) {
			$first_opening = array_shift($opening);
			// self closing ?
			if (str_ends_with($first_opening['raw'], '/>')) {
				$tag = $first_opening;
				$tag['opening'] = $tag['raw'];
				$tag['closing'] = '';
				$tag['innerHtml'] = '';
				$tags[] = $tag;
			} else {
				// enlever les closing qui sont avant le premier opening, car ils n'ont pas de sens
				while (
					!empty($closing)
					&& ($first_closing = reset($closing))
					&& $first_closing['pos'] < $first_opening['pos']
				) {
					array_shift($closing);
				}

				$need_closing = 0;
				$next_closing = reset($closing);
				$next_opening = reset($opening);
				// certaines balises comme <code> neutralisent le contenant, donc tout ce qui est avant le prochain closing doit etre ignoré
				if (in_array($this->lowercase_tag, ['code'])) {
					while ($next_opening && $next_closing && $next_opening['pos'] < $next_closing['pos']) {
						array_shift($opening);
						$next_opening = reset($opening);
					}
				} else {
					while ($next_opening && $next_closing && $next_opening['pos'] < $next_closing['pos']) {
						while ($next_opening && $next_opening['pos'] < $next_closing['pos']) {
							// si pas self closing, il faut un closing de plus
							if (!str_ends_with($next_opening['raw'], '/>')) {
								$need_closing++;
							}
							array_shift($opening);
							$next_opening = reset($opening);
						}
						// il faut depiler les balises fermantes autant de fois que nécessaire et tant qu'on a pas une nouvelle balise ouvrante
						while ($need_closing && $next_closing && (!$next_opening || $next_closing['pos'] < $next_opening['pos'])) {
							array_shift($closing);
							$need_closing--;
							$next_closing = reset($closing);
						}
					}
				}
				// si pas de fermeture, c'est une autofermante mal fermée...
				if (!$next_closing || $need_closing) {
					$tag = $first_opening;
					$tag['opening'] = $tag['raw'];
					$tag['closing'] = '';
					$tag['innerHtml'] = '';
					$tags[] = $tag;
				} else {
					$tag = $first_opening;
					$next_closing = array_shift($closing);
					$innerHtml = substr($texte, $tag['pos'] + $tag['length'], $next_closing['pos'] - $tag['pos'] - $tag['length']);
					$tag['length'] = $next_closing['pos'] - $tag['pos'] + $next_closing['length'];
					$tag['opening'] = $tag['raw'];
					$tag['raw'] = substr($texte, $tag['pos'], $tag['length']);
					$tag['innerHtml'] = $innerHtml;
					$tag['closing'] = $next_closing['raw'];
					$tags[] = $tag;
				}
			}
			if ((!empty($options['detecter_presence']) && count($tags))) {
				return $tags;
			}
			if (($profondeur == 1 && !empty($options['nb_max']) && count($tags) >= $options['nb_max'])) {
				break;
			}
		}

		while (--$profondeur > 0) {
			$outerTags = $tags;
			$tags = [];
			$options['profondeur'] = 1;
			foreach ($outerTags as $outerTag) {
				if (!empty($outerTag['innerHtml'])) {
					$offsetPos = $outerTag['pos'] + strlen($outerTag['opening']);
					$innerTags = $this->collecter($outerTag['innerHtml'], $options);
					if (!empty($innerTags)) {
						foreach ($innerTags as $tag) {
							$tag['pos'] += $offsetPos;
							$tags[] = $tag;
						}
						if (($profondeur == 1 && !empty($options['nb_max']) && count($tags) >= $options['nb_max'])) {
							return $tags;
						}
					}
				}
			}
		}

		return $tags;
	}
}
