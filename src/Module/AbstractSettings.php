<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

namespace Onlineshopmodule\PrestaShop\Module\Facetedsearch\Module;

use Doctrine\DBAL\Connection;
use Monolog\Logger;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Cron\CronQueueRepository;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Settings\Config;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Settings\Controller;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Settings\Hook;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Settings\Orderstate;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Settings\Sql;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Settings\Tab;
use OrderState as PS_OrderState;
use PrestaShop\PrestaShop\Core\MailTemplate\ThemeCatalogInterface;

abstract class AbstractSettings
{
    protected $connection;
    protected $dbPrefix = '';
    protected $module;
    protected $translator;

    public function __construct(
        \GC_Facetedsearch $module,
        Connection $connection,
        string $dbPrefix
    ) {
        $this->module = $module;
        $this->connection = $connection;
        $this->dbPrefix = $dbPrefix;
        $this->translator = $this->module->getTranslator();
    }

    abstract public function config(): array;

    abstract public function controllers(): array;

    abstract public function cron(): array;

    abstract public function hooks(): array;

    abstract public function orderStates(): array;

    abstract public function sql(): Sql;

    abstract public function tabs(): array;

    abstract public function translations(): array;

    abstract public function fixtures(): callable;

    abstract public function help(): string;

    public function getConfig(): array
    {
        $config['LICENSE'] = new Config('LICENSE', '', true);
        $config['PRIVACY'] = new Config('PRIVACY', 0, true);
        $config['LOG_LEVEL'] = new Config('LOG_LEVEL', Logger::WARNING, true);

        if ($this->module->hasJavascriptFiles()) {
            $config['JS_DEFER'] = new Config('JS_DEFER', false);
        }

        if ($this->module->isPayment()) {
            $paymentTitle = [];
            $paymentText = [];

            foreach (\Language::getLanguages() as $language) {
                $paymentTitle[$language['iso_code']] = $this->module->displayName;
                $paymentText[$language['iso_code']] = '<p>' . $this->translator->trans(
                    'You pay with %module_name%',
                    ['%module_name%' => $this->module->displayName],
                    'Modules.Gcfacetedsearch.Shop',
                    $language['locale']
                ) . '</p>';
            }

            $config['SHOW_PAYMENT_LOGO'] = new Config('SHOW_PAYMENT_LOGO', true);
            $config['PAYMENT_LOGO'] = new Config('PAYMENT_LOGO', 'payment.png');
            $config['PAYMENT_TITLE'] = new Config('PAYMENT_TITLE', $paymentTitle);
            $config['PAYMENT_TEXT'] = new Config('PAYMENT_TEXT', $paymentText, true);
        }

        if ($this->module->hasCronjobs()) {
            $config['CRON_MAINTENANCE'] = new Config('CRON_MAINTENANCE', false);
            $config['CRON_ROWS_PER_RUN'] = new Config('CRON_ROWS_PER_RUN', 1);
        }

        if (
            $this->module->hasHummingbirdTemplates()
            || $this->module->hasHummingbirdJavascriptOrStylesheets()
        ) {
            $config['THEME'] = new Config('THEME', 'classic');
        }

        $config = array_merge($config, $this->config());

        return $config;
    }

    public function getControllers(): array
    {
        $controllers = $this->controllers();

        $additionalControllers = [];

        if ($this->module->isPayment()) {
            $additionalControllers[] = new Controller('payment');
        }

        if ($this->module->hasCronjobs()) {
            $additionalControllers[] = new Controller('cron');
        }

        if (is_file($this->module->getLocalPath() . 'controllers/front/ajax.php')) {
            $additionalControllers[] = new Controller('ajax');
        }

        $controllers = array_merge($controllers, $additionalControllers);

        $controllers = $this->convertToInstance(Controller::class, $controllers);

        return $controllers;
    }

    public function getCron(): array
    {
        return $this->cron();
    }

    public function isUsingCron(): bool
    {
        return !empty($this->getCron());
    }

    public function isCronUsingQueue(): bool
    {
        foreach ($this->getCron() as $methodName => $cron) {
            if (!empty($cron['use_queue'])) {
                return true;
            }
        }

        return false;
    }

    public function getHooks(): array
    {
        $hooks = [];

        $hooks['actionAdminControllerSetMedia'] = new Hook('actionAdminControllerSetMedia');

        if (
            $this->module->hasJavascriptFiles(true)
            || $this->module->hasStylesheetFiles(true)
        ) {
            $hooks['actionFrontControllerSetMedia'] = new Hook('actionFrontControllerSetMedia');
        }

        if ($this->module->isPayment()) {
            $hooks['paymentOptions'] = new Hook('paymentOptions');
            $hooks['displayPaymentReturn'] = new Hook('displayPaymentReturn');
            $hooks['actionGetExtraMailTemplateVars'] = new Hook('actionGetExtraMailTemplateVars');
            $hooks['actionEmailSendBefore'] = new Hook('actionEmailSendBefore');
        }

        if (
            is_dir(_PS_MODULE_DIR_ . $this->module->name . '/mails/themes/modern')
            || is_dir(_PS_MODULE_DIR_ . $this->module->name . '/mails/themes/classic')
        ) {
            $hooks[ThemeCatalogInterface::LIST_MAIL_THEMES_HOOK] = new Hook(ThemeCatalogInterface::LIST_MAIL_THEMES_HOOK);
        }

        $hooks = array_merge($hooks, $this->hooks());

        return $hooks;
    }

