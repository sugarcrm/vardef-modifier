<?php

namespace DRI\SugarCRM\VardefModifier\Command;

use DRI\SugarCRM\VardefModifier\VardefModifier;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Emil Kilhage
 */
class DumpCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dump');
        $this->addArgument('module', InputArgument::REQUIRED, '');
        $this->addArgument('yml', InputArgument::REQUIRED, '');
        $this->addArgument('name', InputArgument::REQUIRED, '');
        $this->setDescription('');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->bootstrap($input);

        global $beanList;

        $moduleName = $input->getArgument('module');
        $fileName = $input->getArgument('yml');
        $targetFileName = $input->getArgument('name');

        $vm = VardefModifier::modify($moduleName, array());
        $vm->yaml($fileName);

        $objectName = $beanList[$moduleName];

        $dictionaryKey = $vm->getDictionaryKey();

        $dic = $vm->get();

        $templatePath = dirname(__DIR__);

        $fs = new Filesystem();
        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem("$templatePath/Resources/tpls"));

        $definitions = array(
            'fields' => isset($dic[$dictionaryKey]['fields']) ? $dic[$dictionaryKey]['fields'] : array(),
            'relationships' => isset($dic[$dictionaryKey]['relationships']) ? $dic[$dictionaryKey]['relationships'] : array(),
            'indices' => isset($dic[$dictionaryKey]['indices']) ? $dic[$dictionaryKey]['indices'] : array(),
        );

        $arguments = array(
            'dictionaryKey' => $dictionaryKey,
            'objectName' => $objectName,
            'moduleName' => $moduleName,
            'fileName' => $fileName,
            'fields' => array(),
            'relationships' => array(),
            'indices' => array(),
        );

        foreach ($definitions as $type => $sub) {
            foreach ($sub as $name => $def) {

                $isChange = $type === 'fields' && !isset($def['name']);

                if ($isChange) {
                    foreach ($def as $key => $value) {
                        $def[$key] = var_export($value, true);
                    }
                } else {
                    $def = var_export($def, true);;
                }

                $arguments[$type][$name] = array(
                    'name' => $name,
                    'def' => $def,
                    'isChange' => $isChange,
                );
            }
        }

        if (empty($arguments['fields']) && empty($arguments['relationships']) && empty($arguments['indices'])) {
            return;
        }

        $targetFilePath = "custom/Extension/modules/$moduleName/Ext/Vardefs/$targetFileName.php";

        $output->writeln("Writing vardef to $targetFilePath");

        $content = $twig->render('module/vardefs/vardef.php.twig', $arguments);

        $content = trim($content)."\n";

        $fs->dumpFile($targetFilePath, $content);
    }
}
