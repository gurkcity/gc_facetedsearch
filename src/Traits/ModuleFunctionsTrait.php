<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

namespace Onlineshopmodule\PrestaShop\Module\Facetedsearch\Traits;

trait ModuleFunctionsTrait
{
    /**
     * @var array|null List of controllers supported by this module
     */
    protected $supportedControllers;

    /**
     * This method gets serialized data of filter templates from gc_facetedsearch_filter table and builds detailed
     * information, one category = one line.
     */
    public function buildLayeredCategories()
    {
        // Get data for all filter templates in the database
        $templates = \Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'gc_facetedsearch_filter ORDER BY date_add DESC');

        // We will keep track of pages categories where filter was already set, so we don't have multiple
        // filters for the same category and shop.
        $alreadyAssigned = [];

        // Clear cache
        $this->invalidateLayeredFilterBlockCache();

        // Remove all previous data from gc_facetedsearch_category
        \Db::getInstance()->execute('TRUNCATE ' . _DB_PREFIX_ . 'gc_facetedsearch_category');

        // If no filter templates are defined, nothing else to do here
        if (!count($templates)) {
            return true;
        }

        // We will insert our queries by batches of hundred queries
        $sqlInsertPrefix = 'INSERT INTO ' . _DB_PREFIX_ . 'gc_facetedsearch_category (id_category, controller, id_shop, id_value, type, position, filter_show_limit, filter_type) VALUES ';
        $sqlInsert = '';
        $nbSqlValuesToInsert = 0;

        // Now we will loop through each filter template
        foreach ($templates as $filterTemplate) {
            // We will get it's data and convert it into array
            $data = \Tools::unSerialize($filterTemplate['filters']);

            foreach ($data['shop_list'] as $idShop) {
                if (!isset($alreadyAssigned[$idShop])) {
                    $alreadyAssigned[$idShop] = [];
                }

                // Now let's generate data for each controller in the template
                foreach ($data['controllers'] as $controller) {
                    // If it's a category controller, we will do it for each category
                    // Otherwise, we will use just one line with zero
                    $categories = ($controller == 'category' ? $data['categories'] : [0]);

                    foreach ($categories as $idCategory) {
                        $n = 0;

                        // Make unique job name and check if already generated something for this scenario
                        // If yes, skip it, otherwise note this info for next time
                        $jobName = $controller . '-' . $idCategory;
                        if (in_array($jobName, $alreadyAssigned[$idShop])) {
                            continue;
                        }
                        $alreadyAssigned[$idShop][] = $jobName;

                        foreach ($data as $key => $value) {
                            // The template contains some other data than filters, so we clean it up a bit
                            // All filters begin with filter
                            if (substr($key, 0, 6) != 'filter') {
                                continue;
                            }

                            $type = $value['filter_type'];
                            $limit = $value['filter_show_limit'];
                            ++$n;

                            if ($key == 'filter_stock') {
                                $sqlInsert .= '(' . (int) $idCategory . ', \'' . $controller . '\', ' . (int) $idShop . ', NULL,\'availability\',' . (int) $n . ', ' . (int) $limit . ', ' . (int) $type . '),';
                            } elseif ($key == 'filter_subcategories') {
                                $sqlInsert .= '(' . (int) $idCategory . ', \'' . $controller . '\', ' . (int) $idShop . ', NULL,\'category\',' . (int) $n . ', ' . (int) $limit . ', ' . (int) $type . '),';
                            } elseif ($key == 'filter_condition') {
                                $sqlInsert .= '(' . (int) $idCategory . ', \'' . $controller . '\', ' . (int) $idShop . ', NULL,\'condition\',' . (int) $n . ', ' . (int) $limit . ', ' . (int) $type . '),';
                            } elseif ($key == 'filter_weight_slider') {
                                $sqlInsert .= '(' . (int) $idCategory . ', \'' . $controller . '\', ' . (int) $idShop . ', NULL,\'weight\',' . (int) $n . ', ' . (int) $limit . ', ' . (int) $type . '),';
                            } elseif ($key == 'filter_price_slider') {
                                $sqlInsert .= '(' . (int) $idCategory . ', \'' . $controller . '\', ' . (int) $idShop . ', NULL,\'price\',' . (int) $n . ', ' . (int) $limit . ', ' . (int) $type . '),';
                            } elseif ($key == 'filter_manufacturer') {
                                $sqlInsert .= '(' . (int) $idCategory . ', \'' . $controller . '\', ' . (int) $idShop . ', NULL,\'manufacturer\',' . (int) $n . ', ' . (int) $limit . ', ' . (int) $type . '),';
                            } elseif (substr($key, 0, 23) == 'filter_attribute_group_') {
                                $sqlInsert .= '(' . (int) $idCategory . ', \'' . $controller . '\', ' . (int) $idShop . ', ' . (int) str_replace('filter_attribute_group_', '', $key) . ',
    \'id_attribute_group\',' . (int) $n . ', ' . (int) $limit . ', ' . (int) $type . '),';
                            } elseif (substr($key, 0, 15) == 'filter_feature_') {
                                $sqlInsert .= '(' . (int) $idCategory . ', \'' . $controller . '\', ' . (int) $idShop . ', ' . (int) str_replace('filter_feature_', '', $key) . ',
    \'id_feature\',' . (int) $n . ', ' . (int) $limit . ', ' . (int) $type . '),';
                            } elseif ($key == 'filter_extras') {
                                $sqlInsert .= '(' . (int) $idCategory . ', \'' . $controller . '\', ' . (int) $idShop . ', NULL,\'extras\',' . (int) $n . ', ' . (int) $limit . ', ' . (int) $type . '),';
                            }

                            ++$nbSqlValuesToInsert;

                            // If we reached the limit, we will execute it and flush our "cache"
                            if ($nbSqlValuesToInsert >= 100) {
                                \Db::getInstance()->execute($sqlInsertPrefix . rtrim($sqlInsert, ','));
                                $sqlInsert = '';
                                $nbSqlValuesToInsert = 0;
                            }
                        }
                    }
                }
            }
        }

