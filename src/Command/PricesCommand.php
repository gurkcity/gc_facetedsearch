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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PricesCommand extends Command
{
    protected string $action ='indexPrices';

    protected function configure(): void
    {
        parent::configure();

        $this->addArgument('full', InputArgument::OPTIONAL, 'Full reindex flag');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $methodName = 'cron' . ucfirst($this->action);

        if (!$this->action || !method_exists($this->module, $methodName)) {
            $io->error(sprintf('Cron action \'%s\' is not supported by module %s', $this->action, $this->module->displayName));

            return SymfonyCommand::FAILURE;
        }

        try {
            $full = (bool) $input->getArgument('full');

            $this->module->{$methodName}($full);
        } catch (CronException $e) {
            $io->error(sprintf('An error occurred while executing cron action \'%s\': %s', $this->action, $e->getMessage()));

            return SymfonyCommand::FAILURE;
        }

        $io->success(sprintf('Executing cron action \'%s\'...', $this->action));

        return SymfonyCommand::SUCCESS;
    }
}
