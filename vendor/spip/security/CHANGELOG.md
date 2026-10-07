# Changelog

## 1.6.7 - 2025-09-08

### Fixed

- Simple correction du numéro de version de l’écran

## 1.6.6 - 2025-06-10

### Fixed

- #14 Désactiver par défaut la restriction d'accès des bots en fonction du load. La constante `_ECRAN_SECURITE_LOAD` reste toutefois personnalisable dans `config/ecran_securite_options.php`.
- #15 Ajout de l'agrégateur RSS Netnewswire comme bot ami

## 1.6.5 - 2025-02-14

### Fixed

- mise à jour de la liste des bots + ajout de antennapod (lecteur RSS) comme bot friend

## 1.6.4 - 2025-01-16

### Added

- spip-security/securite#4862 Sanitizer $args dans l'API transmettre.

## 1.6.3 - 2024-08-20

### Security

- spip-security/securite#4859 Vérifier `$_FILES` lorsque utilisé avec Bigup.

## 1.6.2 - 2024-07-26

### Fixed

- spip/spip#5327 Tolérer l’action `converser` (changement de langue) à l’installation de SPIP.

## 1.6.1 - 2024-05-30

### Fixed

- oubli d'affectation de `$_REQUEST` sur 2 cas
- #5 Déclarer `__ecran_test_if_serialized` avant de l'utiliser dans le test

## 1.6.0 - 2024-05-22

### Security

- spip-team/securite#4854 Éxaminer correctement les variables globales POST et GET.

## 1.5.1 - 2023-03-21

### Added

- Installable en tant que package Composer

## 1.5.0 - 2023-02-27

### Added

- Sanitizer toutes les valeurs passées aux formulaires preventivement dans l'écran de sécurité

### Fixed

- Notice PHP : Conversion `int` pour `rand()`