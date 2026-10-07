# Changelog

## 3.2.6 - 2026-08-10

### Fixed

- #4925 Ne pas copier `principal.xml` à la fois en version complète et en version allégée
- QA (coding standard)

## 3.2.5 - 2026-05-12

### Fixed

- Type incorrect dans svp_redirige_boucle créant une erreur fatale en PHP 8.5
- spip/spip#6084 remplacement appels minipres() en Minipage

## 3.2.4 - 2026-02-12

### Added

- Fonction `svp_depoter_distant_variantes_url()` pour générer des variantes d'url pour les dépots.

### Changed

- Actualise les dépôts distants avec les nouvelles variantes d'archives par branches spip, plus légers.

### Fixed

- #4921 Plugins avec extension PHP requise, sans compatibilité associée
- Optimisations mémoire
- Prendre en charge la constante `_DEV_VERSION_SPIP_COMPAT` dans la fonction `svp_phraser_archives()`

## 3.2.3 - 2025-09-08

### Fixed

- #4919 Cas très rare où todo ne serait pas un tableau à la lecture des actions
- !4924 Tester aussi la compatibilité des plugins avec la version PHP dispo et les modules PHP

## 3.2.2 - 2025-01-17

### Fixed

- Support de la branche 4.4 de SPIP dans le référenciel des plugins

## 3.2.1 - 2025-12-02

### Fixed

- Message de retour de formulaire en `div` plutôt que `p`

## 3.2.0 - 2025-11-25

### Changed

- spip/spip#5460 `style_prive_plugin_mots` sans compilation de SPIP
