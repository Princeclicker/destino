# composer-installer

Composer Plugin to Install SPIP Applications.

Based on <https://getcomposer.org/doc/articles/plugins.md>

## Identified Composer Types

- ~~`spip-classic`~~
- `spip-ecrire`
- `spip-prive`
- `spip-plugin`

## Reserved Composer Types for future uses

- `spip-lang`
- `spip-theme`

## Extra parameters in root package

- `extra.spip.template` goes to `./squelettes-dist`
- `extra.spip.extensions` go to `./plugins-dist`
- `extra.spip.back_office` go to `./ecrire`
- `extra.spip.private_template` go to `./prive`

```json
{
    "extra": {
        "spip": {
            "back_office": "vendor/back-office",
            "template": "vendor/default-template",
            "extensions": [
                "vendor1/plugin-dist-1",
                "vendor2/plugin-dist-2"
            ],
            "private_template": "vendor/private-template",
        }
    }
}
```

## Mode Dev

### spip:local

- `composer spip:local <command> <args>`
- `composer local <command> <args>`
- `composer l <command> <args>`

### spip:set-ssh-url

- `composer spip:set-ssh-url`
- `composer mode-dev`

```bash
# Interactive
composer local mode-dev
# Non-interactive
composer local mode-dev -n
composer local require --dev symfony/var-dumper flip/whoops rector/rector
composer local install
```

### spip:extensions:switch-forward

- `composer spip:extensions:switch-forward`
- `composer switch:forward`

See [explanation](docs/plugins-dist.md)

### spip:extensions:switch-back

- `composer spip:extensions:switch-back`
- `composer switch:back`

See [explanation](docs/plugins-dist.md)
