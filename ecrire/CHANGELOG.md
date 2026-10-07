# Changelog

Changelog de SPIP 4.4

## 4.4.25 - 2026-09-25

### Fixed

- #184 ne pas cacher les pages 404
- curl_close inutile en PHP 8, déprécié PHP 8.5
- spip/urls#4842 Calcul de self() avec les urls arborescentes

## 4.4.24 - 2026-09-22

### Fixed

- #182 faire tourner le genie `mise_a_jour` toutes les 6h
- !370 Éviter du cache sur des contextes PHP (complément de !324)
- !371 Éviter une boucle infinie si un collecteur ne fournit ni `$ifchars` ni `$start_with`
- !369 Éviter une fatale sur `verifier_cle_action()`
- #181 Dans l'espace privé la balise `#CACHE` ne fait rien
- !357 Une coquille de parenthèse rendait une traduction de requete sqlite pour le moins hasardeuse
- #179 toujours appeler `autoriser_exception` avec un type non normalisé
- #175 chemin_image() sur les nom d'icone sans extension
- #169 ne pas echouer à construire la minipage admin d'echec
- !126 corriger la déclaration de 'urls_connect_dist()'
- #174 urls de type ?page=x/y pour les webmestres
- Notice PHP lorsqu’une url n’a pas de query string sur queue_lancer_url_http_async()

## 4.4.23 - 2026-09-02

### Security

- spip-security/securite#4907 si le texte est plus long que 8ko on echappe tous les < car on n’est pas certain que le collecteur va trouver tous les modèles
- spip-security/securite#4907 les modèles interdit sont bloqués sans dentelle
- spip-security/securite#4907 ne pas interrompre la recherche sur un preg_match echoué, cela peut venir d'une erreur de preg

### Fixed

