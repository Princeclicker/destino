# Changelog

## 4.3.12 - 2026-09-01

### Fixed

- !4769 inclusion manquante pour certains filtres d'images appliqués aux `.svg`

## 4.3.11 - 2026-08-20

### Fixed

- inclusion manquante pour svg_charger ou svg_ajouter_background

## 4.3.10 - 2026-08-12

### Fixed

- inclusion manquante pour svg_recadrer

## 4.3.9 - 2026-08-10

### Fixed

- spip#6096 medias#5029 #4546 la fonction `image_oriente_selon_exif()` utilise les fonctions du core de inc/exif et on l'utilise au début de chaque filtre, comme en SPIP 5
- #4719 s'assurer que width et height ont bien une valeur numerique
- #4703 accepter uniquement du png en tant que masque
- QA (coding standard)

## 4.3.8 - 2026-07-06

### Deprecated

- Logger les dépréciations de _couleur_rgb2hsl et _couleur_hsl2rgb

## 4.3.7 - 2026-05-12

### Fixed

- !4757 Dans certains cas avec SVG le filtre `svg_filter_sepia` est absent et nécessite d’être chargé
- Autoriser PHP 8.5 dans le composer.json

## 4.3.6 - 2026-02-26

### Fixed

- (interne) mise à jour de tests

## 4.3.5 - 2026-02-12

### Fixed

- !4749 Deprecated en PHP 8.5+ de imagedestroy

## 4.3.4 - 2025-10-10

### Fixed

- Typage de la commande spip-cli

## 4.3.3 - 2025-09-08

### Fixed

- spip/ecrire#88 pas de timestamp sur une balise image

## 4.3.2 - 2025-02-14

### Fixed

- #4728 modifier le test sur la constante _CONVERT_COMMAND en vue de la définir si besoin, permettant de rotationner les images avec convert (#4728)

## 4.3.1 - 2025-01-17

### Fixed

- Être compatible avec SPIP 4.4 beta…

## 4.3.0 - 2025-11-27

### Added

- !4736 Commande cli pour purger les images cache trop anciennes (`cache-gd2` et `cache-vignettes`)

### Fixed

- #4722 check existance de exif_read_data()

### Deprecated

- #4723 Filtre `|image_typo`, installer le plugin `Images typographiques`
- #4723 Function `rtl_mb_ord()`, installer le plugin `Images typographiques`
- #4723 Function `rtl_reverse()`, installer le plugin `Images typographiques`
- #4723 Function `rtl_visuel()`, installer le plugin `Images typographiques`
- #4723 Function `printWordWrapped()`, installer le plugin `Images typographiques`
- #4723 Function `produire_image_typo()`, installer le plugin `Images typographiques`

## 4.2.1 - 2024-07-26

### Fixed

- spip/spip#5974 Éviter des warnings sur `image_oriente_selon_exif()` en absence d’image

## 4.2.0 - 2024-05-29

### Added

- spip/spip#5925 Filtre `image_oriente_selon_exif()` pour réorienter automatiquement une image selon son exif

### Changed

- spip/spip#5925 Les filtres d’images tel que `image_recadre` réorientent l’image selon l’exif d’orientation

### Fixed

- !4724 Optimisation du filtre `image_aplatir()`
- !4723 Optimisation du filtre `image_renforcement()`
- !4722 Optimisation du filtre `image_flou()`
- !4721 Optimisation du filtre `image_sepia()`
- !4718 Optimisation des filtres `image_flip_vertical()` & `image_flip_horizontal()`
- !4720 Optimisation du filtre `image_nb()`
- !4719 Optimisation du filtre `image_gamma()`
- #4716 Optimisation du filtre `image_rotation()`
- #4716 Correction du paramètre `crop` de `image_rotation()`