        // We will execute remaining queries because we almost certainly didn't reach 100 in the batch
        if ($nbSqlValuesToInsert) {
            \Db::getInstance()->execute($sqlInsertPrefix . rtrim($sqlInsert, ','));
        }
    }

    /**
     * Invalid filter block cache
     */
    public function invalidateLayeredFilterBlockCache()
    {
        return \Db::getInstance()->execute('TRUNCATE TABLE ' . _DB_PREFIX_ . 'gc_facetedsearch_filter_block');
    }

    /**
     * Returns array with all controllers supported by this module
     */
    public function getSupportedControllers(): array
    {
        if ($this->supportedControllers === null) {
            $this->initializeSupportedControllers();
        }
        return $this->supportedControllers;
    }

    public function setSupportedControllers(array $supportedControllers): void
    {
        $this->supportedControllers = $supportedControllers;
    }

    public function initializeSupportedControllers(): void
    {
        $supportedControllers = [
            'category' => [
                'name' => $this->trans('Category', [], 'Modules.Facetedsearch.Admin'),
                'cacheable' => true,
            ],
            'manufacturer' => [
                'name' => $this->trans('Manufacturer', [], 'Modules.Facetedsearch.Admin'),
                'cacheable' => true,
            ],
            'supplier' => [
                'name' => $this->trans('Supplier', [], 'Modules.Facetedsearch.Admin'),
                'cacheable' => true,
            ],
            'new-products' => [
                'name' => $this->trans('New products', [], 'Modules.Facetedsearch.Admin'),
                'cacheable' => false,
            ],
            'best-sales' => [
                'name' => $this->trans('Best sales', [], 'Modules.Facetedsearch.Admin'),
                'cacheable' => false,
            ],
            'prices-drop' => [
                'name' => $this->trans('Prices drop', [], 'Modules.Facetedsearch.Admin'),
                'cacheable' => false,
            ],
            'search' => [
                'name' => $this->trans('Search', [], 'Modules.Facetedsearch.Admin'),
                'cacheable' => false,
            ],
        ];

        \Hook::exec(
            'actionFacetedSearchSetSupportedControllers',
            [
                'supportedControllers' => &$supportedControllers,
            ]
        );

        $this->setSupportedControllers($supportedControllers);
    }
}
