# Changelog

## 2.2.6 - 2026-08-10

### Fixed

- En absence de fichier à extraire, il faut passer null à ZipArchive::extractTo
- QA (coding standard)

## 2.2.5 - 2026-05-12

### Fixed

- Tests en erreurs.

## 2.2.4 - 2026-02-26

### Fixed

- PHP 8.5 deprecated

## 2.2.3 - 2024-05-07

### Changed

- Mise à jour des chaînes de langues
- Compatibilité max sur SPIP 4.*

## 2.2.2 - 2023-05-28

### Fixed

- #4429 `Spip\Archiver\ArchiverInterface::emballer()` accepte comme premier paramètre, soit une liste de fichiers, soit un tableau associatif du type `['source' => 'destination']`, auquel cas le second paramètre n'est pas pris en compte.

## 2.2.1 - 2023-05-27

### Changed

- Gestion des retours des méthodes SpipArchiver::commenter et SpipArchiver::retirer

## 2.2.0 - 2023-01-27

### Added

- Fichier `README.md`
