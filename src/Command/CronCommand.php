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

use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Cron\CronExecuter;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Cron\CronQueueRepository;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Exception\CronException;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CronCommand extends Command
{
    public const CRON_RUNTIME_MAX = 360;

    public function __construct(
        protected \GC_Facetedsearch $module,
        protected CronQueueRepository $queueRepository
    ) {
        parent::__construct($module);
    }

    protected function configure(): void
    {
        $moduleName = $this->module->name;
        $moduleTitle = $this->module->displayName;

        $settings = $this->module->getSettings();
        $cronjobs = $settings->getCron();

        $actionNames = [];

        foreach ($cronjobs as $methodName => $cron) {
            $actionNames[] = $cron['command']['arguments']['action'] ?? $methodName;
        }

        $this
            ->setName($moduleName . ':cron')
            ->setDescription('Executes scheduled tasks for the module ' . $moduleTitle)
            ->addArgument('action', InputArgument::REQUIRED, 'The action to execute: ' . implode(', ', $actionNames))
            ->addArgument('force', InputArgument::OPTIONAL, 'Force the cron to run regardless of current state.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $action = $input->getArgument('action');
        $forceCron = (bool) $input->getArgument('force');

        $logger = $this->module->getLogger()->cron;

        try {
            $moduleCronActions = $this->module->getSettings()->getCron();

            $logger->info('Run cron');
            $logger->info('Action ' . $action);

            if ($forceCron) {
                $logger->notice('Run with force');
            }

            $this->secureCheck($action, $moduleCronActions);

            $logger->info(sprintf('Execute action %s ', $action));

            if (!$forceCron && $this->isCronRunning()) {
                throw new CronException('Cron already running!');
            }

            if (!$forceCron) {
                $this->startCron();
            }

            if ($moduleCronActions[$action]['use_queue'] ?? false) {
                $logger->notice('Using cron queue');

                $cronRuntime = (int) ($this->module->getConfig()->get('CRON_RUNTIME') ?: 1);
                $cronRuntime *= 60;

                $logger->info(sprintf('Execute with runtime of %s seconds', $cronRuntime));

                $cronExecuter = new CronExecuter(
                    $this->queueRepository,
                    $cronRuntime,
                    $logger
                );

                $result = $cronExecuter->run([$this->module, $action]);
            } else {
                $logger->notice('Run single cron action');

                $result = call_user_func([$this->module, $action], $logger, $io);
            }

            $this->endCron();

            if ($result) {
                $io->success('Cron executed successfully.');
            }

            $logger->info('Cron success');
        } catch (CronException $e) {
            $logger->error($e->getMessage());

            $io->error('Cron execution failed: ' . $e->getMessage());
        } catch (\Exception $e) {
            $logger->error($e->getMessage());

            $io->error('Cron execution failed: ' . $e->getMessage());
        }

        return Command::SUCCESS;
    }

    private function secureCheck(string $action, array $moduleCronActions)
    {
        if (!$action || !method_exists($this->module, $action)) {
            throw new CronException(sprintf('Cron action \'%s\' corrupt.', $action));
        }

        if (!in_array($action, array_keys($moduleCronActions))) {
            throw new CronException(sprintf('Cron action \'%s\' is not available.', $action));
        }
    }

    private function isCronRunning(): bool
    {
        $cronRunning = (int) $this->module->getConfig()->get('CRON_RUNNING');

        if (!$cronRunning) {
            return false;
        }

        if (time() < ($cronRunning + self::CRON_RUNTIME_MAX)) {
            return true;
        }

        return false;
    }

    private function startCron()
    {
        $this->module->getConfig()->set('CRON_RUNNING', time());
    }

    private function endCron()
    {
        $this->module->getConfig()->set('CRON_RUNNING', 0);
    }
}
