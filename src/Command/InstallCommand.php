<?php

namespace DRI\SugarCRM\VardefModifier\Command;

use DRI\SugarCRM\Bootstrap\Bootstrap;
use DRI\SugarCRM\VardefModifier\Installer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Emil Kilhage
 */
class InstallCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('install');

        $this->addOption('force', 'F', InputOption::VALUE_NONE);
        $this->addOption('core', 'C', InputOption::VALUE_NONE);
        $this->addOption('dry', 'D', InputOption::VALUE_NONE);
        $this->addOption('only-yml', 'Y', InputOption::VALUE_NONE);
        $this->addOption('only-php', 'P', InputOption::VALUE_NONE);
        $this->addOption('target', 'T', InputOption::VALUE_OPTIONAL);

        $this->addOption('module', 'm', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY);
        $this->addOption('name', 'N', InputOption::VALUE_OPTIONAL);

        $this->setDescription('Installs an empty yaml vardef addition for a module');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->hasOption('target') ? $input->getOption('target') : null;

        Bootstrap::boot($path);

        $installer = new Installer($path);

        $installer->setCore($input->getOption('core'));
        $installer->setForce($input->getOption('force'));
        $installer->setDry($input->getOption('dry'));
        $installer->setModules($input->getOption('module'));
        $installer->setName($input->getOption('name'));

        $installer->install();
    }
}
