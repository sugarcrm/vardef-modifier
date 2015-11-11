UPGRADE FROM 1.x to 2.0
=======================

### Composer support

In v3.0 the VardefModifier can be included as a composer dependency

   ```json
    {
        "require": {
            "dri-nordic/vardef-modifier": "~2.0"
        },
        "repositories": [
            {
                "type": "composer",
                "url":  "https://packages.dricrm.com/"
            }
        ]
    }
   ```

### Submodule no longer supported

If you have these situations where you can not include the library as a composer dependency you should rather use this tool from outside of the project and dump the vardef definitions trough the `dump` command

If you still need to include the library as a submodule, please make sure that the `1.2` branch is used and not `master` or any `2.0` branch as this may break existing code.

### Namespace

The VardefModifier class has been moved into its own namespace and is now referenced like this

    \DRI\SugarCRM\VardefModifier\VardefModifier

### Commands

All commands previously accessed in the root of the repo has been moved into using symfony's console framework

The old install command was used like this:

   ```bash
   install.php Accounts Contacts
   install.php DRI_Config --core
   ```

The new command is used like this:

   ```bash
   bin/vardef-modifier install -m Accounts -m Contacts
   bin/vardef-modifier install -m DRI_Config --core
   ```

Here is the full help of the new install command, run this command your self for the latest help, also look at the other documentation for a more complete overview of this

   ```bash

    bin/vardef-modifier help install

    Usage:
      install [options]
    
    Options:
      -F, --force
      -C, --core
      -D, --dry
      -Y, --only-yml
      -P, --only-php
      -m, --module=MODULE     (multiple values allowed)
      -N, --name[=NAME]
      -T, --target[=TARGET]  target sugar path that should be used as context, defaults to the current working directory
      -h, --help             Display this help message
      -q, --quiet            Do not output any message
      -V, --version          Display this application version
          --ansi             Force ANSI output
          --no-ansi          Disable ANSI output
      -n, --no-interaction   Do not ask any interactive question
      -v|vv|vvv, --verbose   Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
    
    Help:
     Installs an empty yaml vardef addition for a module

   ```

A few new commands has also been added, to see what is available run:

    bin/vardef-modifier help

### Migration

Use the migration command to find and reinstall the vardef inclusions of the library

#### List files that needs to be migrated

    bin/vardef-modifier migrate --find

#### Attempt to automatically migrate files

    bin/vardef-modifier migrate --write
