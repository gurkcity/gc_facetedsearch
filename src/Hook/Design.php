<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

namespace Onlineshopmodule\PrestaShop\Module\Facetedsearch\Hook;

class Design extends AbstractHook
{
    const AVAILABLE_HOOKS = [
        'displayLeftColumn',
    ];

    /**
     * Force this hook to be called here instance of using WidgetInterface
     * because Hook::isHookCallableOn before the instanceof function.
     * Which means is_callable always returns true with a __call usage.
     *
     * @param array $params
     */
    public function displayLeftColumn(array $params)
    {
        return $this->module->smartyAssign(
            [],
            'module:gc_facetedsearch/views/templates/hook/displayLeftColumn.tpl'
        );
    }
}
