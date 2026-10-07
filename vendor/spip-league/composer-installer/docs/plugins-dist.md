# Extensions d'une distribution SPIP

## Arborescence

### SPIP4.4

```txt
.
├── composer.json
├── composer.lock
├── plugins-dist
│   ├── aide
│   ├── ...
│   └── urls_etendues
└── plugins-dist.json
```

### SPIP5

```txt
.
├── composer.json
├── composer.lock
├── plugins-dist
    └── spip
        ├── aide
        ├── ...
        └── urls
```

## Historique

Le fichier `./plugins-dist.json` est construit comme ci-dessous :

```json
{
    "prefix": {
        "path": "plugins-dist/prefix",
        "source": "https://git.spip.net/spip/prefix.git",
        "branch": "x.y",
        "tag": "vx.y.z",
    },
    "..."  : {
        "path": "...",
        "source": "...",
        "...": "..."
    }
}
```

`path` et `source` sont obligatoires. `branch` et `tag` sont facultatifs.

Chaque entrée de ce fichier peut être normalisé dans une instance de
[la classe Specification](../src/Extensions/Specification.php).

L'ensemble des plugins-dist est normalisé dans une instance de
[la classe Collection](../src/Extensions/Collection.php),
capable de se construire à partir d'un fichier valide ou de générer un fichier valide.

## Composerisation

`composer.json` du projet (racine):

```json
{
    "require": {
        "spip/prefix": "...",
        "...": "..."
    },
    "extra": {
        "spip": {
            "extensions": [
                "spip/prefix",
                "..."
            ]
        }
    }
}
```

`composer.json` d'un plugin-dist spip:

```json
{
    "name": "spip/prefix",
    "type": "spip-plugin",
    "...": "..."
}
```

## Bascule

### Forward 4.4->5

#### Analyse forward

- pour tout prefix présent dans plugins-dist.json:
  - si existence plugins-dist/prefix, marquer le dossier plugins-dist/prefix pour suppression
  - marquer vendor/prefix dans extra.spip.extensions pour ajout si absent
  - marquer vendor/prefix:constraint dans require pour ajout si absent
  - marquer prefix dans plugins-dist.json pour suppression

#### Switch forward

- mettre à jour require et extensions avec les vendor/prefix:constraint marqués
- supprimer les dossiers plugins-dist/prefix marqués
- supprimer les prefix marqués dans plugins-dist.json
- si plugins-dist.json vide, le supprimer
- composer update

### Back 5->4.4

#### Analyse back

- pour tout prefix présent dans extra.spip.extensions:
  - si installé, convertir en spécification 4.4 et marquer prefix/path/source/branch/tag pour ajout à plugins-dist.json
  - marquer vendor/prefix dans extra.spip.extensions pour suppression
  - marquer vendor/prefix:constraint dans require si présent pour suppression
  - marquer le dossier plugins-dist/vendor pour suppression

#### Switch back

- pour tout plugins-dist/vendor marqués, supprimer si vide
- générer plugins-dist.json
- composer update

Reprendre l'usage des outils historiques
