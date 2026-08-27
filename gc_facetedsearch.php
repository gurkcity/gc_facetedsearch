<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Traits\ModuleHelperTrait;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Traits\ModuleLicenseTrait;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Traits\ModuleTrait;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\HookDispatcher;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

class GC_Facetedsearch extends Module
{
    use ModuleTrait;
    use ModuleHelperTrait;
    use ModuleLicenseTrait;

    /**
     * @var string Name of the module running on PS 1.6.x. Used for data migration.
     */
    const PS_16_EQUIVALENT_MODULE = 'blocklayered';

    /**
     * @var string Official PrestaShop faceted search module to migrate from.
     */
    const PS_FACETEDSEARCH_MODULE = 'ps_facetedsearch';

    /**
     * Lock indexation if too many products
     *
     * @var int
     */
    const LOCK_TOO_MANY_PRODUCTS = 5000;

    /**
     * Lock template filter creation if too many products
     *
     * @var int
     */
    const LOCK_TEMPLATE_CREATION = 20000;

    /**
     * US iso code, used to prevent taxes usage while computing prices
     *
     * @var array
     */
    const ISO_CODE_TAX_FREE = [
        'US',
    ];

    /**
     * Number of digits for MySQL DECIMAL
     *
     * @var int
     */
    const DECIMAL_DIGITS = 6;

    /**
     * @var array List of controllers supported by this module
     */
    protected $supportedControllers = [];

    /**
     * @var bool
     */
    private $ajax;

    /**
     * @var int
     */
    private $gcLayeredFullTree;

    /**
     * @var Db
     */
    private $database;

    /**
     * @var HookDispatcher
     */
    private $hookDispatcher;

    public function __construct()
    {
        $this->version = '9.0.0';

        $this->name = 'gc_facetedsearch';

        $this->author = 'Gurkcity';

        $this->ps_versions_compliancy = [
            'min' => '9.0.0',
            'max' => _PS_VERSION_,
        ];

        $this->tab = 'front_office_features';

        $this->displayName = $this->trans('GC Facetedsearch', [], 'Modules.Gcfacetedsearch.Admin');
        $this->displayNamePre = $this->trans('Faceted', [], 'Modules.Gcfacetedsearch.Admin');
        $this->displayNamePost = $this->trans('Search', [], 'Modules.Gcfacetedsearch.Admin');
        $this->description = $this->trans('Filter your catalog to help visitors picture the category tree and browse your store easily.', [], 'Modules.Gcfacetedsearch.Admin');
        $this->description_full = $this->trans('Filter your catalog to help visitors picture the category tree and browse your store easily.', [], 'Modules.Gcfacetedsearch.Admin');

        parent::__construct();

        $this->initModule();
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

    /**
     * Return current context
     *
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    private function uninstallPsFacetedSearchModule()
    {
        /** @var Module|bool $oldModule */
        $oldModule = Module::getInstanceByName(self::PS_FACETEDSEARCH_MODULE);
        if ($oldModule) {
            $oldModule->uninstall();
        }
    }

