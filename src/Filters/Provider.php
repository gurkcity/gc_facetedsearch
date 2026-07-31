<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

namespace Onlineshopmodule\PrestaShop\Module\Facetedsearch\Filters;

use Db;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;

/**
 * Class responsible for providing filters configured for current search query
 */
class Provider
{
    /**
     * @var array
     */
    private $filters = [];

    /**
     * @var Db
     */
    private $database;

    public function __construct(Db $database)
    {
        $this->database = $database;
    }

    /**
     * Get filters for current search query
     *
     * @param ProductSearchQuery $query
     * @param int $idShop
     *
     * @return array Filters
     */
    public function getFiltersForQuery(ProductSearchQuery $query, int $idShop)
    {
        if (empty($this->filters)) {
            $this->filters = $this->database->executeS(
            'SELECT type, id_value, filter_show_limit, filter_type FROM ' . _DB_PREFIX_ . 'gc_facetedsearch_category
            WHERE controller = \'' . $query->getQueryType() . '\'
            AND id_category = ' . ($query->getQueryType() == 'category' ? (int) $query->getIdCategory() : 0) . '
            AND id_shop = ' . $idShop . '
            GROUP BY `type`, id_value ORDER BY position ASC'
            );
        }

        return $this->filters;
    }
}
