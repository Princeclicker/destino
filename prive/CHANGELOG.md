# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 1.1.6 - 2026-09-22

### Fixed

- QA: style, rector, ci

## 1.1.5 - 2026-09-02

### Security

- spip-security/securite#4909 la modification d'un mot de passe via id_auteur necessite une autorisation

## 1.1.4 - 2026-09-01

### Security

- spip-security/securite#4901 les url de reset on un token qui n'est valide que pour le host de l'URL

### Fixed

- #140 corrections UI sur petit écran (sur liste de liaisons comme auteurs ou mots clés)

## 1.1.3 - 2026-08-12

### Fixed

- le bouton 'reinitialiser la liste des taches' doit recharger le bloc principal de la page
- #162 lever un autoriser_exception avant de modifier le mot de passe

## 1.1.2 - 2026-08-10

### Fixed

- !160 correction affichage RTL du picker

### Security

- spip-security/securite#4888 Une clé de menu est uniquement scalaire

## 1.1.1 - 2026-07-06

### Security

- spip-security/securite#4882 utiliser des attributes selector case insensitive sur les a[href*="javascript:"]
- spip-security/securite#4885 nettoyer les valeurs inatendues pour qualifier_lien

## 1.1.0 - 2026-05-12

### Fixed

- spip/compresseur!4890 Utiliser `minifier` à la place de `compacte`
- !126 !136 Vérification du formulaire d'institution générique des pour les personnes non webmestres avec un objet sans parent
- #107 Chaîne de langue pour les articles à paraître
- !141 Conditionner l'affichage des liens ical dans Suivre la vie du site

### Removed

- spip-contrib-extensions/organiseur#4698 spip/spip#6084 ical_prive.html déplacé dans le plugin Organiseur.
- !139 spip-contrib-extensions/petitions#3949 Suppression du suivi RSS des signatures de pétitions, déplacé dans le plugin Pétitions.

## 1.0.10 - 2026-03-06

### Fixed

- Deprecated filter 'generer_url_ecrire_entite_edit'

## 1.0.9 - 2026-02-27

### Fixed

- #126 Tolérer du js sur le dépliement des sous-rubriques

## 1.0.8 - 2026-02-26

### Security

- Brider un unserialize dans l’édition d’un logo

### Fixed

- Deprecated dans l’édition d’un logo (utiliser `spip_sanitize_env_from_request()`)
- #123 chargement du formulaire d'institution générique pour les objets sans parents et les personnes non webmestre

## 1.0.7 - 2026-02-18

### Fixed

- !121 Erreurs sur la page Visiteurs `?exec=visiteurs`

## 1.0.6 - 2026-02-12

### Fixed

- #116 Affichage de l’ID d’un auteur sur page Visiteurs
- Faire fonctionner le selecteur d'article avec le paramètre `limite_branche`
- #117 En l'absence de date de rédaction antérieure, ne pas pré-remplir avec `0000-00-00 00:00`
- #102 Sur petit écran, afficher le nom des auteurs dans les listes
- #35 Afficher le nombre d'articles dans les différentes listes de la page d'accueil
- #107 !34 Chaînes de langue singulier et pluriel sur toutes les listes d'objets qui manquaient
- #105 Limiter à 500000 les articles paginés et à 25 pour les articles les plus récents

## 1.0.5 - 2025-12-05

### Fixed

- ajout du pipeline `afficher_config_objet`  manquant sur les pages auteurs
- #108 Retirer la clé `titre=` erronée dans le suivi édito
- spip/dist#4867 prise en charge de la page indentifiants comme pour login & spip_pass

## 1.0.4 - 2025-10-10

### Fixed

- !45 Ne pas gérer le flux RSS des brèves ici (mettre à jour le plugin Brèves en conséquence, si utilisé)
- #103 Améliorer l'affichage de retour du formulaire de reset de mot de passe.
- #101 Rendre facultatives les boucles breves et messages
- Quand le `#FORMULAIRE_EDITER_LIENS` n'est pas editable, l'afficher uniquement s’il y a des objets liés.
- !96 Pas de marge sur les `ul` de pagination

## 1.0.3 - 2025-09-08

### Fixed