- #173 rétablir l'appel au filtre insert_head_css_conditionnel
- #170 conditionner le log d'erreur de connection pour les boucles DATA
- autosave: ne pas supprimer abusiment les antislash `\`

## 4.4.22 - 2026-09-01

### Security

- spip-security/securite#4906 dans les formulaires les champs _xxx permettent d'injecter du html ou du js mais pas plus
- spip-security/securite#4901 si on a un HTTP_X_FORWARDED_HOST différent de HTTP_HOST on l'utilise mais on ne lui fait confiance que si il est connu
- spip-security/securite#4900 urlencode() plus facilement les chaines dans safe_export_env()

### Added

- spip-security/securite#4902 fonction `urls_type_objet_valide()` pour vérifier si un type correspond à un objet URL-able
- spip-security/securite#4902 Liste d’autorisation et d’interdiction des modèles SPIP pour les contributions publiques

### Fixed

- Éviter de générer du cache avec des contextes empoisonnés par du PHP
- spip-security/securite#4900 ne pas se fier qu'a la regexp et s'assurer que la directive header() est la seule chose de ce bloc PHP
- Dépréciation PHP 8.4 sur ReflectionMethod

### Changed

- Signer les filtres insérés via la balise #FILTRE

## 4.4.21 - 2026-08-20

### Fixed

- spip/spip#5802 utiliser `date()` pour émuler `DATE_FORMAT()` en sqlite
- spip/medias#5040 faire fonctionner "titre REGEXP '^$'" en sqlite
- #165 Autoriser la création du password d’un nouvel auteur
- #159 Simplifier `attribut_url()`

### Security

- !312 compléter `safe_export_env()` pour traiter aussi les clés de tableaux
- #167 Ne pas capturer des headers en dehors de cas prévus (issus de la balise HTTP_HEADER notamment)

## 4.4.20 - 2026-08-17

### Added

- spip-security/securite#4899 collecteur de blocs `<?php ... ?>` dans une page html

### Fixed

- Collecteurs: si la callback de la méthode `remplacer()` renvoie `null` alors on ne remplace pas l'occurence et on continue
- #164 Ne pas redéfinir `spip_interdire_cache` (pouvant arriver sur des traitements longs, spécifiques mysql)

### Security

- spip-security/securite#4899 fonction `safe_export_env()` pour exporter l'env de manière un peu sécurisée dans les code d'inclusion
- spip-security/securite#4899 utiliser un collecteur PHP pour echapper le php avant de filtrer un squelette et collecter les headers venant de balises SPIP

## 4.4.19 - 2026-08-12

### Fixed

- si pas de meta version_installee déclencher un upgrade à partir d'une version arbitraire
- ne pas timeout sur l'update des signature de jobs
- #162 s'assurer que autoriser() et bien chargé
- #160 le bon niveau de log (et la bonne constante)

## 4.4.18 - 2026-08-10

### Fixed

- spip-security/securite#4888 Correction de cas d’usages avec des boucles DATA (depuis SPIP 4.4.17)

### Security

- spip-security/securite#4888 sql_serveur spécifique des boucles DATA uniquement dans le calcul des critères
- spip-security/securite#4898 transmettre un sql_serveur à tous les appels de kwote

## 4.4.17 - 2026-08-10

### Added

- spip-security/securite#4895 Signature sur les jobs
- spip-security/securite#4891 ajout de SpipCles::secret_des_actions()
- Ajout de la méthode remplacer() pour les collecteurs (AbstractCollecteur)
- spip-contrib-extensions/multidomaines!24 #144 Ajout d'un pipeline qui permet d'informer les plugins d'une déconnexion
- spip-security/securite#4897 recuperer_url supporte une option callback_valider_url

### Fixed

- |chercher_rubrique{} appelé avec actionable=1 est déprécié, poste vers l'action 'instituer_parent_objet' et vérifie l'autorisation d'instituer
- #138 distant_trouver_extension_selon_headers() prefere l'extension de l'URL
- Changement de méthode par recuperer_url en cas de 301/302/303
- #157 supprimer les `<template>` du calcul de l'introduction
- spip/spip#6096 spip/medias#5029 spip/images#4546 support des exif sur les images
- spip-security/securite#4893 si champs_editable n'est pas défini, ce n'est pas un objet editorial proprement déclaré
- spip-security/securite#4889 Réparer le filtre ajax des modèles

### Security

- spip-security/securite#4894 verifier les autorisations de modifier login et pass dans auteur_instituer() car ce sont bien des informations sensibles
- spip-security/securite#4887 find_in_path() refuse les requetes contenant un ../ dans le chemin, cela fait sortir du document_root
- spip-security/securite#4892 L’échappement SQL appliqué sur un champ date ignore uniquement la fonction NOW()
- spip-security/securite#4888 Le serveur SQL d’une boucle DATA lui est spécifique
- l'appel en POST sur editer_objet, editer_rubrique, editer_article, editer_auteur est déprécié. Si on l'utilise il passe par une autorisation explicite

## 4.4.16 - 2026-07-06

### Deprecated

- Le 5è paramètre de quete_logo est déprécié depuis SPIP 4.2 ; le logger

## 4.4.15 - 2026-05-22

### Security

- spip-security/securite#4881 éviter un open-redirect

### Fixed

- !256 définir `_VAR_MODE` si besoin dans minipage
- #147 si la session est vide, la balise `#SESSION` doit renvoyer une chaine vide et non un tableau vide sérialisé, pour pouvoir tester |oui ou |non dessus.
- !255 Caster et définir une valeur par défaut si max_execution_time absent


## 4.4.14 - 2026-05-12

### Security

- spip-security/securite#4879 meilleure sanitisation de HTTP_HOST

### Fixed

- #142 !250 Nginx peuple REMOTE_USER vide
- #141 !248 Correction PHPDoc de calculer_liste()
- #140 !245 `#CHEMIN_IMAGE` gère aussi les avif, webp et jpg

### Deprecated

- Annotation: _ROOT_RACINE et _ROOT_RESTREINT sont dépréciées.

## 4.4.13 - 2026-03-06

### Fixed

- Syntaxe compatible PHP 7.4 (bug introduit en 4.4.12)

## 4.4.12 - 2026-03-06

### Fixed

