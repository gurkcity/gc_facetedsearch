<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

namespace Onlineshopmodule\PrestaShop\Module\Facetedsearch\Model;

use ObjectModel;
use Shop;

/**
 * ObjectModel for table gc_facetedsearch_filter (+ shop association).
 */
class FacetedSearchFilter extends ObjectModel
{
    /** @var string */
    public $name;

    /** @var string|null Serialized filter configuration */
    public $filters;

    /** @var int */
    public $n_categories;

    /** @var string */
    public $date_add;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'gc_facetedsearch_filter',
        'primary' => 'id_gc_facetedsearch_filter',
        'multishop' => true,
        'fields' => [
            'name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
                'size' => 64,
            ],
            'filters' => [
                'type' => self::TYPE_STRING,
                'required' => false,
                'allow_null' => true,
                'size' => 4294967295,
            ],
            'n_categories' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => true,
            ],
        ],
    ];

    public function __construct($id = null, $id_lang = null, $id_shop = null, $translator = null)
    {
        Shop::addTableAssociation(self::$definition['table'], ['type' => 'shop']);

        parent::__construct($id, $id_lang, $id_shop, $translator);
    }
}
