# Changelog

## 4.2.5 - 2026-09-22

### Fixed

- #4841 Correction d’empilement d’URL dans certains cas sur les urls arborescentes

## 4.2.4 - 2026-09-01

### Security

- spip-security/securite#4902 Vérifier la validité d’un type d’objet d’URL avant de l’insérer en base
- spip-security/securite#4902 L’autorisation de modification d’URL est réservée aux admins si l’objet est publié
- spip-security/securite#4902 Vérifier le type d'objet dans les formulaires de gestion des urls

## 4.2.3 - 2026-08-10

### Fixed

- QA (coding standard)

## 4.2.2 - 2026-03-06

### Fixed

- Notice PHP en exécutant les tests

## 4.2.1 - 2026-02-18

### Fixed

- #4835 Notice PHP sur urls/arbo.php

## 4.2.0 - 2025-11-27

### Added

- spip-league/composer-installer#5: composerisation version 4.2
- spip/spip#6005: Dépréciation de la constante _DIR_RESTREINT_ABS

### Fixed

- spip/spip#5973 Invalider le cache (même pour les bots) lorsqu’une URL permanente est ajoutée
- spip/spip#5460 Utiliser les propriétés logiques dans la CSS de l'espace privé
