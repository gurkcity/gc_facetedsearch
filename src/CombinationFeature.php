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

use Context;
use PrestaShop\PrestaShop\Adapter\ContainerFinder;
use Throwable;

/**
 * Tells whether the faceted search must also take combination (product_attribute) feature values
 * into account, in addition to the product ones.
 *
 * This is only available from PrestaShop 9.3 (the version that introduced feature values at
 * combination level) and must additionally be turned on through the "combination_feature_values"
 * feature flag.
 */
class CombinationFeature
{
    /**
     * Name of the core feature flag guarding combination feature values.
     */
    public const FEATURE_FLAG = 'combination_feature_values';

    /**
     * Minimum PrestaShop version exposing combination feature values.
     */
    public const MIN_PS_VERSION = '9.3.0';

    /**
     * @var bool|null
     */
    private static $enabled;

    /**
     * @return bool
     */
    public static function isFilteringEnabled()
    {
        if (self::$enabled !== null) {
            return self::$enabled;
        }

        self::$enabled = false;

        if (version_compare(_PS_VERSION_, self::MIN_PS_VERSION, '<')) {
            return self::$enabled;
        }

        try {
            /** @var \Psr\Container\ContainerInterface $container */
            $container = (new ContainerFinder(Context::getContext()))->getContainer();
            $checker = $container->get('PrestaShop\\PrestaShop\\Core\\FeatureFlag\\FeatureFlagStateCheckerInterface');
            self::$enabled = $checker !== null && $checker->isEnabled(self::FEATURE_FLAG);
        } catch (Throwable $e) {
            self::$enabled = false;
        }

        return self::$enabled;
    }

    /**
     * Resets the memoized state, mostly useful for tests.
     */
    public static function resetCache()
    {
        self::$enabled = null;
    }
}
