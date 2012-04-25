<?php

/**
 * Should be executed from cli
 * @author Emil Kilhage
 */

$help = "
****************************************************
Installs an empty yaml vardef addition for a module
****************************************************
\$ php install.php [module] [module] [module] [....]

";

php_sapi_name() === 'cli' or die("Could only be executed from cli!");
isset($argv[1]) or die($help);

$dir = __DIR__;

while (1) {
    if (file_exists($dir . '/sugar_version.php')) break;
    $dir = dirname($dir);
    $dir !== '/' or die("Unable to find SugarCrm root.");
}

chdir($dir);
define("sugarEntry", true);
require_once 'include/entryPoint.php';

$modifier_dir = str_replace("$dir/", '', __DIR__);

global $beanList;

$date = date('Y-m-d');

foreach (array_slice($argv, 1) as $module)
{
    if (!isset($beanList[$module]))
        die("Invalid module: $module \n");

    echo "* Installing $module \n";

    $php_dir = "custom/Extension/modules/$module/Ext/Vardefs";
    $php_path = "$php_dir/yaml_vardef.php";

    $yml_dir = "custom/modules/$module";
    $yml_path = "$yml_dir/vardefs.yml";

    $php_file = <<<PHP
<?php

/*********************************************
 * Module: $module
 ********************************************/
require_once '$modifier_dir/VardefModifier.php';
\$dictionary = VardefModifier::modify("$module", \$dictionary)->
    yaml("$yml_path")->
    get();


PHP;
    $yml_file = <<<YAML
################################################
# Module: $module
################################################
---

YAML;

    echo "Creating: $php_path \n";
    is_dir($php_dir) or mkdir($php_dir. 0755, true);
    file_put_contents($php_path, $php_file);

    echo "Creating: $yml_path \n";
    is_dir($yml_dir) or mkdir($yml_dir. 0755, true);
    file_put_contents($yml_path, $yml_file);
}

echo "\n* Done!\n";
