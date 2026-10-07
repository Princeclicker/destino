# Changelog

## 3.1.7 - 2026-08-10

### Fixed

- QA (coding standard)

## 3.1.6 - 2026-05-12

### Fixed

- Corrections de tests

## 3.1.5 - 2026-02-18

### Fixed

- Maj lib svg-sanitizer en 0.22.0

## 3.1.4 - 2026-02-12

### Added

- spip-security/securite#4866 Options `allowIframe` et `allowIframeURIRegexp` permettant d'accepter des iframe, eventuellement sur la base d'une regexp pour l'URL.

### Changed

- spip-security/securite#4866 `inc_safehtml_dist()` accepte un tableau d'options en second argument, vide par défaut.

### Fixed

- Maj lib htmlpurifieur-html5 en 0.1.12 (corrections PHP 8.4+)
- Maj lib htmlpurifier en 4.19.0 (corrections diverses & PHP 8.4+)

## 3.1.3 - 2024-11-12

### Fixed

- Description composer.json

## 3.1.2 - 2024-05-07

### Changed

- Mise à jour des chaînes de langues
- Compatibilité max sur SPIP 4.*

## 3.1.1 - 2023-09-01

### Fixed

- Exclure les tests et fichiers de développement des livrables

## 3.1.0 - 2023-01-27

### Added

- spip/safehtml#4786 Ajout du Sanitizer SVG auparavant dans le plugin medias
- Fichier `README.md`

### Changed

- spip/spip#5271 Utilise HTMLPurifier à la place de SafeHTML
- Conversion des tests unitaires en PHPUnit
- Compatible SPIP 4.2.0-dev
