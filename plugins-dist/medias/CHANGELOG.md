# Changelog


## 4.4.14 - 2026-09-22

### Fixed

- #5047 Corrige le remplacement de document et l’ajout ou modification de vignette de document

## 4.4.13 - 2026-09-02

### Fixed

- spip-security/securite#4910 verifier la galerie fournie dans le #ENV et n'accepter que des choses du type prive/squelettes/inclure/mediatheque-xxx

## 4.4.12 - 2026-09-01

### Security

- spip-security/securite#4902 Vérifier les droits d'upload ou de jointure pour l’envoi de document

## 4.4.11 - 2026-08-10

### Fixed

- L'appel en POST sur editer_document est déprécié. Si on l'utilise il passe par une autorisation explicite
- QA (Coding Standard)

## 4.4.10 - 2026-05-12

### Security

- spip-security/securite#4880 Certains champs de documents de doivent pas être éditables manuellement.

### Fixed

- spip/spip#6084 remplacement appels minipres() en Minipres
- Mise à jour de la librairie getId3

## 4.4.9 - 2026-02-18

### Fixed

- S'assurer que la constante IMAGETYPE_SVG est bien définie

## 4.4.8 - 2026-02-12

### Security

- spip-security/securite#4867 Sécuriser les paramètres `align` et `class` pour les modèles de documents

### Fixed

- #4455 Prise en charge du typedoc `apk`

## 4.4.7 - 2025-12-05

### Fixed

- adapter le markup pour bénéficier des styles de `.instituer_objet`
- la liste d'onglets pour filtrer selon `medias` utilise la table `spip_documents_types`

## 4.4.6 - 2025-10-10

### Fixed

- timestamper l'image de la balise `<img>` générée par la balise `#LOGO_DOCUMENT` et protéger également les URLs via `attribut_url`

## 4.4.5 - 2025-06-10

### Fixed

- Mise à jour de la librairie mejs en version 7.0.7

## 4.4.4 - 2025-04-08

### Fixed

- #5016 La navigation dans la médiathèque peut recevoir n'importe quel id d'objet

## 4.4.3 - 2025-02-18

### Fixed

- #5024 La tache cron de nettoyage des documents (logos orphelins) doit avoir l’autorisation de les supprimer
- #5021 Correction de la balise `#MIME_TYPE` si elle est hors boucle.

## 4.4.2 - 2025-02-14

### Added

- #5008 balise `#MIME_TYPE` calculée
- #4645 balise  `#URL_DOCUMENT_FICHIER` qui retourne l’url du fichier du document ; `#URL_DOCUMENT` pouvant retourner une URL vers une page d’objet éditorial "document" si une configuration est utilisée.

### Changed

- #5008 `inc_vignette_dist()` prend un media en second argument, pour décliner la vignette selon le type de fichier

### Fixed

- #4645 ajouter la nouvelle balise `#URL_DOCUMENT_FICHIER` et l'utiliser partout où on sait que c'est vraiment un fichier attendu
- #5017 Permettre de ne pas avoir d’autolien sur une grande image avec `<docXX|lien=>`
- #5008 Calcul dynamique de `#MIME_TYPE` (qui peut varier par exemple pour les mp4 selon le media de type video, audio, ...)
- #5008 declarer audio/mp4 comme un alias de application/mp4
- #5008 afficher un titre de mime-type plus pertinent sur les mp4 qui peuvent etre audio/video/rien, on traite ça de manière générique
- le champ media n'existe plus sur spip_documents, c'est media_defaut

## 4.4.1 - 2025-01-17

### Fixed

- spip/spip#6022 message de retour de formulaire en `div` plutot qu'en `p`
- #5012 Le mimetype `audio/x-m4a` est un alias de `audio/mp4a-latm`

## 4.4.0 - 2025-11-27

### Changed

- #4958 Appel à la globale `$formats_logos` remplacée par `_image_extensions_acceptees_en_entree()`

### Fixed

- spip/spip#5460 Utiliser les propriétés logiques dans la CSS de l'espace privé
- !5008 Corriger la duplication (plugin Duplicator par exemple) de logo si le dossier `tmp/upload` n'existe pas
- #4999 Affichage du sélecteur de rôles de documents (avec le plugin en question)
- !5009 Affichage des aperçus dans les modèles emb
- !5010 Correction chemin des plugins mediaelements
- !5010 Pas de fallback Flash