- spip/ecrire!186 spip/ecrire!197 spip/ecrire!199 Limiter le changement sur les balises avec traitements (tel que `#TEXTE`) provenant de l’env
- spip/spip6080 Bien récupérer l’environnement dans une boucle DATA si le champ n’est pas dedans

### Deprecated

- !204 !204#note_219916 `cadre_depliable()` est déprécié.

## 4.4.11 - 2026-02-27

### Fixed

- !197 !186 Sur les balise `#TRUC*` d’environnement, ne pas appliquer les traitements, comme sur `ENV*{truc}`
- !186 spip/prive#127 Retours plus tolérants des balises `#ID_` (correction de !186)

## 4.4.10 - 2026-02-26

### Security

- spip-security/securite#4872 Mieux sanitizer les valeurs d’environnement tabulaires provenant du GET ou POST
- spip-security/securite#4857 Gérer mieux le fallback de `#TOTO` vers `#ENV{toto}`
- spip-security/securite#4875 Utiliser `hash_equals` dans `verifier_low_sec()`

### Added

- Fonction `spip_sanitize_env_from_request()` remplaçant `spip_sanitize_from_request([...], '*')` ou `spip_sanitize_from_request([...], [...])`

### Fixed

- spip/tw#4891 Éviter une boucle infinie en respectant l'option `ignore_echappe_js` lors du second appel de `interdire_scripts()`

### Deprecated

- Second argument `*` dans `spip_sanitize_from_request($env, '*')`: Utiliser `spip_sanitize_env_from_request($env)`
- Second argument `array` dans `spip_sanitize_from_request($env, ['nom'])`: Utiliser `spip_sanitize_env_from_request($env, ['nom'])`

## 4.4.9 - 2026-02-18

### Security

- !184 spip-security/securite#4871 Limiter l’usage de données sérialisées dans le filtre `table_valeur` et l’itérateur `DATA`

### Added

- !177 Option `ignore_echappe_js` à `safehtml()` + un argument `$options` à `is_html_safe()` que l'on passe à l'appel interne à `safehtml()`

### Fixed

- !177 Éviter une boucle infinie depuis `echappe_js`
- !182 Notice PHP en présence d’une erreur de squelette, mais en étant non connecté.

### Deprecated

- !184 Filtre `table_valeur` et de l’itérateur `DATA` (avec source table) : déprécier les tableaux sérialisés en entrée

## 4.4.8 - 2026-02-12

### Security

- spip-security/securite#4866 Sécuriser l'affichage des iframe eventuelles soit par echappement soit par sandboxing selon les cas
- spip-security/securite#4866 Améliorer la détection de contenus malicieux dans `echapper_html_suspect()`

### Added

- La fonction `safehtml()` accepte un tableau d'options en second argument, vide par défaut, que l'on passe à `inc_safehml_dist()` (ou fonction surchargée)
- Fonction `afficher_html_suspect()` pour assurer le rendu du html suspect échappé

### Fixed

- #110 !164 Deprecated diverses en PHP & PHP 8.5
- spip/medias#5034 Éviter une indéfinie lors de l'affectation des doublons documents
- !160 Si un pipeline corrompt args/data, le dénoncer dans les logs et en erreur_squelette
- spip/prive#114 `prepare_icone_base()` envoyait une classe erronnée depuis le passage en SVG
- #105 Inscription : ne pas générer 2 fois de suite un jeton
- #105 Inscription : rétablir les paramètres passés au modèle de notification
- #109 Authentification HTTP : Assigner l'auteur à la session uniquement si on a pu le récupérer


## 4.4.7 - 2025-12-05

### Fixed

- Deprecated usage of `_T` in `debusquer_compose_message()`
- Vider la meta 'drapeau_edition' quand on désinstalle un plugin
- Accepter PHP 8.5 à l’installation
- #98 Corriger la définition de IMAGETYPE_SVG qui existe en PHP 8.5
- la langue hazaragi se lit de droite à gauche (RTL)
- accepter "HTTP/2" ou "HTTP/3" comme réponse acceptable

## 4.4.6 - 2025-10-10

### Fixed

