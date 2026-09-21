<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param GC_Facetedsearch $module
 *
 * @return bool
 */
function upgrade_module_9_0_1($module)
{
    try {
        $module->getConfig()->set('FILTER_FEATURE_VALUES_USE_POSITION', 0);

        return true;
    } catch (Exception $e) {
        /* @phpstan-ignore property.notFound */
        $module->getLogger()->upgrade->error($e->getMessage());

        return false;
    }
}
