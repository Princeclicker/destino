# Changelog

## 4.4.6 - 2026-09-25

### Fixed

- !4909 éviter deprecated trim(null)

## 4.4.5 - 2026-08-10

### Fixed

- l'appel en POST sur editer_site est déprécié. Si on l'utilise il passe par une autorisation explicite
- correction des auteurs syndiqués suite à 8ed99f41
- QA (coding standard)

### Security

- spip-security/securite#4897 valider l'URL distante à chaque syndication

## 4.4.4 - 2026-05-12

### Fixed

- #4869 warning -- quand un item RSS comporte une balise `source` autofermante
- #4866 réparer le bouton "Tenter une nouvelle récupération" quand un site est en erreur de syndication

## 4.4.3 - 2026-02-18

### Security

- spip-security/securite#4870 sécuriser l'affichage de `#URL_SYNDIC` sur la page d'un site
- spip-security/securite#4869 lors de l'edition d'un site vérifier que l'URL de syndication est bien une URL distante

## 4.4.2 - 2026-02-12

### Fixed

- Deprectated en PHP 8.5

## 4.4.1 - 2025-12-05

### Fixed

- utiliser le bon argument pour la mise à jour d'un site particulier dans la commande spip-cli

## 4.4.0 - 2025-10-10

### Added

- ajout d'une commande spip-cli pour mettre à jour un ou tous les sites syndiqués

## 4.3.3 - 2025-02-18

### Fixed

- #4878 Notices PHP dans certaines analyses RSS

## 4.3.2 - 2025-02-14

### Fixed

- Correction du Changelog…

## 4.3.1 - 2025-01-17

### Fixed

- #4877 Dédoublonner test de statut dans une requête SQL.

## 4.3.0 - 2025-11-25

### Fixed

- spip/spip#5460 Utiliser des variables CSS et les propriétés logiques dans la CSS de l'espace privé