    public function getOrderStates(): array
    {
        if (
            !$this->module->isPayment()
            || !$this->module->installOrderStates
        ) {
            return $this->orderStates();
        }

        $config = $this->module->getConfig();

        $idOrderState = (int) $config->get('OS');

        $osObject = new PS_OrderState($idOrderState);

        if (!\Validate::isLoadedObject($osObject)) {
            $osObject->name = [];
            $osObject->template = [];

            $sendEmail = false;

            foreach (\Language::getLanguages() as $language) {
                $osObject->name[$language['id_lang']] = $this->translator->trans('Awaiting payment: Facetedsearch', [], 'Modules.Gcfacetedsearch.Admin');

                if (is_file($this->module->getLocalPath() . 'mails/' . $language['iso_code'] . '/' . $this->module->name . '_payment.html')) {
                    $osObject->template[$language['id_lang']] = $this->module->name . '_payment';

                    $sendEmail = $this->module->sendMailOrderStatusAwaitingPayment;
                }
            }

            $osObject->send_email = $sendEmail;
            $osObject->module_name = $this->module->name;
            $osObject->invoice = false;
            $osObject->color = '#4169e1';
            $osObject->unremovable = false;
            $osObject->logable = false;
            $osObject->delivery = false;
            $osObject->hidden = false;
            $osObject->shipped = false;
            $osObject->paid = false;
            $osObject->deleted = false;
            $osObject->pdf_invoice = false;
            $osObject->pdf_delivery = false;
        }

        $orderStates['awaiting_payment'] = new Orderstate(
            $this->translator->trans('Awaiting Payment', [], 'Modules.Gcfacetedsearch.Admin'),
            'awaiting_payment',
            $osObject
        );

        $idOrderState = (int) \Configuration::get('OS_NEWORDER');

        $osObject = new PS_OrderState($idOrderState);

        if (!\Validate::isLoadedObject($osObject)) {
            $osObject->name = [];
            $osObject->template = [];

            foreach (\Language::getLanguages() as $language) {
                $osObject->name[$language['id_lang']] = $this->translator->trans('Order placed', [], 'Modules.Gcfacetedsearch.Shop');
            }

            $osObject->send_email = false;
            $osObject->module_name = '';
            $osObject->invoice = false;
            $osObject->color = '#FF8C00';
            $osObject->unremovable = true;
            $osObject->logable = false;
            $osObject->delivery = false;
            $osObject->hidden = true;
            $osObject->shipped = false;
            $osObject->paid = false;
            $osObject->deleted = false;
            $osObject->pdf_invoice = false;
            $osObject->pdf_delivery = false;
        }

        $orderStates['new_order'] = new Orderstate(
            $this->translator->trans('Order placed', [], 'Modules.Gcfacetedsearch.Admin'),
            'new_order',
            $osObject
        );

        if ($parentOrderStates = $this->orderStates()) {
            $orderStates = array_merge($orderStates, $parentOrderStates);
        }

        return $orderStates;
    }

