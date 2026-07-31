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

use Configuration;
use Context;
use Country;
use Currency;
use Db;
use Product;
use Shop;
use Tools;

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
        $templates = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'gc_facetedsearch_filter ORDER BY date_add DESC');

        // We will keep track of pages categories where filter was already set, so we don't have multiple
        // filters for the same category and shop.
        $alreadyAssigned = [];

        // Clear cache
        $this->invalidateLayeredFilterBlockCache();

        // Remove all previous data from gc_facetedsearch_category
        Db::getInstance()->execute('TRUNCATE ' . _DB_PREFIX_ . 'gc_facetedsearch_category');

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
                                Db::getInstance()->execute($sqlInsertPrefix . rtrim($sqlInsert, ','));
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
            Db::getInstance()->execute($sqlInsertPrefix . rtrim($sqlInsert, ','));
        }
    }

    /**
     * Invalid filter block cache
     */
    public function invalidateLayeredFilterBlockCache()
    {
        return Db::getInstance()->execute('TRUNCATE TABLE ' . _DB_PREFIX_ . 'gc_facetedsearch_filter_block');
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
                'name' => $this->trans('Category', [], 'Modules.Gcfacetedsearch.Admin'),
                'cacheable' => true,
            ],
            'manufacturer' => [
                'name' => $this->trans('Manufacturer', [], 'Modules.Gcfacetedsearch.Admin'),
                'cacheable' => true,
            ],
            'supplier' => [
                'name' => $this->trans('Supplier', [], 'Modules.Gcfacetedsearch.Admin'),
                'cacheable' => true,
            ],
            'new-products' => [
                'name' => $this->trans('New products', [], 'Modules.Gcfacetedsearch.Admin'),
                'cacheable' => false,
            ],
            'best-sales' => [
                'name' => $this->trans('Best sales', [], 'Modules.Gcfacetedsearch.Admin'),
                'cacheable' => false,
            ],
            'prices-drop' => [
                'name' => $this->trans('Prices drop', [], 'Modules.Gcfacetedsearch.Admin'),
                'cacheable' => false,
            ],
            'search' => [
                'name' => $this->trans('Search', [], 'Modules.Gcfacetedsearch.Admin'),
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

    /**
     * Returns array with all controllers supported by this module
     */
    public function isControllerSupported($controller)
    {
        return isset($this->supportedControllers[$controller]);
    }

    /**
     * Should this controller filter blocks be cached?
     */
    public function shouldCacheController(string $controller)
    {
        return $this->supportedControllers[$controller]['cacheable'];
    }

    /**
     * Check if method is an ajax request.
     * This check is an old behavior and only check for _GET value.
     *
     * @return bool
     */
    public function isAjax()
    {
        return (bool) $this->ajax;
    }

    /**
     * Return current context
     *
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Return the current database instance
     *
     * @return Db
     */
    public function getDatabase()
    {
        if ($this->database === null) {
            $this->database = Db::getInstance();
        }

        return $this->database;
    }

    public function installPost(): bool
    {
        try {
            $this->getDatabase()->execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'gc_facetedsearch_indexable_feature` (id_feature)
                SELECT id_feature FROM `' . _DB_PREFIX_ . 'feature`'
            );

            $this->getDatabase()->execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'gc_facetedsearch_indexable_attribute_group` (id_attribute_group)
                SELECT id_attribute_group FROM `' . _DB_PREFIX_ . 'attribute_group`'
            );

            $this->rebuildPriceIndexTable();
        } catch (\Exception $e) {
        }
        return true;
    }

    /**
     * Install price indexes table
     */
    public function rebuildPriceIndexTable()
    {
        $this->getDatabase()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'gc_facetedsearch_price_index`');

        $this->getDatabase()->execute(
            'CREATE TABLE `' . _DB_PREFIX_ . 'gc_facetedsearch_price_index` (
            `id_product` INT  NOT NULL,
            `id_currency` INT NOT NULL,
            `id_shop` INT NOT NULL,
            `price_min` DECIMAL(20, 6) NOT NULL,
            `price_max` DECIMAL(20, 6) NOT NULL,
            `id_country` INT NOT NULL,
            PRIMARY KEY (`id_product`, `id_currency`, `id_shop`, `id_country`),
            INDEX `id_currency` (`id_currency`),
            INDEX `price_min` (`price_min`),
            INDEX `price_max` (`price_max`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;'
        );
    }

    /*
     * Generate data product attributes
     *
     * @param int $idProduct
     *
     * @return boolean
     */
    public function indexAttributes(int $idProduct = null)
    {
        if (null === $idProduct) {
            $this->getDatabase()->execute('TRUNCATE ' . _DB_PREFIX_ . 'gc_facetedsearch_product_attribute');
        } else {
            $this->getDatabase()->execute(
                'DELETE FROM ' . _DB_PREFIX_ . 'gc_facetedsearch_product_attribute
                WHERE id_product = ' . (int) $idProduct
            );
        }

        return $this->getDatabase()->execute(
            'INSERT INTO `' . _DB_PREFIX_ . 'gc_facetedsearch_product_attribute` (`id_attribute`, `id_product`, `id_attribute_group`, `id_shop`)
            SELECT pac.id_attribute, pa.id_product, ag.id_attribute_group, product_attribute_shop.`id_shop`
            FROM ' . _DB_PREFIX_ . 'product_attribute pa' .
            Shop::addSqlAssociation('product_attribute', 'pa') . '
            INNER JOIN ' . _DB_PREFIX_ . 'product_attribute_combination pac ON pac.id_product_attribute = pa.id_product_attribute
            INNER JOIN ' . _DB_PREFIX_ . 'attribute a ON (a.id_attribute = pac.id_attribute)
            INNER JOIN ' . _DB_PREFIX_ . 'attribute_group ag ON ag.id_attribute_group = a.id_attribute_group
            ' . ($idProduct === null ? '' : 'AND pa.id_product = ' . (int) $idProduct) . '
            GROUP BY a.id_attribute, pa.id_product , product_attribute_shop.`id_shop`'
        );
    }

    /**
     * create table product attribute.
     */
    private function installProductAttributeTable()
    {
        $this->getDatabase()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'gc_facetedsearch_product_attribute`');
        $this->getDatabase()->execute(
            'CREATE TABLE `' . _DB_PREFIX_ . 'gc_facetedsearch_product_attribute` (
            `id_attribute` int(10) unsigned NOT NULL,
            `id_product` int(10) unsigned NOT NULL,
            `id_attribute_group` int(10) unsigned NOT NULL DEFAULT "0",
            `id_shop` int(10) unsigned NOT NULL DEFAULT "1",
            PRIMARY KEY (`id_attribute`, `id_product`, `id_shop`),
            UNIQUE KEY `id_attribute_group` (`id_attribute_group`,`id_attribute`,`id_product`, `id_shop`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;'
        );
    }

    /**
     * Index product prices
     *
     * @param int $idProduct
     * @param bool $smart Delete before reindex
     */
    public function indexProductPrices(int $idProduct, $smart = true)
    {
        static $groups = null;

        if ($groups === null) {
            $groups = $this->getDatabase()->executeS('SELECT id_group FROM `' . _DB_PREFIX_ . 'group_reduction`');
            if (!$groups) {
                $groups = [];
            }
        }

        $shopList = Shop::getShops(false, null, true);

        foreach ($shopList as $idShop) {
            $currencyList = Currency::getCurrencies(false, true, true);

            $minPrice = [];
            $maxPrice = [];

            if ($smart) {
                $this->getDatabase()->execute('DELETE FROM `' . _DB_PREFIX_ . 'gc_facetedsearch_price_index` WHERE `id_product` = ' . (int) $idProduct . ' AND `id_shop` = ' . (int) $idShop);
            }

            $taxRatesByCountry = $this->getDatabase()->executeS(
                'SELECT t.rate rate, tr.id_country, c.iso_code ' .
                'FROM `' . _DB_PREFIX_ . 'product_shop` p ' .
                'LEFT JOIN `' . _DB_PREFIX_ . 'tax_rules_group` trg ON  ' .
                '(trg.id_tax_rules_group = p.id_tax_rules_group AND p.id_shop = ' . (int) $idShop . ') ' .
                'LEFT JOIN `' . _DB_PREFIX_ . 'tax_rule` tr ON (tr.id_tax_rules_group = trg.id_tax_rules_group) ' .
                'LEFT JOIN `' . _DB_PREFIX_ . 'tax` t ON (t.id_tax = tr.id_tax AND t.active = 1) ' .
                'JOIN `' . _DB_PREFIX_ . 'country` c ON (tr.id_country=c.id_country AND c.active = 1) ' .
                'WHERE id_product = ' . (int) $idProduct . ' ' .
                'GROUP BY id_product, tr.id_country'
            );

            if (empty($taxRatesByCountry) || !Configuration::get('GC_LAYERED_FILTER_PRICE_USETAX')) {
                $shopCountries = Country::getCountriesByIdShop($idShop, $this->getContext()->language->id);
                $taxCountries = array_filter($shopCountries, function ($country) {
                    return $country['active'];
                });
                $taxRatesByCountry = array_map(function ($country) {
                    return [
                        'rate' => 0,
                        'id_country' => $country['id_country'],
                        'iso_code' => $country['iso_code'],
                    ];
                }, $taxCountries);
            }

            $productMinPrices = $this->getDatabase()->executeS(
                'SELECT id_shop, id_currency, id_country, id_group, from_quantity
                FROM `' . _DB_PREFIX_ . 'specific_price`
                WHERE id_product = ' . (int) $idProduct . ' AND id_shop IN (0,' . (int) $idShop . ')'
            );

            $countries = Country::getCountries($this->getContext()->language->id, true, false, false);
            foreach ($countries as $country) {
                $idCountry = $country['id_country'];

                // Get price by currency & country, without reduction!
                foreach ($currencyList as $currency) {
                    $price = Product::priceCalculation(
                        $idShop,
                        (int) $idProduct,
                        null,
                        $idCountry,
                        0,
                        '',
                        $currency['id_currency'],
                        0,
                        0,
                        false,
                        6, // Decimals
                        false,
                        false,
                        true,
                        $specificPriceOutput,
                        true
                    );

                    $minPrice[$idCountry][$currency['id_currency']] = $price;
                    $maxPrice[$idCountry][$currency['id_currency']] = $price;
                }

                foreach ($productMinPrices as $specificPrice) {
                    foreach ($currencyList as $currency) {
                        if ($specificPrice['id_currency'] &&
                            $specificPrice['id_currency'] != $currency['id_currency']
                        ) {
                            continue;
                        }

                        $price = Product::priceCalculation(
                            $idShop,
                            (int) $idProduct,
                            null,
                            $idCountry,
                            0,
                            '',
                            $currency['id_currency'],
                            (int) $specificPrice['id_group'],
                            $specificPrice['from_quantity'],
                            false,
                            6,
                            false,
                            true,
                            true,
                            $specificPriceOutput,
                            true
                        );

                        if ($price > $maxPrice[$idCountry][$currency['id_currency']]) {
                            $maxPrice[$idCountry][$currency['id_currency']] = $price;
                        }

                        if ($price == 0) {
                            continue;
                        }

                        if (null === $minPrice[$idCountry][$currency['id_currency']] || $price < $minPrice[$idCountry][$currency['id_currency']]) {
                            $minPrice[$idCountry][$currency['id_currency']] = $price;
                        }
                    }
                }

                foreach ($groups as $group) {
                    foreach ($currencyList as $currency) {
                        $price = Product::priceCalculation(
                            $idShop,
                            (int) $idProduct,
                            null,
                            (int) $idCountry,
                            0,
                            '',
                            (int) $currency['id_currency'],
                            (int) $group['id_group'],
                            0,
                            false,
                            6,
                            false,
                            true,
                            true,
                            $specificPriceOutput,
                            true
                        );

                        if (!isset($maxPrice[$idCountry][$currency['id_currency']])) {
                            $maxPrice[$idCountry][$currency['id_currency']] = 0;
                        }

                        if (!isset($minPrice[$idCountry][$currency['id_currency']])) {
                            $minPrice[$idCountry][$currency['id_currency']] = null;
                        }

                        if ($price == 0) {
                            continue;
                        }

                        if (null === $minPrice[$idCountry][$currency['id_currency']] || $price < $minPrice[$idCountry][$currency['id_currency']]) {
                            $minPrice[$idCountry][$currency['id_currency']] = $price;
                        }

                        if ($price > $maxPrice[$idCountry][$currency['id_currency']]) {
                            $maxPrice[$idCountry][$currency['id_currency']] = $price;
                        }
                    }
                }
            }

            $values = [];
            foreach ($taxRatesByCountry as $taxRateByCountry) {
                $taxRate = $taxRateByCountry['rate'];
                $idCountry = $taxRateByCountry['id_country'];
                foreach ($currencyList as $currency) {
                    $minPriceValue = array_key_exists($idCountry, $minPrice) ? $minPrice[$idCountry][$currency['id_currency']] : 0;
                    $maxPriceValue = array_key_exists($idCountry, $maxPrice) ? $maxPrice[$idCountry][$currency['id_currency']] : 0;
                    if (!in_array($taxRateByCountry['iso_code'], self::ISO_CODE_TAX_FREE)) {
                        $minPriceValue = Tools::ps_round($minPriceValue * (100 + $taxRate) / 100, self::DECIMAL_DIGITS);
                        $maxPriceValue = Tools::ps_round($maxPriceValue * (100 + $taxRate) / 100, self::DECIMAL_DIGITS);
                    }

                    $values[] = '(' . (int) $idProduct . ',
                        ' . (int) $currency['id_currency'] . ',
                        ' . $idShop . ',
                        ' . (float) $minPriceValue . ',
                        ' . (float) $maxPriceValue . ',
                        ' . (int) $idCountry . ')';
                }
            }

            if (!empty($values)) {
                $this->getDatabase()->execute(
                    'INSERT INTO `' . _DB_PREFIX_ . 'gc_facetedsearch_price_index` (id_product, id_currency, id_shop, price_min, price_max, id_country)
                     VALUES ' . implode(',', $values) . '
                     ON DUPLICATE KEY UPDATE id_product = id_product' // Avoid duplicate keys
                );
            }
        }
    }

    /**
     * Provides data about single filter template.
     *
     * @param int $idFilterTemplate ID of filter template
     *
     * @return array Filter data
     */
    public function getFilterTemplate($idFilterTemplate)
    {
        return $this->getDatabase()->getRow(
            'SELECT *
            FROM `' . _DB_PREFIX_ . 'gc_facetedsearch_filter`
            WHERE id_gc_facetedsearch_filter = ' . (int) $idFilterTemplate
        );
    }
}
