vardef-modifier
========

# About

 * __Author:__ Emil Kilhage
 * __Date Created:__ 2012-04-24
 * __License:__ MIT

See vardefs.example.yml for some syntax references.

# Installation

Add the dependency to composer.json:

```json
{
    "require": {
        "dri-nordic/vardef-modifier": "~2.0@dev"
    },
    "repositories": [
        {
            "type": "composer",
            "url":  "https://packages.dricrm.com/"
        }
    ]
}
```

And run:

```bash
composer install
```

Composer will install the library to your project's `vendor/dri-nordic/vardef-modifier` directory.

# Usage

Run the help command for full documentation about available commands:

```bash
bin/vardef-modifier help
```

## install

Run the help command as the command name as the first parameter for full documentation about the command:

```bash
bin/vardef-modifier help install
```

### Install in custom module

```bash
bin/vardef-modifier install -m DRI_Invoices -c
```

### Install in core module

```bash
bin/vardef-modifier install -m Accounts
```

## dump

### normal usage

```bash
bin/vardef-modifier dump DRI_Workflows modules/DRI_Workflows/vardefs.yml dri-customer-journey
```

### use the dump command from outside of Sugar

```bash
bin/vardef-modifier dump -T ~/www/sugarcrm-7.6.0.0 DRI_Workflows modules/DRI_Workflows/vardefs.yml dri-customer-journey
```

## migrate

NOT FINISHED

### List files that needs to be migrated

```bash
bin/vardef-modifier migrate --find
```

### Attempt to automatically migrate files

```bash
bin/vardef-modifier migrate --write
```