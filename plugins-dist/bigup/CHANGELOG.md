# Changelog

## 3.3.14 - 2026-09-02

### Security

- spip-security/securite#4911 Autorisation sur formulaire de test de bigup

## 3.3.13 - 2026-08-17

### Fixed

- #4915 Éviter une erreur JS sur l’upload d’image avec redimensionnement navigateur, sans exif sur l’image

## 3.3.12 - 2026-08-10

### Fixed

- #4910 Meilleure qualité des images retaillées côté navigateur
- #4914 Orientation exif correcte des images retaillées côté navigateur
- QA (conding standard)

## 3.3.11 - 2026-07-06

### Fixed

- #4912 enlever la lang avant d'appeler la fonction identifier() du formulaire

## 3.3.10 - 2026-05-12

### Fixed

- Autoriser PHP 8.5 dans le composer.json

## 3.3.9 - 2026-02-12

### Fixed

- !4928 Deprecated en PHP 8.5+ de finfo_close

## 3.3.8 - 2025-07-17

### Fixed

- #4906 transmettre le nombre d'uploads en succès dans l'événement 'bigup.complete' à la fin de l'upload

## 3.3.7 - 2025-02-14

### Fixed

- spip/porte-plume#4833 Ne plus déclarer de z-index sur les colonnes qui sont déjà en flex.

## 3.3.6 - 2025-01-17

Note: retrouver la cohérence version paquet.xml / tag

## 3.3.5 - 2024-11-27

Note: les tags 3.3.0 à 3.3.4 sont erronés.

### Changed

- spip/medias#4958 Avec SPIP 4.4, utiliser `_image_extensions_logos()`, sinon utiliser la globale `$formats_logos`

### Fixed

- spip/spip#5460 Utiliser des variables CSS et les propriétés logiques dans la CSS de l'espace privé
- #4900 Inclusion manquante dans certains contextes ajax