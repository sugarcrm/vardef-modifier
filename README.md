sugarcrm-vardef-modifier
========

About
---------------------

 * __Author:__ Emil Kilhage
 * __Date Created:__ 2012-04-24
 * __License:__ MIT

See vardefs.example.yml for some syntax references.

Installation
---------------------

# Install in project

git submodule add gitolite@gitlab.dri-nordic.com:dri-nordic/vardef-modifier docroot/custom/include/VardefModifier

# Install in module

## Install in custom module

php docroot/custom/include/VardefModifier/install.php DRI_Invoices -c

## Install in core module

php docroot/custom/include/VardefModifier/install.php Accounts

Todo
---------------------

 * Make it possible to install as composer dependency
 * Make it possible to export .yml definitions to php files