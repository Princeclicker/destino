# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html) and
[Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/).

## 0.8.2 - 2025-01-17

### Fixed

- Standards de code (CS, Rector)

## 0.8.1 - 2024-12-10

### Fixed

- #8 Appliquer systématiquement le CHMOD sur IMG, local et tmp à l'installation

## 0.7.2 - 2024-11-19

### Added

- `spip:extensions:switch-forward` and `spip:extensions:switch-back` commands
- inclut spip/spip (projet racine, "porte d'entrée") pour le mode-dev (aka git ssh)
- nettoie le cache des "wheels" de textwheel lors d'un composer install/update
- force le chmod sur SpipLeague\Composer\BaseDirectories::createBaseDirectories

## 0.6.4 - 2024-10-10

### Added

- SPIP assets clear cache script

## 0.6.3 - 2024-09-30

### Added

- SPIP plugins clear cache script

## 0.6.2 - 2024-09-18

### Added

- `prive/` and `ecrire/` ready for `composer spip:set-ssh-url`

## 0.6.0 - 2023-03-27

### Added

- `composer spip:local` command
- `composer spip:set-ssh-url` comand

### Deprecated

- Safety Screen composer script
- ModeDev composer script

## 0.5.1 - 2023-03-24

### Added

- ModeDev composer script

## 0.5.0 - 2023-03-19

### Changed

- Composer version

### Fixed

- Unit tests

## 0.4.3 - 2021-10-06

### Changed

- Composer version

## 0.4.2 - 2021-05-02

### Changed

- Composer version

## 0.4.1 - 2020-08-21

### Fixed

- Exclude files/directories from Classic copy on install

## 0.4.0 - 2020-08-20

### Changed

- Composer 2.0 API

## 0.3.0 - 2019-05-01

### Added

- Safety Screen composer script

## 0.2 - 2018-05-02

### Added

- Unit tests
- spip-ecrire, spip-prive types
- spip-plugin, extra.spip.template, extra.spip.extensions

### Removed

- spip-cms type
