<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

namespace Onlineshopmodule\PrestaShop\Module\Facetedsearch\Product;

use Context;

class SearchFactory
{
    /**
     * Returns an instance of Search for this context
     *
     * @param Context $context
     *
     * @return Search
     */
    public function build(Context $context)
    {
        return new Search($context);
    }
}
