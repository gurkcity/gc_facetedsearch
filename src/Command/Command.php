<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

namespace Onlineshopmodule\PrestaShop\Module\Facetedsearch\Command;

use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Exception\CronException;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Command extends SymfonyCommand
{
    protected string $action ='execute';

    public function __construct(
        protected \GC_Facetedsearch $module
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName($this->module->name . ':' . $this->action)
            ->setDescription(sprintf('Executes command %s for %s', $this->action, $this->module->displayName));
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /**
         * Do Something
         */

        if (!$this->action || !method_exists($this->module, $this->action)) {
            throw new CronException(sprintf('Cron action \'%s\' corrupt.', $this->action));
        }

        $io->success('Test');

        return SymfonyCommand::SUCCESS;
    }
}
