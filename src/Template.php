<?php

namespace DRI\SugarCRM\VardefModifier;

/**
 * @author Emil Kilhage
 */
class Template
{
    /**
     * @var string
     */
    private $templatePath;

    /**
     * Template constructor.
     */
    public function __construct()
    {
        $this->templatePath = sprintf('%s/Resources/tpls', __DIR__);
    }

    /**
     * @param string $name
     * @param array  $context
     * @return string
     */
    public function render($name, array $context = array())
    {
        $twig = $this->getTwig();

        return $twig->render($name, $context);
    }

    /**
     * @return \Twig_Environment
     */
    private function getTwig()
    {
        return new \Twig_Environment(new \Twig_Loader_Filesystem($this->templatePath));
    }
}