    /**
     * @return HookDispatcher
     */
    public function getHookDispatcher()
    {
        return $this->hookDispatcher;
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

    /*
     * Generate data for product features
     *
     * @return boolean
     */
    public function indexFeatures()
    {
        return $this->getDatabase()->execute(
            'INSERT INTO `' . _DB_PREFIX_ . 'gc_facetedsearch_indexable_feature` ' .
            'SELECT id_feature, 1 FROM `' . _DB_PREFIX_ . 'feature` ' .
            'WHERE id_feature NOT IN (SELECT id_feature FROM ' .
            '`' . _DB_PREFIX_ . 'gc_facetedsearch_indexable_feature`)'
        );
    }

    /*
     * Generate data for product attribute group
     *
     * @return boolean
     */
    public function indexAttributeGroup()
    {
        return $this->getDatabase()->execute(
            'INSERT INTO `' . _DB_PREFIX_ . 'gc_facetedsearch_indexable_attribute_group` ' .
            'SELECT id_attribute_group, 1 FROM `' . _DB_PREFIX_ . 'attribute_group` ' .
            'WHERE id_attribute_group NOT IN (SELECT id_attribute_group FROM ' .
            '`' . _DB_PREFIX_ . 'gc_facetedsearch_indexable_attribute_group`)'
        );
    }

    /**
     * Full prices index process
     *
     * @param int $cursor in order to restart indexing from the last state
     * @param bool $ajax
     * @param bool $smart
     */
    public function fullPricesIndexProcess($cursor = 0, $ajax = false, $smart = false)
    {
        if ($cursor == 0 && !$smart) {
            $this->rebuildPriceIndexTable();
        }

        return $this->indexPricesRecursive($cursor, true, $ajax, $smart);
    }

    /**
     * Prices index process
     *
     * @param int $cursor in order to restart indexing from the last state
     * @param bool $ajax
     */
    public function pricesIndexProcess($cursor = 0, $ajax = false)
    {
        return $this->indexPricesRecursive($cursor, false, $ajax);
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

        $omitCountries = (bool) $this->getConfig()->get('OMIT_COUNTRIES');

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
                ($omitCountries ? 'AND c.id_country = ' . (int) Configuration::get('PS_COUNTRY_DEFAULT') : '') . ' ' .
                'GROUP BY id_product, tr.id_country'
            );

            if (empty($taxRatesByCountry) || !$this->getConfig()->get('FILTER_PRICE_USETAX')) {
                $shopCountries = Country::getCountriesByIdShop($idShop, $this->getContext()->language->id);

                if ($omitCountries) {
                    $shopCountries = [[
                        'id_country' => (int) Configuration::get('PS_COUNTRY_DEFAULT'),
                        'active' => 1,
                        'iso_code' => Country::getIsoById((int) Configuration::get('PS_COUNTRY_DEFAULT')),
                    ]];
                } else {
                    $shopCountries = Country::getCountriesByIdShop($idShop, $this->getContext()->language->id);
                }

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

            if ($omitCountries) {
                $countries = [['id_country' => (int) Configuration::get('PS_COUNTRY_DEFAULT')]];
            } else {
                $countries = Country::getCountries($this->getContext()->language->id, true, false, false);
            }
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

                        foreach ($this->resolveTemplateFilterKeys($data) as $key) {
                            $value = $data[$key] ?? null;
                            if (!is_array($value) || !isset($value['filter_type'])) {
                                continue;
                            }

                            $type = $value['filter_type'];
                            $limit = $value['filter_show_limit'];
                            $rowSql = '';

                            if ($key == 'filter_stock') {
                                $rowSql = '(' . (int) $idCategory . ', \'' . $controller . '\', ' . (int) $idShop . ', NULL,\'availability\',' . (int) ($n + 1) . ', ' . (int) $limit . ', ' . (int) $type . '),';
                            } elseif ($key == 'filter_subcategories') {
                                $rowSql = '(' . (int) $idCategory . ', \'' . $controller . '\', ' . (int) $idShop . ', NULL,\'category\',' . (int) ($n + 1) . ', ' . (int) $limit . ', ' . (int) $type . '),';
                            } elseif ($key == 'filter_condition') {
                                $rowSql = '(' . (int) $idCategory . ', \'' . $controller . '\', ' . (int) $idShop . ', NULL,\'condition\',' . (int) ($n + 1) . ', ' . (int) $limit . ', ' . (int) $type . '),';
                            } elseif ($key == 'filter_weight_slider') {
                                $rowSql = '(' . (int) $idCategory . ', \'' . $controller . '\', ' . (int) $idShop . ', NULL,\'weight\',' . (int) ($n + 1) . ', ' . (int) $limit . ', ' . (int) $type . '),';
                            } elseif ($key == 'filter_price_slider') {
                                $rowSql = '(' . (int) $idCategory . ', \'' . $controller . '\', ' . (int) $idShop . ', NULL,\'price\',' . (int) ($n + 1) . ', ' . (int) $limit . ', ' . (int) $type . '),';
                            } elseif ($key == 'filter_manufacturer') {
                                $rowSql = '(' . (int) $idCategory . ', \'' . $controller . '\', ' . (int) $idShop . ', NULL,\'manufacturer\',' . (int) ($n + 1) . ', ' . (int) $limit . ', ' . (int) $type . '),';
                            } elseif (substr($key, 0, 23) == 'filter_attribute_group_') {
                                $rowSql = '(' . (int) $idCategory . ', \'' . $controller . '\', ' . (int) $idShop . ', ' . (int) str_replace('filter_attribute_group_', '', $key) . ',
    \'id_attribute_group\',' . (int) ($n + 1) . ', ' . (int) $limit . ', ' . (int) $type . '),';
                            } elseif (substr($key, 0, 15) == 'filter_feature_') {
                                $rowSql = '(' . (int) $idCategory . ', \'' . $controller . '\', ' . (int) $idShop . ', ' . (int) str_replace('filter_feature_', '', $key) . ',
    \'id_feature\',' . (int) ($n + 1) . ', ' . (int) $limit . ', ' . (int) $type . '),';
                            } elseif ($key == 'filter_extras') {
                                $rowSql = '(' . (int) $idCategory . ', \'' . $controller . '\', ' . (int) $idShop . ', NULL,\'extras\',' . (int) ($n + 1) . ', ' . (int) $limit . ', ' . (int) $type . '),';
                            }

                            if ($rowSql === '') {
                                continue;
                            }

                            ++$n;
                            $sqlInsert .= $rowSql;
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
     * Dispatch hooks
     *
     * @param string $methodName
     * @param array $arguments
     */
    public function __call($methodName, array $arguments)
    {
        if (strpos($methodName, 'hook') === false) {
            throw new Exception('Call missing method ::' . $methodName);
        }

        return $this->getHookDispatcher()->dispatch(
            $methodName,
            !empty($arguments[0]) ? $arguments[0] : []
        );
    }

    /**
     * Invalid filter block cache
     */
    public function invalidateLayeredFilterBlockCache()
    {
        return Db::getInstance()->execute('TRUNCATE TABLE ' . _DB_PREFIX_ . 'gc_facetedsearch_filter_block');
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

    /**
     * Index prices
     *
     * @param int $cursor last indexed id_product
     * @param bool $full
     * @param bool $ajax
     * @param bool $smart
     *
     * @return int|string|bool
     */
    private function indexPricesRecursive($cursor = 0, $full = false, $ajax = false, $smart = false)
    {
        if ($full) {
            $nbProducts = (int) $this->getDatabase()->getValue(
                'SELECT count(DISTINCT p.`id_product`) ' .
                'FROM ' . _DB_PREFIX_ . 'product p ' .
                'INNER JOIN `' . _DB_PREFIX_ . 'product_shop` ps ' .
                'ON (ps.`id_product` = p.`id_product` AND ps.`active` = 1 AND ps.`visibility` IN ("both", "catalog"))'
            );
        } else {
            $nbProducts = (int) $this->getDatabase()->getValue(
                'SELECT COUNT(DISTINCT p.`id_product`) ' .
                'FROM `' . _DB_PREFIX_ . 'product` p ' .
                'INNER JOIN `' . _DB_PREFIX_ . 'product_shop` ps ON (ps.`id_product` = p.`id_product` AND ps.`active` = 1 AND ps.`visibility` IN ("both", "catalog")) ' .
                'LEFT JOIN  `' . _DB_PREFIX_ . 'gc_facetedsearch_price_index` psi ON (psi.id_product = p.id_product) ' .
                'WHERE psi.id_product IS NULL'
            );
        }

        $maxExecutiontime = @ini_get('max_execution_time');
        if ($maxExecutiontime > 5 || $maxExecutiontime <= 0) {
            $maxExecutiontime = 5;
        }

        $startTime = microtime(true);

        $indexedProducts = 0;
        $length = 100;
        do {
            $lastCursor = $cursor;
            $cursor = (int) $this->indexPricesUnbreakable((int) $cursor, $full, $smart, $length);
            if ($cursor == 0) {
                $lastCursor = $cursor;
                break;
            }
            $time_elapsed = microtime(true) - $startTime;
            $indexedProducts += $length;
        } while (
            $cursor < $nbProducts
            && (Tools::getMemoryLimit() == -1 || Tools::getMemoryLimit() > memory_get_peak_usage())
            && $time_elapsed < $maxExecutiontime
        );

        if (($nbProducts > 0 && !$full || $cursor != $lastCursor && $full) && !$ajax) {
            return $this->indexPricesRecursive((int) $cursor, $full, $ajax, $smart);
        }

        if ($ajax && $nbProducts > 0 && $cursor != $lastCursor && $full) {
            return json_encode([
                'total' => $nbProducts,
                'cursor' => $cursor,
                'count' => $indexedProducts,
            ]);
        }

        if ($ajax && $nbProducts > 0 && !$full) {
            return json_encode([
                'total' => $nbProducts,
                'cursor' => $cursor,
                'count' => $indexedProducts,
            ]);
        }

        if ($ajax) {
            return json_encode([
                'result' => 'ok',
            ]);
        }

        return $nbProducts;
    }

    /**
     * Index prices unbreakable
     *
     * @param int $cursor last indexed id_product
     * @param bool $full All products, otherwise only indexed products
     * @param bool $smart Delete before reindex
     * @param int $length nb of products to index
     *
     * @return int
     */
    private function indexPricesUnbreakable($cursor, $full = false, $smart = false, $length = 100)
    {
        if ($full) {
            $query = 'SELECT p.`id_product` ' .
                'FROM `' . _DB_PREFIX_ . 'product` p ' .
                'INNER JOIN `' . _DB_PREFIX_ . 'product_shop` ps ' .
                'ON (ps.`id_product` = p.`id_product` AND ps.`active` = 1 AND ps.`visibility` IN ("both", "catalog")) ' .
                'WHERE p.id_product > ' . (int) $cursor . ' ' .
                'GROUP BY p.`id_product` ' .
                'ORDER BY p.`id_product` LIMIT 0,' . (int) $length;
        } else {
            $query = 'SELECT p.`id_product` ' .
                'FROM `' . _DB_PREFIX_ . 'product` p ' .
                'INNER JOIN `' . _DB_PREFIX_ . 'product_shop` ps ' .
                'ON (ps.`id_product` = p.`id_product` AND ps.`active` = 1 AND ps.`visibility` IN ("both", "catalog")) ' .
                'LEFT JOIN  `' . _DB_PREFIX_ . 'gc_facetedsearch_price_index` psi ON (psi.id_product = p.id_product) ' .
                'WHERE psi.id_product IS NULL ' .
                'GROUP BY p.`id_product` ' .
                'ORDER BY p.`id_product` LIMIT 0,' . (int) $length;
        }

        $lastIdProduct = 0;
        foreach ($this->getDatabase()->executeS($query) as $product) {
            $this->indexProductPrices((int) $product['id_product'], ($smart && $full));
            $lastIdProduct = $product['id_product'];
        }

        return (int) $lastIdProduct;
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
     * Ordered filter keys for a template (includes disabled slots from filters_order).
     */
    private function resolveTemplateFilterKeys(array $data): array
    {
        $reserved = ['categories', 'shop_list', 'controllers', 'filters_order'];
        $order = [];

        if (!empty($data['filters_order']) && is_array($data['filters_order'])) {
            foreach ($data['filters_order'] as $key) {
                if (is_string($key) && $key !== '' && !in_array($key, $reserved, true)) {
                    $order[] = $key;
                }
            }
        }

        foreach ($data as $key => $value) {
            if (in_array($key, $reserved, true) || !is_array($value)) {
                continue;
            }
            if (!in_array($key, $order, true)) {
                $order[] = $key;
            }
        }

        return $order;
    }

    public function parentInitModule()
    {
        $this->hookDispatcher = new HookDispatcher($this);
        $this->initializeSupportedControllers();
        $this->ajax = (bool) Tools::getValue('ajax');
    }

    public function cronIndexAttributes()
    {
        Shop::setContext(Shop::CONTEXT_ALL);

        $this->indexAttributes();
        $this->indexFeatures();
        $this->indexAttributeGroup();
    }

    public function cronClearCache()
    {
        return $this->invalidateLayeredFilterBlockCache();
    }

    public function cronIndexPrices(bool $full = false)
    {
        Shop::setContext(Shop::CONTEXT_ALL);

        if ($full) {
            return $this->fullPricesIndexProcess((int) Tools::getValue('cursor'), (bool) Tools::getValue('ajax'), true);
        } else {
            return $this->pricesIndexProcess((int) Tools::getValue('cursor'), (bool) Tools::getValue('ajax'));
        }
    }

    public function cronIndexBestsales()
    {
        Shop::setContext(Shop::CONTEXT_ALL);

        return $this->bestSalesIndexProcess(
            (int) Tools::getValue('cursor'),
            (bool) Tools::getValue('ajax')
        );
    }

    public function installPost(): bool
    {
        try {
            $this->getDatabase()->execute(
                'TRUNCATE TABLE `' . _DB_PREFIX_ . 'gc_facetedsearch_indexable_feature`'
            );

            $this->getDatabase()->execute(
                'TRUNCATE TABLE `' . _DB_PREFIX_ . 'gc_facetedsearch_indexable_attribute_group`'
            );

            $this->getDatabase()->execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'gc_facetedsearch_indexable_feature` (id_feature)
                SELECT id_feature FROM `' . _DB_PREFIX_ . 'feature`'
            );

            $this->getDatabase()->execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'gc_facetedsearch_indexable_attribute_group` (id_attribute_group)
                SELECT id_attribute_group FROM `' . _DB_PREFIX_ . 'attribute_group`'
            );

            $this->migrateFromPsFacetedSearch();

            $this->rebuildPriceIndexTable();
        } catch (\Exception $e) {
        }
        return true;
    }



    /**
     * Full best sales index process (batched, cursor-based).
     *
     * @param int $cursor last indexed id_product
     * @param bool $ajax
     *
     * @return int|string
     */
    public function bestSalesIndexProcess($cursor = 0, $ajax = false)
    {
        if ((int) $cursor === 0) {
            $this->getDatabase()->execute(
                'TRUNCATE TABLE `' . _DB_PREFIX_ . 'gc_facetedsearch_salescache`'
            );
        }

        return $this->indexBestSalesRecursive((int) $cursor, (bool) $ajax);
    }

    /**
     * Index best sales scores in batches.
     *
     * @param int $cursor last indexed id_product
     * @param bool $ajax
     *
     * @return int|string
     */
    private function indexBestSalesRecursive($cursor = 0, $ajax = false)
    {
        $days = (int) $this->getConfig()->get('BEST_SALES_DAYS');
        if ($days <= 0) {
            $days = 60;
        }

        $nbProducts = (int) $this->getDatabase()->getValue(
            'SELECT COUNT(DISTINCT p.`id_product`) FROM `' . _DB_PREFIX_ . 'product` p'
        );

        $maxExecutiontime = @ini_get('max_execution_time');
        if ($maxExecutiontime > 5 || $maxExecutiontime <= 0) {
            $maxExecutiontime = 5;
        }

        $startTime = microtime(true);
        $indexedProducts = 0;
        $length = 100;

        do {
            $lastCursor = $cursor;
            $cursor = (int) $this->indexBestSalesBatch((int) $cursor, $length, $days);
            if ($cursor === 0) {
                $lastCursor = $cursor;
                break;
            }
            $time_elapsed = microtime(true) - $startTime;
            $indexedProducts += $length;
        } while (
            $cursor < $nbProducts
            && (Tools::getMemoryLimit() == -1 || Tools::getMemoryLimit() > memory_get_peak_usage())
            && $time_elapsed < $maxExecutiontime
        );

        if ($cursor != $lastCursor && !$ajax) {
            return $this->indexBestSalesRecursive((int) $cursor, $ajax);
        }

        if ($ajax && $nbProducts > 0 && $cursor != $lastCursor) {
            return json_encode([
                'total' => $nbProducts,
                'cursor' => $cursor,
                'count' => $indexedProducts,
            ]);
        }

        if ($ajax) {
            return json_encode([
                'result' => 'ok',
            ]);
        }

        return $nbProducts;
    }

    /**
     * Index best sales for a product batch.
     *
     * @param int $cursor last indexed id_product
     * @param int $length nb of products to index
     * @param int $days sales period in days
     *
     * @return int last indexed id_product
     */
    private function indexBestSalesBatch($cursor, $length, $days)
    {
        $products = $this->getDatabase()->executeS(
            'SELECT p.`id_product`
            FROM `' . _DB_PREFIX_ . 'product` p
            WHERE p.`id_product` > ' . (int) $cursor . '
            ORDER BY p.`id_product`
            LIMIT ' . (int) $length
        );

        if (!$products) {
            return 0;
        }

        $ids = array_map('intval', array_column($products, 'id_product'));
        $idList = implode(',', $ids);
        $lastId = (int) end($ids);

        $this->getDatabase()->execute(
            'INSERT INTO `' . _DB_PREFIX_ . 'gc_facetedsearch_salescache` (`id_product`, `score`)
            SELECT
                p.id_product,
                (IFNULL(od.score, 0) + (1 / DATEDIFF(CURRENT_DATE(), p.`date_add`))) * 1000 AS score
            FROM `' . _DB_PREFIX_ . 'product` AS p
            LEFT JOIN (
                SELECT
                    od.product_id AS id_product,
                    COUNT(od.`id_order_detail`) AS score
                FROM `' . _DB_PREFIX_ . 'order_detail` AS od
                INNER JOIN `' . _DB_PREFIX_ . 'orders` AS o ON (o.`id_order` = od.`id_order`)
                WHERE o.`valid` = 1
                AND o.`date_add` > DATE_SUB(NOW(), INTERVAL ' . (int) $days . ' DAY)
                AND od.product_id IN (' . $idList . ')
                GROUP BY od.`product_id`
            ) AS od ON (od.`id_product` = p.`id_product`)
            WHERE p.id_product IN (' . $idList . ')'
        );

        return $lastId;
    }

    /**
     * Migrate configuration, filter templates and indexable SEO data from ps_facetedsearch,
     * then uninstall the original module. Price/attribute indexes are rebuilt by install().
     *
     * @return bool true when migration run
     */
    private function migrateFromPsFacetedSearch()
    {
        if (!Module::isInstalled(self::PS_FACETEDSEARCH_MODULE)) {
            return false;
        }

        if (!$this->databaseTableExists('layered_filter')) {
            // Module marked installed but tables missing — still try to uninstall it
            $this->uninstallPsFacetedSearchModule();

            return false;
        }

        $this->migratePsConfiguration();
        $this->migratePsFilterTemplates();
        $this->migratePsIndexableTables();

        $this->uninstallPsFacetedSearchModule();

        return true;
    }

    /**
     * @param string $tableWithoutPrefix
     *
     * @return bool
     */
    private function databaseTableExists($tableWithoutPrefix)
    {
        $result = $this->getDatabase()->executeS(
            'SHOW TABLES LIKE "' . _DB_PREFIX_ . pSQL($tableWithoutPrefix) . '"'
        );

        return !empty($result);
    }



    private function migratePsConfiguration()
    {
        $configMap = [
            'PS_LAYERED_CACHE_ENABLED' => 'GC_FACETEDSEARCH_CACHE_ENABLED',
            'PS_LAYERED_SHOW_QTIES' => 'GC_FACETEDSEARCH_SHOW_QUIES',
            'PS_LAYERED_FULL_TREE' => 'GC_FACETEDSEARCH_FULL_TREE',
            'PS_LAYERED_FILTER_PRICE_USETAX' => 'GC_FACETEDSEARCH_FILTER_PRICE_USETAX',
            'PS_LAYERED_FILTER_CATEGORY_DEPTH' => 'GC_FACETEDSEARCH_FILTER_CATEGORY_DEPTH',
            'PS_LAYERED_FILTER_PRICE_ROUNDING' => 'GC_FACETEDSEARCH_FILTER_PRICE_ROUNDING',
            'PS_LAYERED_FILTER_SHOW_OUT_OF_STOCK_LAST' => 'GC_FACETEDSEARCH_FILTER_SHOW_OUT_OF_STOCK_LAST',
            'PS_LAYERED_FILTER_BY_DEFAULT_CATEGORY' => 'GC_FACETEDSEARCH_FILTER_BY_DEFAULT_CATEGORY',
            'PS_LAYERED_DEFAULT_CATEGORY_TEMPLATE' => 'GC_FACETEDSEARCH_DEFAULT_CATEGORY_TEMPLATE',
            'PS_LAYERED_INDEXED' => 'GC_FACETEDSEARCH_INDEXED',
            'PS_USE_JQUERY_UI_SLIDER' => 'GC_FACETEDSEARCH_USE_JQUERY_UI_SLIDER',
        ];

        foreach ($configMap as $oldKey => $newKey) {
            $value = Configuration::get($oldKey);
            if ($value === false) {
                $value = Configuration::getGlobalValue($oldKey);
            }
            if ($value !== false) {
                if ($oldKey === 'PS_LAYERED_INDEXED') {
                    Configuration::updateGlobalValue($newKey, $value);
                } else {
                    Configuration::updateValue($newKey, $value);
                }
            }
        }
    }

    private function migratePsFilterTemplates()
    {
        $db = $this->getDatabase();

        $db->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'gc_facetedsearch_filter`');
        $db->execute(
            'INSERT INTO `' . _DB_PREFIX_ . 'gc_facetedsearch_filter`
            (`id_gc_facetedsearch_filter`, `name`, `filters`, `n_categories`, `date_add`)
            SELECT `id_layered_filter`, `name`, `filters`, `n_categories`, `date_add`
            FROM `' . _DB_PREFIX_ . 'layered_filter`'
        );

        $templates = $db->executeS('SELECT `id_gc_facetedsearch_filter`, `filters` FROM `' . _DB_PREFIX_ . 'gc_facetedsearch_filter`');
        if (is_array($templates)) {
            foreach ($templates as $template) {
                $filters = @unserialize($template['filters']);
                if (!is_array($filters)) {
                    continue;
                }

                $remapped = $this->remapLayeredFilterKeys($filters);
                $db->execute(
                    'UPDATE `' . _DB_PREFIX_ . 'gc_facetedsearch_filter`
                    SET `filters` = "' . pSQL(serialize($remapped)) . '"
                    WHERE `id_gc_facetedsearch_filter` = ' . (int) $template['id_gc_facetedsearch_filter']
                );
            }
        }

        $db->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'gc_facetedsearch_filter_shop`');
        if ($this->databaseTableExists('layered_filter_shop')) {
            $db->execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'gc_facetedsearch_filter_shop`
                (`id_gc_facetedsearch_filter`, `id_shop`)
                SELECT `id_layered_filter`, `id_shop`
                FROM `' . _DB_PREFIX_ . 'layered_filter_shop`'
            );
        }

        $db->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'gc_facetedsearch_category`');
        if ($this->databaseTableExists('layered_category')) {
            // Older schemas may miss controller column
            $columns = $db->executeS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'layered_category`');
            $columnNames = is_array($columns) ? array_column($columns, 'Field') : [];
            if (in_array('controller', $columnNames, true)) {
                $db->execute(
                    'INSERT INTO `' . _DB_PREFIX_ . 'gc_facetedsearch_category`
                    (`id_gc_facetedsearch_category`, `id_shop`, `controller`, `id_category`, `id_value`, `type`, `position`, `filter_type`, `filter_show_limit`)
                    SELECT `id_layered_category`, `id_shop`, `controller`, `id_category`, `id_value`, `type`, `position`, `filter_type`, `filter_show_limit`
                    FROM `' . _DB_PREFIX_ . 'layered_category`'
                );
            } else {
                $db->execute(
                    'INSERT INTO `' . _DB_PREFIX_ . 'gc_facetedsearch_category`
                    (`id_gc_facetedsearch_category`, `id_shop`, `controller`, `id_category`, `id_value`, `type`, `position`, `filter_type`, `filter_show_limit`)
                    SELECT `id_layered_category`, `id_shop`, \'category\', `id_category`, `id_value`, `type`, `position`, `filter_type`, `filter_show_limit`
                    FROM `' . _DB_PREFIX_ . 'layered_category`'
                );
            }
        }
    }

    /**
     * Remap ps_facetedsearch layered_selection_* keys to gc_facetedsearch filter_* keys.
     */
    private function remapLayeredFilterKeys(array $filters): array
    {
        $staticMap = [
            'layered_selection_subcategories' => 'filter_subcategories',
            'layered_selection_stock' => 'filter_stock',
            'layered_selection_condition' => 'filter_condition',
            'layered_selection_manufacturer' => 'filter_manufacturer',
            'layered_selection_weight_slider' => 'filter_weight_slider',
            'layered_selection_price_slider' => 'filter_price_slider',
            'layered_selection_extras' => 'filter_extras',
        ];

        $remapped = [];
        foreach ($filters as $key => $value) {
            if (isset($staticMap[$key])) {
                $key = $staticMap[$key];
            } elseif (strpos($key, 'layered_selection_ag_') === 0) {
                $key = 'filter_attribute_group_' . substr($key, strlen('layered_selection_ag_'));
            } elseif (strpos($key, 'layered_selection_feat_') === 0) {
                $key = 'filter_feature_' . substr($key, strlen('layered_selection_feat_'));
            }

            $remapped[$key] = $value;
        }

        return $remapped;
    }

    private function migratePsIndexableTables()
    {
        $db = $this->getDatabase();

        $copies = [
            'layered_indexable_attribute_group' => [
                'gc_facetedsearch_indexable_attribute_group',
                '(`id_attribute_group`, `indexable`) SELECT `id_attribute_group`, `indexable`',
            ],
            'layered_indexable_attribute_group_lang_value' => [
                'gc_facetedsearch_indexable_attribute_group_lang_value',
                '(`id_attribute_group`, `id_lang`, `url_name`, `meta_title`) SELECT `id_attribute_group`, `id_lang`, `url_name`, `meta_title`',
            ],
            'layered_indexable_attribute_lang_value' => [
                'gc_facetedsearch_indexable_attribute_lang_value',
                '(`id_attribute`, `id_lang`, `url_name`, `meta_title`) SELECT `id_attribute`, `id_lang`, `url_name`, `meta_title`',
            ],
            'layered_indexable_feature' => [
                'gc_facetedsearch_indexable_feature',
                '(`id_feature`, `indexable`) SELECT `id_feature`, `indexable`',
            ],
            'layered_indexable_feature_lang_value' => [
                'gc_facetedsearch_indexable_feature_lang_value',
                '(`id_feature`, `id_lang`, `url_name`, `meta_title`) SELECT `id_feature`, `id_lang`, `url_name`, `meta_title`',
            ],
            'layered_indexable_feature_value_lang_value' => [
                'gc_facetedsearch_indexable_feature_value_lang_value',
                '(`id_feature_value`, `id_lang`, `url_name`, `meta_title`) SELECT `id_feature_value`, `id_lang`, `url_name`, `meta_title`',
            ],
        ];

        foreach ($copies as $oldTable => $spec) {
            list($newTable, $selectPart) = $spec;
            $db->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . $newTable . '`');
            if ($this->databaseTableExists($oldTable)) {
                $db->execute(
                    'INSERT INTO `' . _DB_PREFIX_ . $newTable . '` ' . $selectPart .
                    ' FROM `' . _DB_PREFIX_ . $oldTable . '`'
                );
            }
        }
    }
}