    public function getSql(): Sql
    {
        $sql = $this->sql();
        $schema = $sql->getSchema();

        if ($this->isCronUsingQueue()) {
            try {
                /**
                 * @var CronQueueRepository $cronQueueRepository
                 */
                $cronQueueRepository = $this->module->get('onlineshopmodule.module.facetedsearch.cronqueuerepository');
            } catch (\Throwable $e) {
                $cronQueueRepository = new CronQueueRepository(
                    $this->module->get('doctrine.dbal.default_connection'),
                    $this->dbPrefix,
                    $this->module
                );
            }

            $table = $schema->createTable($cronQueueRepository->getTableName());
            $table->addColumn('id_cron_queue', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $table->addColumn('value', 'string', ['length' => 128]);
            $table->addColumn('type', 'string', ['length' => 32, 'notnull' => false]);
            $table->addColumn('date_add', 'datetime');
            $table->addColumn('executed', 'integer', ['default' => 0]);
            $table->addColumn('runtime', 'integer', ['unsigned' => true, 'default' => 0]);
            $table->addColumn('priority', 'integer', ['unsigned' => true, 'default' => 1]);
            $table->setPrimaryKey(['id_cron_queue']);
            $table->addIndex(['executed']);
            $table->addIndex(['date_add']);
            $table->addIndex(['priority']);
            $table->addIndex(['value', 'executed', 'type']);
        }

        return $sql;
    }

    public function getTabs(): array
    {
        $defaultTabs = [];
        $defaultTabs[] = Tab::buildFromArray([
            'class_name' => 'GcFacetedsearchConfigurationAdminParentController',
            'route_name' => 'gc_facetedsearch',
            'icon' => '',
            'wording' => 'GC Facetedsearch',
            'wording_domain' => 'Modules.Gcfacetedsearch.Admin',
            'visible' => false,
            'parent_class_name' => 'AdminParentModulesSf',
            'name' => [
                'en' => 'GC Facetedsearch',
                'de' => $this->translator->trans('GC Facetedsearch', [], 'Modules.Gcfacetedsearch.Admin'),
            ],
        ]);

        $defaultTabs[] = Tab::buildFromArray([
            'class_name' => 'GcFacetedsearchConfigurationAdminController',
            'route_name' => 'gc_facetedsearch_configuration',
            'icon' => 'settings',
            'wording' => 'Configuration',
            'wording_domain' => 'Modules.Gcfacetedsearch.Admin',
            'visible' => true,
            'parent_class_name' => 'GcFacetedsearchConfigurationAdminParentController',
            'name' => [
                'en' => 'Configuration',
                'de' => 'Einstellungen',
            ],
        ]);

        $tabs = $this->tabs();

        if (!empty($tabs)) {
            $defaultTabs = array_merge($defaultTabs, $tabs);
        }

        if ($this->module->isPayment()) {
            $defaultTabs[] = Tab::buildFromArray([
                'class_name' => 'GcFacetedsearchPaymentAdminController',
                'route_name' => 'gc_facetedsearch_payment',
                'icon' => 'credit_card',
                'wording' => 'Payment',
                'wording_domain' => 'Modules.Gcfacetedsearch.Admin',
                'visible' => true,
                'parent_class_name' => 'GcFacetedsearchConfigurationAdminParentController',
                'name' => [
                    'en' => 'Payment',
                    'de' => 'Status & Zahlung',
                ],
            ]);
        }

        if ($this->isUsingCron()) {
            $defaultTabs[] = Tab::buildFromArray([
                'class_name' => 'GcFacetedsearchCronAdminController',
                'route_name' => 'gc_facetedsearch_cron',
                'icon' => 'alarm',
                'wording' => 'Cron',
                'wording_domain' => 'Modules.Gcfacetedsearch.Admin',
                'visible' => true,
                'parent_class_name' => 'GcFacetedsearchConfigurationAdminParentController',
                'name' => [
                    'en' => 'Cron',
                    'de' => 'Cron',
                ],
            ]);
        }

        $defaultTabs[] = Tab::buildFromArray([
            'class_name' => 'GcFacetedsearchLogsAdminController',
            'route_name' => 'gc_facetedsearch_logs',
            'icon' => 'assignment',
            'wording' => 'Logs',
            'wording_domain' => 'Modules.Gcfacetedsearch.Admin',
            'visible' => true,
            'parent_class_name' => 'GcFacetedsearchConfigurationAdminParentController',
            'name' => [
                'en' => 'Logs',
                'de' => 'Logs',
            ],
        ]);

        $defaultTabs[] = Tab::buildFromArray([
            'class_name' => 'GcFacetedsearchMaintenanceAdminController',
            'route_name' => 'gc_facetedsearch_maintenance',
            'icon' => 'handyman',
            'wording' => 'Maintenance',
            'wording_domain' => 'Modules.Gcfacetedsearch.Admin',
            'visible' => true,
            'parent_class_name' => 'GcFacetedsearchConfigurationAdminParentController',
            'name' => [
                'en' => 'Maintenance',
                'de' => 'Wartung',
            ],
        ]);

        $defaultTabs[] = Tab::buildFromArray([
            'class_name' => 'GcFacetedsearchLicenseAdminController',
            'route_name' => 'gc_facetedsearch_license',
            'icon' => 'contract',
            'wording' => 'License',
            'wording_domain' => 'Modules.Gcfacetedsearch.Admin',
            'visible' => false,
            'parent_class_name' => 'GcFacetedsearchConfigurationAdminParentController',
            'name' => [
                'en' => 'License',
                'de' => 'Lizenz',
            ],
        ]);

        return $defaultTabs;
    }

    public function getSqlInstall(): array
    {
        $schema = $this->getSql()->getSchema();

        return $schema->toSql(
            $this->connection->getDatabasePlatform()
        );
    }

    public function getSqlUninstall(): array
    {
        $schema = $this->getSql()->getSchema();

        return $schema->toDropSql(
            $this->connection->getDatabasePlatform()
        );
    }

    public function getTranslations()
    {
        return $this->translations();
    }

    public function getFixtures(): callable
    {
        return $this->fixtures();
    }

    public function getHelp(): string
    {
        return $this->help();
    }

    public function getAll(): array
    {
        return [
            'config' => $this->getConfig(),
            'controllers' => $this->getControllers(),
            'cron' => $this->getCron(),
            'fixtures' => $this->getFixtures(),
            'hooks' => $this->getHooks(),
            'orderstates' => $this->getOrderStates(),
            'sql' => $this->getSql(),
            'tabs' => $this->getTabs(),
            'translations' => $this->getTranslations(),
            'help' => $this->getHelp(),
        ];
    }

    private function convertToInstance($className, array $instances): array
    {
        return array_map(function ($instance) use ($className) {
            if (!($instance instanceof $className)) {
                return new $className($instance);
            }

            return $instance;
        }, $instances);
    }
}