- !126 Balises `#URL_ARTICLE` et autres dans une boucle avec un connect externe
- spip-contrib-extensions/spip-bonux#19 warning sur `inc_importer_csv_dist()` en PHP 8.4

### Deprecated

- spip/ecrire!128 L’argument 3 des fonctions `_T` ou `_L` doit être un tableau depuis SPIP 3.0

## 4.4.5 - 2025-09-08

### Security

- spip-security/securite#4865 fix open redirect sur formulaire de login en ajax

### Fixed

- #88 Simplification dans `http_img_pack` évitant un `file_exists`
- #88 La fonction timestamp peut accepter une entrée null
- #88 La fonction `timestamp` gère le cas d'un fichier ayant déjà un timestamp
- #60 Retour correct du pipeline `cvtconf_formulaire_charger`
- #76 Éviter des erreurs sur la suppression des fichiers de cache
- !113 L'optimisation du collecteur empêchait de retrouver les balises avec une casse mixte
- !108 Correction du collecteur sur les commentaires HTML
- !114 Collecte spécifique des balises `<code>` dont le contenu est ignoré
- !107 Collecte des balises HTML en cas de balise fermante surnuméraire
- #61 utf8_noplanes n'accepte qu'une string en entrée
- spip/prive#99 Filtre `|nom_jour` sur les années négatives ou inférieures à 1901
- #73 Ne pas ajouter de lien pour confirmer l'inscription sur les visiteurs

### Deprecated

- Constante `_IS_CLI` : utiliser `PHP_SAPI === 'cli'` à la place

## 4.4.4 - 2025-06-10

### Fixed

- !74 Les urls d’actions de formulaires finissant par `/` ajoutaient inutilement une ancre

## 4.4.3 - 2025-04-08

### Fixed

- spip/prive#82 Définir l’autorisation de voir le formulaire de préférence des menus…
- #58 Ignorer les commentaires dans la création de champs SQL
- #6065 réparer la pagination ajax de la liste des admins dans la boite info d'une rubrique
- spip/tw#4883 éviter une fatale Undefined constant `_PROTOCOLES_STD` en la définissant dans `spip_initialisation_core()`

## 4.4.2 - 2025-02-18

### Fixed

- #39 Fix warning PHP (un report manquait pour #39)

## 4.4.1 - 2025-02-18

### Fixed

- #41 Utiliser `Spip\Afficher\Minipage\Admin` au lieu de `install_debut_html()` et `install_fin_html()` qui lèvent un deprecated
- #41 L’option `onload` de Minipage n’était pas appliquée…
- #39 Notifier uniquement les mises à jour de `patch` de SPIP en entête de page de l’espace privé
- #39 éviter de stocker une info erronée dans la meta `derniere_maj_notifiee`

## 4.4.0 - 2025-02-14

### Fixed

- charger l'autoloader dans le fichier prive.php
- spip/medias#5020 Éviter un warning PHP si le fichier du logo n'est pas présent
- spip/medias#5008 spip/medias!5034 Suivre medias sur `inc_vignette_dist` qui attend un paramètre medias

## 4.4.0-beta4 - 2025-01-29

### Fixed

- #34 Affichage des chaînes de langue en squelettes sur certains cas.
- #34 !39 Revert: transformation des idiomes en balise (sera dans SPIP 5.0 uniquement)
- !37 Si un plugin indique une icone `''` dans son `xml`, ne pas planter
- #35 Pouvoir se déconnecter
- #35 Le second paramètre de `Minipage` est un tableau

## 4.4.0-beta3 - 2025-01-17

## 4.4.0-beta2 - 2025-01-17

### Security

- spip-security/securite#4862 Sécuriser le contenu du message d'erreur affiché par l'API transmettre

### Changed

- #33 Version max PHP 8.4

### Fixed

- spip/medias#5011 utiliser pour `IMAGETYPE_SVG` une valeur qui ne risque pas une collision avec un futur ajout de format image (19 a été pris par `IMAGETYPE_AVIF` entre temps)
- #24 Correction d’une erreur fatale sur l’appel à `phraser_champs_interieurs()`
- Bonne version le dans paquet.xml

