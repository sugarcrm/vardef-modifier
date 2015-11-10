<?php

namespace DRI\SugarCRM\VardefModifier\Command;

use DRI\SugarCRM\Bootstrap\Bootstrap;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * @author Emil Kilhage
 */
abstract class AbstractCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addOption('target', 'T', InputOption::VALUE_OPTIONAL, 'target sugar path that should be used as context, defaults to the current working directory');
    }

    /**
     * @param InputInterface $input
     * @return mixed|null
     */
    protected function bootstrap(InputInterface $input)
    {
        $path = $input->hasOption('target') ? $input->getOption('target') : null;

        Bootstrap::boot($path);

        return $path;
    }
}
