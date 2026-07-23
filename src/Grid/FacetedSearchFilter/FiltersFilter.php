<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

namespace Onlineshopmodule\PrestaShop\Module\Facetedsearch\Grid\FacetedSearchFilter;

use PrestaShop\PrestaShop\Core\Search\Filters;

class FiltersFilter extends Filters
{
	protected $filterId = FiltersGridDefinitionFactory::GRID_ID;

    public static function getDefaults(): array
    {
        return [
            'limit' => 50,
            'offset' => 0,
            'orderBy' => 'a.name',
            'sortOrder' => 'asc',
            'filters' => [],
        ];
    }
}
