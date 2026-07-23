<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

namespace Onlineshopmodule\PrestaShop\Module\Facetedsearch\Plugin;

class PluginLoaderFactory
{
    public const PLUGIN_FOLDER = 'plugins/';

    private static $instance;

    public static function getInstance(): PluginLoader
    {
        if (self::$instance === null) {
            self::$instance = new PluginLoader(
                __DIR__ . '/../../' . self::PLUGIN_FOLDER,
                \Context::getContext()
            );
        }

        return self::$instance;
    }
}
