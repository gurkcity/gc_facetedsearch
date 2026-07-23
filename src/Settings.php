<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

namespace Onlineshopmodule\PrestaShop\Module\Facetedsearch;

use Doctrine\DBAL\Schema\Schema;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Module\AbstractSettings;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Settings\Config;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Settings\Controller;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Settings\Hook;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Settings\Sql;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Settings\Tab;

class Settings extends AbstractSettings
{
    public function config(): array
    {
        return [];

        /*
        return [
            'TEST_CONFIG' => new Config(
                'TEST_CONFIG',
                [
                    'en' => 'Value',
                    'de' => 'Wert',
                ]
            ),
            'TEST_CONFIG_ABC' => new Config(
                'TEST_CONFIG_ABC',
                1
            ),
            'TEST_CONFIG_ABC' => new Config(
                'TEST_CONFIG_ABC',
                'value'
            ),
        ];
        */
    }

    public function controllers(): array
    {
        return [];

        /*
        return [
            new Controller('controller_name'),
        ];
        */
    }

    public function cron(): array
    {
        return [];

        /*
        return [
            'cronMethodName' => [
                'title' => $this->translator->trans('Name of the cronjob', [], 'Modules.Gcfacetedsearch.Admin'),
                'description' => $this->translator->trans('This cron job does super cool things in your webshop.', [], 'Modules.Gcfacetedsearch.Admin'),
                'use_queue' => true,
                'params' => [
                    [
                        'description' => $this->translator->trans('Description for parameter', [], 'Modules.Gcfacetedsearch.Admin'),
                        'title' => 'Parameter 1',
                        'name' => 'parameter',
                        'values' => [
                            '1' => $this->translator->trans('Parameter 1 value description', [], 'Modules.Gcfacetedsearch.Admin'),
                            '2' => $this->translator->trans('Parameter 2 value description', [], 'Modules.Gcfacetedsearch.Admin'),
                        ],
                    ],
                ],
                'command' => [
                    'arguments' => [
                        'action' => 'actionName',
                    ],
                    'cli_only' => true,
                ],
            ],
        ];
        */
    }

    public function hooks(): array
    {
        return [];

        /*
        return [
            'actionTestHook' => new Hook('actionTestHook'),
        ];
        */
    }

    public function orderStates(): array
    {
        return [];
    }

    public function sql(): Sql
    {
        $schema = new Schema();

        /*
        $table = $schema->createTable($this->dbPrefix . 'example');
        $table->addColumn('id_example', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('id_column', 'integer', ['unsigned' => true]);
        $table->addColumn('date_add', 'datetime');
        $table->addColumn('date_upd', 'datetime');
        $table->addColumn('active', 'integer', ['unsigned' => true]);
        $table->addColumn('deleted', 'integer', ['unsigned' => true]);
        $table->setPrimaryKey(['id_example']);
        $table->addIndex(['id_column']);

        $table = $schema->createTable($this->dbPrefix . 'example_lang');
        $table->addColumn('id_example', 'integer', ['unsigned' => true]);
        $table->addColumn('id_lang', 'integer', ['unsigned' => true]);
        $table->addColumn('name', 'string', ['length' => 64]);
        $table->addUniqueIndex(['id_example', 'id_lang']);

        $table = $schema->createTable($this->dbPrefix . 'example_shop');
        $table->addColumn('id_example', 'integer', ['unsigned' => true]);
        $table->addColumn('id_shop', 'integer', ['unsigned' => true]);
        $table->addColumn('active', 'integer', ['unsigned' => true]);
        $table->addUniqueIndex(['id_example', 'id_shop']);
        */

        return new Sql($schema);
    }

    public function tabs(): array
    {
        return [];

        /*
        return [
            new Tab(
                [
                    'en' => 'Example',
                    'de' => 'Example',
                ],
                'GcFacetedsearchExampleAdminController',
                'GcFacetedsearchConfigurationAdminParentController',
                'gc_facetedsearch_examplegrid_index',
                '',
                'Example',
                'Modules.Gcfacetedsearch.Admin',
                true
            ),
        ];
        */
    }

    public function translations(): array
    {
        return [
            // Locale
            'de-DE' => [
                // Original, Translation, Domain
                ['Awaiting payment: Facetedsearch', 'Warten auf Zahlungseingang: Facetedsearch', 'ModulesGcfacetedsearchAdmin'],
                ['Order placed', 'Bestellung eingegangen', 'ModulesGcfacetedsearchAdmin'],
                ['Facetedsearch Module', 'Facetedsearch Modul', 'ModulesGcfacetedsearchAdmin'],
                ['Configuration', 'Einstellungen', 'ModulesGcfacetedsearchAdmin'],
                ['Cron', 'Cron', 'ModulesGcfacetedsearchAdmin'],
                ['Logs', 'Logs', 'ModulesGcfacetedsearchAdmin'],
                ['Maintenance', 'Wartung', 'ModulesGcfacetedsearchAdmin'],
                ['License', 'Lizenz', 'ModulesGcfacetedsearchAdmin'],
                ['Payment', 'Status & Zahlung', 'ModulesGcfacetedsearchAdmin'],
            ],
        ];
    }

    public function fixtures(): callable
    {
        return function () {
            return true;
        };
    }

    public function help(): string
    {
        return 'http://www.onlineshop-module.de/';
    }
}
