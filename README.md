vardef-modifier
========

# About

 * __Author:__ Emil Kilhage
 * __Date Created:__ 2012-04-24
 * __License:__ MIT

See vardefs.example.yml for some syntax references.

# Installation

Require the library with composer:

```bash
composer require dri-nordic/vardef-modifier "~2.0@dev"
```

Composer will install the library to your project's `vendor/dri-nordic/vardef-modifier` directory.

# Usage

Run the help command for full documentation about available commands:

```bash
php bin/vardef-modifier help
```

Run the help command as the command name as the first parameter for full documentation about the command:

```bash
php bin/vardef-modifier help install
```

## Install in custom module

```bash
php bin/vardef-modifier install -m DRI_Invoices -c
```

## Install in core module

```bash
php bin/vardef-modifier install -m Accounts
```