### Deprecated

- #26 Inclusion de fichier PHP via `<INCLURE(fichier.php)>` ou `<INCLURE{fond=fichier.php}>`
- #26 Balise fermante `</INCLURE>`


## 4.4.0-beta - 2024-12-03

### Added

- spip/spip#6003 Ne pas envoyer de mot de passe en clair, mais plutôt des liens pour définir son mot de passe
- spip/spip#6049 `copie_locale()` passe une clé `action` au pipeline `post_edition`
- spip/prive#35 Chaînes de langue supplémentaire pour les listes d'articles
- spip/spip#5560 Balise `#LAYOUT_PRIVE`
- spip/spip!5633 Balise `#TRAD{module:cle, #ARRAY{param, val, ..}, #ARRAY{option, val..}}`
- spip/spip#5933 Les actions `ajouter_lien` et `supprimer_lien` peuvent gérer un qualificatif
- spip/spip#5766 Pipeline `ajouter_menus_args`, en complément au pipeline `ajouter_menus`, qui transmet les arguments de `definir_barre_boutons()`
- spip/spip!6051 Purger les variables de `var_nullify` du contexte dans `traiter_appels_inclusions_ajax`
- spip/spip!6044 balise `#PARAM` pour récupérer les paramètres du container de services (Cf [UPGRADE_5.0.md](UPGRADE_5.0.md#Constantes_PHP))
- spip/spip!6034 Le filtre `|affdate` accepte un timestamp en entrée
- spip/medias#4958 Fonction `_image_extensions_logos()` et pipeline `image_extensions_logos`

### Fixed

- !8 Utiliser une variable pour l'url de l'item de langue `pass_reset_url`
- spip/spip!6100 Utiliser `fpassthru()` pour livrer directement les fichiers et eviter un memory limit plutôt que `readfile()` qui passe par un chargement en memoire du fichier
- spip/spip!5633 Possibilité de calculer dynamiquement les paramètres des chaînes de langue
- spip/spip!5633 Possibilité de calculer dynamiquement les paramètre des filtres des chaînes de langue
- spip/spip#5722 Requêter les fichiers distants avec `STREAM_CRYPTO_METHOD_TLS_CLIENT`
- spip/spip#5919 Remplacer les balises `tt` obsolètes par `code`
- spip/spip#3928 Les emails des auteurs sont masqués par défaut

### Deprecated

- spip/spip#5560 Balise `#LARGEUR_ECRAN` pour les squelettes du privé à remplacer par `#LAYOUT_PRIVE`
- spip/spip!5633 Classe interne `Idiome` depréciée, utiliser plutot `phraser_preparer_idiomes()` et la balise interne `#TRAD_IDIOME`
- spip/spip#2536 À partir de SPIP 5, l'appel des chaînes de langues en squelette sera sensible à la casse de la déclaration, il n'y aura plus de conversion automatique en minuscule
- spip/spip!5633 Fonction interne `phraser_boucle_placeholder()` à remplacer par `phraser_placeholder_memoriser_ou_reinjecter()`
- spip/spip!5633 Fonction interne `public_generer_boucle_placeholder()` à remplacer par `public_placeholder_generer()`
- spip/spip#6014 Les fichiers de langue peuplant une `$GLOBALS` sont dépréciés ; renvoyer directement un tableau
- spip/spip#4903 Constante obsolète `_DIR_IMG_PACK`
- spip/spip#5993 Globales `$traiter_math`, `$tex_server`, fonctions `produire_image_math()`, `traiter_math()`, utiliser le plugin `mathjax` à la place
- spip/spip#5992 Modifier la globale `$formats_logos` est déprécié : utiliser le pipeline `image_extensions_logos`
- spip/spip#5992 Appeler la globale `$formats_logos` est déprécié, utiliser la fonction `_images_extensions_logos()`

### Removed

- spip/spip#5505 spip/spip#5988 Fonctions `verif_butineur()`, `editer_texte_recolle()` et environnement `_texte_trop_long` des formulaires (Inutilisé — servait pour IE !)


