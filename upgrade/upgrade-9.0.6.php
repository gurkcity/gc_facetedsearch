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
function upgrade_module_9_0_6($module)
{
    try {
        $result = true;

        $settings = $module->getSettings();
        $tabs = $settings->getTabs();

        foreach ($tabs as $tab) {
            Db::getInstance()->execute('
                UPDATE `' . _DB_PREFIX_ . 'tab` SET
                    `icon` = \'' . pSQL($tab->getIcon()) . '\'
                WHERE `class_name` = \'' . pSQL($tab->getClassName()) . '\'
            ');
        }

        return (bool) $result;
    } catch (Exception $e) {
        /* @phpstan-ignore property.notFound */
        $module->getLogger()->upgrade->error($e->getMessage());

        return false;
    }
}
