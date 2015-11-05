<?php

namespace DRI\SugarCRM\VardefModifier;

/**
 * @author Emil Kilhage
 */
class Installer
{
    /**
     * @var bool
     */
    private $core = false;

    /**
     * @var bool
     */
    private $force = false;

    /**
     * @var bool
     */
    private $dry = false;

    /**
     * @var bool
     */
    private $onlyYml = false;

    /**
     * @var bool
     */
    private $onlyPhp = false;

    /**
     * @var array
     */
    private $modules = array();

    /**
     * @param boolean $force
     */
    public function setForce($force)
    {
        $this->force = $force;
    }

    /**
     * @param boolean $core
     */
    public function setCore($core)
    {
        $this->core = $core;
    }

    /**
     * @param boolean $dry
     */
    public function setDry($dry)
    {
        $this->dry = $dry;
    }

    /**
     * @param boolean $onlyYml
     */
    public function setOnlyYml($onlyYml)
    {
        $this->onlyYml = $onlyYml;
    }

    /**
     * @param boolean $onlyPhp
     */
    public function setOnlyPhp($onlyPhp)
    {
        $this->onlyPhp = $onlyPhp;
    }

    /**
     * @param array $modules
     */
    public function setModules(array $modules)
    {
        $this->modules = $modules;
    }

    /**
     * @throws Exception
     */
    public function install()
    {
        if (count($this->modules) === 0) {
            throw new Exception('Missing modules');
        }

        foreach ($this->modules as $module) {
            echo "* Installing $module \n";
            if (!$this->onlyYml) {
                $this->writePhpFile($module);
            }

            if (!$this->onlyPhp) {
                $this->writeYamlFile($module);
            }
        }
    }

    /**
     * @param string $module
     * @return string
     */
    private function getYamlTemplate($module)
    {
        $file = file_get_contents(dirname(__DIR__).'/vardefs.template.yml');

        return str_replace('$module', $module, $file);
    }

    /**
     * @param string $module
     * @return string
     */
    private function getPhpTemplate($module)
    {
        $class_name = __CLASS__;

        return <<<PHP
<?php

/* Installed by \\$class_name */
if (!isset(\$dictionary) || !is_array(\$dictionary))
    global \$dictionary;
{$this->getPhpCode($module)}
/* End installation */

PHP;
    }

    /**
     * @param string $module
     * @return string
     */
    private function getPhpCode($module)
    {
        return <<<PHP
if (class_exists('\DRI\SugarCRM\VardefModifier\VardefModifier')) {
    \$dictionary = \DRI\SugarCRM\VardefModifier\VardefModifier::modify("$module", \$dictionary)->
        yaml("{$this->getYamlFilePath($module)}")->
        get();
}
PHP;
    }

    /**
     * @param string $module
     * @return string
     */
    private function getPhpCoreVardefAddition($module)
    {
        $class_name = __CLASS__;

        return <<<PHP
/* Installed by $class_name */
{$this->getPhpCode($module)}
/* End installation */

PHP;
    }

    /**
     * @param string $module
     * @return string
     */
    private function getYamlFilePath($module)
    {
        $dir = $this->core ? "modules/$module" : "custom/modules/$module";
        is_dir($dir) or mkdir($dir, 0755, true);

        return "$dir/vardefs.yml";
    }

    /**
     * @param string $module
     * @return string
     */
    private function getPhpFilePath($module)
    {
        $dir = $this->core ?
            "modules/$module" :
            "custom/Extension/modules/$module/Ext/Vardefs";

        is_dir($dir) or mkdir($dir, 0755, true);

        return "$dir/".($this->core ? 'vardefs.php' : 'yaml_vardefs.php');
    }

    /**
     * @param string $module
     */
    private function writePhpFile($module)
    {
        if (!$this->core) {
            $file_path = $this->getPhpFilePath($module);
            if ($this->force || !file_exists($file_path)) {
                echo "Creating: $file_path \n";
                $this->write($file_path, $this->getPhpTemplate($module));
            } else {
                echo "$file_path does already exists, skipping... \n";
            }
        } else {
            $filename = $this->getPhpFilePath($module);
            $content = file_get_contents($filename);
            if ($this->force || strpos($content, 'VardefModifier::modify(') === false) {
                echo "Installing in $filename \n";
                $content = rtrim($content, "?>\n");
                $content .= "\n\n";
                $content .= $this->getPhpCoreVardefAddition($module);
                $this->write($filename, $content);
            } else {
                echo "$filename is already installed, skipping... \n";
            }
        }
    }

    /**
     * @param string $module
     */
    private function writeYamlFile($module)
    {
        $file_path = $this->getYamlFilePath($module);
        if ($this->force || !file_exists($file_path)) {
            echo "Creating: $file_path \n";
            $this->write($file_path, $this->getYamlTemplate($module));
        } else {
            echo "$file_path does alredy exists, skipping... \n";
        }
    }

    /**
     * @param string $path
     * @param string $content
     */
    private function write($path, $content)
    {
        if (!$this->dry) {
            file_put_contents($path, $content);
        }
    }
}
