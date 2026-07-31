<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

namespace Onlineshopmodule\PrestaShop\Module\Facetedsearch;

use GC_Facetedsearch;

/**
 * Class works with Hook\AbstractHook instances in order to reduce gc_facetedsearch.php size.
 *
 * The dispatch method is called from the __call method in the module class.
 */
class HookDispatcher
{
    const CLASSES = [
        Hook\Attribute::class,
        Hook\AttributeGroup::class,
        Hook\Category::class,
        Hook\Configuration::class,
        Hook\Design::class,
        Hook\Feature::class,
        Hook\FeatureValue::class,
        Hook\Product::class,
        Hook\ProductSearch::class,
        Hook\SpecificPrice::class,
    ];

    /**
     * List of available hooks
     *
     * @var string[]
     */
    private $availableHooks = [];

    /**
     * Hook classes
     *
     * @var Hook\AbstractHook[]
     */
    private $hooks = [];

    /**
     * Module
     *
     * @var GC_Facetedsearch
     */
    private $module;

    /**
     * Init hooks
     *
     * @param GC_Facetedsearch $module
     */
    public function __construct(GC_Facetedsearch $module)
    {
        $this->module = $module;

        foreach (self::CLASSES as $hookClass) {
            $hook = new $hookClass($this->module);
            $this->availableHooks = array_merge($this->availableHooks, $hook->getAvailableHooks());
            $this->hooks[] = $hook;
        }
    }

    /**
     * Get available hooks
     *
     * @return string[]
     */
    public function getAvailableHooks()
    {
        return $this->availableHooks;
    }

    /**
     * Find hook and dispatch it
     *
     * @param string $hookName
     * @param array $params
     *
     * @return mixed
     */
    public function dispatch($hookName, array $params = [])
    {
        $hookName = preg_replace('~^hook~', '', $hookName);

        foreach ($this->hooks as $hook) {
            if (method_exists($hook, $hookName)) {
                return call_user_func([$hook, $hookName], $params);
            }
        }

        // No hook found, render it as a widget
        return $this->module->renderWidget($hookName, $params);
    }
}
