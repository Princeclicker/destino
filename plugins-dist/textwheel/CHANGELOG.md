# Changelog

## 3.3.11 - 2026-09-01

### Fixed

- #4895 ne pas traiter le raccourci cadre/frame dans les traitements typo

## 3.3.10 - 2026-08-10

### Fixed

- spip-security/securite#4855 appliquer aux balises `<pre>` le même traitement qu'aux balises `<code>` dans l'espace privé et dans le public paranoid
- QA (coding standard)

## 3.3.9 - 2026-07-06

### Fixed

- spip-security/securite#4882 il faut decoder aussi les éventuelles entités html et html5

## 3.3.8 - 2026-02-27

### Fixed

- #4892 Ne pas échapper les liens parce qu'il manque simplement la balise fermante.

## 3.3.7 - 2026-02-26

### Fixed

- !4902 Forcer la mise à jour des cache des wheels suite aux versions 3.3.6 & 3.3.5

## 3.3.6 - 2026-02-18

### Security

- !4901 Appliquer systématiquement `echappe_anti_xss` pour les balises `input`,`form`,`button`,`a`

### Fixed

- spip-security/securite#4866 appeller `is_html_safe()` avec l'option `ignore_echappe_js=true` pour eviter une réentrance

## 3.3.5 - 2026-02-12

### Security

- Inspecter aussi les balises `input`, `form`, `button` et `a`

### Fixed

- Accepter PHP 8.5
- Homogéneiser les détections de la même manière que `echapper_html_suspect()` du core
- Utiliser les fonctions `afficher_html_suspect()` et `is_html_safe()`

## 3.3.4 - 2025-09-08

### Fixed

- !4893 La langue dans les modèles inclus via propre doit être celle de la boucle en cours

## 3.3.3 - 2025-04-08

### Fixed

- #4485 Autoriser les fusions de cellule dès la 2ème ligne du tableau
- spip/spip#6037 `traiter_lien_explicite()` renvoie un tableau associatif en mode `'tout'`
- spip/spip#6037 `traiter_lien_explicite()` en l'absence de protocole explicite, mettre `https` et plus `http`
- spip/spip#6037 `traiter_lien_explicite()` utiliser `_PROTOCOLES_STD` pour la liste des protocoles à rechercher

## 3.3.2 - 2025-02-14

### Fixed

- Correction du Changelog…

## 3.3.1 - 2025-01-17

### Fixed

- !4886 Tableaux (colspan & rowspan): ignorer un ^ sur le première ligne, ce qui n'a pas de sens, et éviter une variable indéfinie

## 3.3.0 - 2024-11-27

### Deprecated

- spip/spip#5993 `replace_math()`, utiliser le plugin `mathjax` à la place
