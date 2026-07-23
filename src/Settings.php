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
        return [
            // 'TEST_CONFIG' => new Config(
            //     'TEST_CONFIG',
            //     [
            //         'en' => 'Value',
            //         'de' => 'Wert',
            //     ]
            // ),
            'CACHE_ENABLED' => new Config('CACHE_ENABLED', 1),
            'SHOW_QUIES' => new Config('SHOW_QUIES', 1),
            'FULL_TREE' => new Config('FULL_TREE', 1),
            'FILTER_BY_DEFAULT_CATEGORY' => new Config('FILTER_BY_DEFAULT_CATEGORY', 0),
            'FILTER_CATEGORY_DEPTH' => new Config('FILTER_CATEGORY_DEPTH', 1),
            'FILTER_PRICE_USETAX' => new Config('FILTER_PRICE_USETAX', 1),
            'FILTER_PRICE_ROUNDING' => new Config('FILTER_PRICE_ROUNDING', 1),
            'FILTER_SHOW_OUT_OF_STOCK_LAST' => new Config('FILTER_SHOW_OUT_OF_STOCK_LAST', 0),
            'USE_JQUERY_UI_SLIDER' => new Config('USE_JQUERY_UI_SLIDER', 1),
            'DEFAULT_CATEGORY_TEMPLATE' => new Config('DEFAULT_CATEGORY_TEMPLATE', 0),
            // 'TEST_CONFIG_ABC' => new Config(
            //     'TEST_CONFIG_ABC',
            //     'value'
            // ),
        ];
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
        return [
            // // attribute
            // 'actionAttributeGroupDelete' => new Hook('actionAttributeGroupDelete'),
            // 'actionAttributeSave' => new Hook('actionAttributeSave'),
            // 'displayAttributeForm' => new Hook('displayAttributeForm'),
            // 'actionAttributePostProcess' => new Hook('actionAttributePostProcess'),
            // 'actionAttributeFormBuilderModifier' => new Hook('actionAttributeFormBuilderModifier'),
            // 'actionAttributeFormDataProviderData' => new Hook('actionAttributeFormDataProviderData'),
            // 'actionAfterCreateAttributeFormHandler' => new Hook('actionAfterCreateAttributeFormHandler'),
            // 'actionAfterUpdateAttributeFormHandler' => new Hook('actionAfterUpdateAttributeFormHandler'),
            // // attribute group
            // 'actionAttributeGroupSave' => new Hook('actionAttributeGroupSave'),
            // 'displayAttributeGroupForm' => new Hook('displayAttributeGroupForm'),
            // 'displayAttributeGroupPostProcess' => new Hook('displayAttributeGroupPostProcess'),
            // 'actionAttributeGroupFormBuilderModifier' => new Hook('actionAttributeGroupFormBuilderModifier'),
            // 'actionAttributeGroupFormDataProviderData' => new Hook('actionAttributeGroupFormDataProviderData'),
            // 'actionAfterCreateAttributeGroupFormHandler' => new Hook('actionAfterCreateAttributeGroupFormHandler'),
            // 'actionAfterUpdateAttributeGroupFormHandler' => new Hook('actionAfterUpdateAttributeGroupFormHandler'),
            // // product
            // 'actionProductSave' => new Hook('actionProductSave'),
            // // category
            // 'actionCategoryAdd' => new Hook('actionCategoryAdd'),
            // 'actionCategoryDelete' => new Hook('actionCategoryDelete'),
            // // configuration
            // 'actionProductPreferencesPageStockSave' => new Hook('actionProductPreferencesPageStockSave'),
            // // design
            // 'displayLeftColumn' => new Hook('displayLeftColumn'),
            // // feature
            // 'actionFeatureSave' => new Hook('actionFeatureSave'),
            // 'actionFeatureDelete' => new Hook('actionFeatureDelete'),
            // 'displayFeatureForm' => new Hook('displayFeatureForm'),
            // 'displayFeaturePostProcess' => new Hook('displayFeaturePostProcess'),
            // 'actionFeatureFormBuilderModifier' => new Hook('actionFeatureFormBuilderModifier'),
            // 'actionAfterCreateFeatureFormHandler' => new Hook('actionAfterCreateFeatureFormHandler'),
            // 'actionAfterUpdateFeatureFormHandler' => new Hook('actionAfterUpdateFeatureFormHandler'),
            // // feature value
            // 'actionFeatureValueSave' => new Hook('actionFeatureValueSave'),
            // 'actionFeatureValueDelete' => new Hook('actionFeatureValueDelete'),
            // 'displayFeatureValueForm' => new Hook('displayFeatureValueForm'),
            // 'displayFeatureValuePostProcess' => new Hook('displayFeatureValuePostProcess'),
            // 'actionFeatureValueFormBuilderModifier' => new Hook('actionFeatureValueFormBuilderModifier'),
            // 'actionAfterCreateFeatureValueFormHandler' => new Hook('actionAfterCreateFeatureValueFormHandler'),
            // 'actionAfterUpdateFeatureValueFormHandler' => new Hook('actionAfterUpdateFeatureValueFormHandler'),
            // // product search
            // 'productSearchProvider' => new Hook('productSearchProvider'),
            // // specific price
            // 'actionObjectSpecificPriceRuleUpdateBefore' => new Hook('actionObjectSpecificPriceRuleUpdateBefore'),
            // 'actionAdminSpecificPriceRuleControllerSaveAfter' => new Hook('actionAdminSpecificPriceRuleControllerSaveAfter'),
        ];
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

        $table = $schema->createTable($this->dbPrefix . 'gc_facetedsearch_category');
        $table->addColumn('id_gc_facetedsearch_category', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('id_shop', 'integer', ['unsigned' => true]);
        $table->addColumn('controller', 'string', ['length' => 64]);
        $table->addColumn('id_category', 'integer', ['unsigned' => true]);
        $table->addColumn('id_value', 'integer', ['unsigned' => true, 'notnull' => false, 'default' => 0]);
        $table->addColumn('type', 'string', [
            'columnDefinition' => "ENUM('category','id_feature','id_attribute_group','availability','condition','manufacturer','weight','price','extras') NOT NULL",
        ]);
        $table->addColumn('position', 'integer', ['unsigned' => true]);
        $table->addColumn('filter_type', 'integer', ['unsigned' => true, 'default' => 0]);
        $table->addColumn('filter_show_limit', 'integer', ['unsigned' => true, 'default' => 0]);
        $table->setPrimaryKey(['id_gc_facetedsearch_category']);
        $table->addIndex(['id_category', 'id_shop', 'type', 'id_value', 'position'], 'id_category_shop');
        $table->addIndex(['id_category', 'type'], 'id_category');


        $table = $schema->createTable($this->dbPrefix . 'gc_facetedsearch_filter');
        $table->addColumn('id_gc_facetedsearch_filter', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 64]);
        $table->addColumn('filters', 'text', ['notnull' => false, 'length' => 4294967295]);
        $table->addColumn('n_categories', 'integer', ['unsigned' => true]);
        $table->addColumn('date_add', 'datetime');
        $table->setPrimaryKey(['id_gc_facetedsearch_filter']);

        $table = $schema->createTable($this->dbPrefix . 'gc_facetedsearch_filter_block');
        $table->addColumn('hash', 'string', ['length' => 32, 'fixed' => true, 'default' => '']);
        $table->addColumn('data', 'text', ['notnull' => false, 'length' => 4294967295]);
        $table->setPrimaryKey(['hash']);

        $table = $schema->createTable($this->dbPrefix . 'gc_facetedsearch_filter_shop');
        $table->addColumn('id_gc_facetedsearch_filter', 'integer', ['unsigned' => true]);
        $table->addColumn('id_shop', 'integer', ['unsigned' => true]);
        $table->setPrimaryKey(['id_gc_facetedsearch_filter', 'id_shop']);
        $table->addIndex(['id_shop'], 'id_shop');

        return new Sql($schema);
    }

    public function tabs(): array
    {
        return [
            new Tab(
                [
                    'en' => 'Filter templates',
                    'de' => 'Filter templates',
                ],
                'GcFacetedsearchFilterTemplateAdminController',
                'GcFacetedsearchConfigurationAdminParentController',
                'gc_facetedsearch_configuration',
                '',
                'Filter templates',
                'Modules.Gcfacetedsearch.Admin',
                false
            ),
        ];
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
