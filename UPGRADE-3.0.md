UPGRADE FROM 2.x to 3.0
=======================

### Composer support

In v3.0 the VardefModifier can be included as a composer dependency

This can be done like this:

   ```json
    {
        // ...
        "require": {
            // ...
            "dri-nordic/vardef-modifier": "~2.0"
        },
        // ...
        "repositories": [
            {
                "type": "composer",
                "url":  "https://packages.dricrm.com/"
            }
        ],
        // ...
    }
   ```

### Submodule no longer supported

If you have these situations where you can not include the library as a composer dependency you should rather use this tool from outside of the project and dump the vardef definitions trough the `dump` command

### Namespace

The VardefModifier class has been moved into its own namespace and is now referenced like this

    \DRI\SugarCRM\VardefModifier\VardefModifier

### Commands

All commands previously accessed in the root of the repo has been moved into using symfony's console framework

   | Old Command | New Command
   | -------- | ---
   | `install.php Accounts` | `bin/vardef-modifier install Accounts`

A few new commands has also been added, to see what is available run:

    bin/vardef-modifier help

### Migration

Use the migration command to find and reinstall the vardef inclusions of the library

#### List files that needs to be migrated

    bin/vardef-modifier migrate --find

#### Attempt to automatically migrate files

    bin/vardef-modifier migrate --write