- !93 Dans un ensemble de choix, mettre en gras automatiquement le choix `:checked` si possible
- !93 Dans un .editer.obligatoire on veut certes que le .label principal soit gras, mais pas les label des .choix
- #99 Ne pas enregistrer de date de rédaction antérieure si la date est vide
- #95 ne pas appliquer les traitements de sécurité sur les messages de retour du formulaire editer_logo afin de pouvoir y injecter du Javascript
- !86 Le bouton de réinitialisation de la couleur de login ne doit être affiché que si la couleur est différente de la couleur par défaut
- spip/ecrire#73 Ne pas ajouter de lien pour confirmer l'inscription sur les visiteurs
- #92 Rétablir les puces "plus" dans les sélecteurs de rubriques/articles


## 1.0.2 - 2025-06-10

### Fixed

- #61 ce n'est pas un justify-content: space-between que l'on veut sur le body, mais simplement que le pied soit aligné en bas

## 1.0.1 - 2025-04-08

### Fixed

- #80 erreur PHP fatale lors de la regénération d'un mot de passe, dans certains cas
- #79 faire fonctionner le bouton "Envoyer un lien pour choisir son mot de passe" lors de la création d'un nouveau compte
- #78 rétablir la pagination des rubriques d'un·e admin restreint·e
- !64 En RTL, justifié les listes d'objets à droite

## 1.0.0 - 2025-02-14

### Security

- #65 Nettoyer certaines entrées auteurs sur le formulaire d’édition d’auteur (évite de se retrouver dans des logs, si des logs verbeux sont activés)

### Fixed

- #64 Dans l’espace privé, éviter une scrollbar horizontale dans certaines configurations de systèmes d’exploitation.
- #59 aligner le contenu de la bande `en_lignes` avec celui du bandeau
- #13 meilleur affichage des listes d'objets sur petit écran
- spip#6022: Pas de marge basse sur les derniers éléments des réponses

## 1.0.0-beta2 - 2025-01-17

### Security

- spip-security/securite#4860 Bien tester les autorisations d'afficher le contenu des articles/rubriques dans les fragments chargés en ajax

### Added

- #18 utiliser le pipeline `compter_contributions_auteur` dans la boite d'info d'un auteur

### Fixed

- #18 transmettre au pipeline `compter_contributions_auteur` le nombre de contribution sur les articles

## 1.0.0-beta - 2024-12-03

### Added

- #35 Possibilité de personnaliser les chaînes de langue singulier et pluriel du titre de toutes les listes d'objets
- spip/spip#5560 Nouveaux layouts pour le privé (`#LAYOUT_PRIVE`) : `defaut`, `fluide`, `pleine-largeur`, `complements-droite`, `complements-bas`, `complements-bas-inverse`
- spip/spip#6005: Dépréciation de la constante `_DIR_RESTREINT_ABS`
- !20: les valeurs d'environnement explicitement vidées en ajax passent par `var_nullify` pour en être totalement expurgées
- !10: Permettre de trier les visiteurs 'nouveau' par date d'inscription-relance
- #3: Ajouter l'heure de publication à côté de la date
- !1: Utiliser des variables CSS dans l’espace privé pour éviter la compilation des fichiers CSS
- Composerisation

### Changed


- spip/spip#5560 La page de contrôle des tâches utilise le layout `complement-gauche`
- spip/medias#4958 Utilisation de `image_extensions_logos()` à la place de `$GLOBALS['formats_logos']`

### Fixed

- !36 Passer l'url à l'item de langue `pass_reset_url`
- !24 pour le formulaire générique d'institution d'objet, tester l'autorisation `publierdans` en utilisant le parent déclaré par l'API de parenté
- spip/spip#3408 Dans le formulaire générique d'institution d'un objet, utiliser le même jeu de test pour l'affichage du statut `publie` dans `charger` et pour la validation dans `verifier`
- #14 Pouvoir supprimer l'image de l'écran de connexion
- !13 Pouvoir modifier logo principal quand il y a un logo de survol
- spip/spip#3928 Les emails des auteurs sont masqués par défaut

### Removed

- #5505 #5988 Prise en compte de l’environnement de formulaire `_texte_trop_long` qui servait pour IE
