<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

namespace Onlineshopmodule\PrestaShop\Module\Facetedsearch\Form\DataProvider;

use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Form\AbstractFormDataProvider;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;

class ConfigurationFormDataProvider extends AbstractFormDataProvider implements FormDataProviderInterface
{
    public function getData()
    {
        return [
            'js_defer' => $this->configurationAdapter->get('JS_DEFER'),
            'theme' => $this->configurationAdapter->get('THEME'),
            'CACHE_ENABLED' => $this->configurationAdapter->get('CACHE_ENABLED'),
            'SHOW_QUIES' => $this->configurationAdapter->get('SHOW_QUIES'),
            'FULL_TREE' => $this->configurationAdapter->get('FULL_TREE'),
            'FILTER_BY_DEFAULT_CATEGORY' => $this->configurationAdapter->get('FILTER_BY_DEFAULT_CATEGORY'),
            'FILTER_CATEGORY_DEPTH' => $this->configurationAdapter->get('FILTER_CATEGORY_DEPTH'),
            'FILTER_PRICE_USETAX' => $this->configurationAdapter->get('FILTER_PRICE_USETAX'),
            'FILTER_PRICE_ROUNDING' => $this->configurationAdapter->get('FILTER_PRICE_ROUNDING'),
            'FILTER_SHOW_OUT_OF_STOCK_LAST' => $this->configurationAdapter->get('FILTER_SHOW_OUT_OF_STOCK_LAST'),
            'USE_JQUERY_UI_SLIDER' => $this->configurationAdapter->get('USE_JQUERY_UI_SLIDER'),
            'DEFAULT_CATEGORY_TEMPLATE' => $this->configurationAdapter->get('DEFAULT_CATEGORY_TEMPLATE'),
        ];
    }

    public function setData(array $data)
    {
        if (isset($data['js_defer'])) {
            $this->updateConfiguration('JS_DEFER', 'js_defer', $data);
        }

        if (isset($data['theme'])) {
            $this->updateConfiguration('THEME', 'theme', $data);
        }

        if (isset($data['CACHE_ENABLED'])) {
            $this->updateConfiguration('CACHE_ENABLED', 'CACHE_ENABLED', $data);
        }
        if (isset($data['SHOW_QUIES'])) {
            $this->updateConfiguration('SHOW_QUIES', 'SHOW_QUIES', $data);
        }
        if (isset($data['FULL_TREE'])) {
            $this->updateConfiguration('FULL_TREE', 'FULL_TREE', $data);
        }
        if (isset($data['FILTER_BY_DEFAULT_CATEGORY'])) {
            $this->updateConfiguration('FILTER_BY_DEFAULT_CATEGORY', 'FILTER_BY_DEFAULT_CATEGORY', $data);
        }
        if (isset($data['FILTER_CATEGORY_DEPTH'])) {
            $this->updateConfiguration('FILTER_CATEGORY_DEPTH', 'FILTER_CATEGORY_DEPTH', $data);
        }
        if (isset($data['FILTER_PRICE_USETAX'])) {
            $this->updateConfiguration('FILTER_PRICE_USETAX', 'FILTER_PRICE_USETAX', $data);
        }
        if (isset($data['FILTER_PRICE_ROUNDING'])) {
            $this->updateConfiguration('FILTER_PRICE_ROUNDING', 'FILTER_PRICE_ROUNDING', $data);
        }
        if (isset($data['FILTER_SHOW_OUT_OF_STOCK_LAST'])) {
            $this->updateConfiguration('FILTER_SHOW_OUT_OF_STOCK_LAST', 'FILTER_SHOW_OUT_OF_STOCK_LAST', $data);
        }
        if (isset($data['USE_JQUERY_UI_SLIDER'])) {
            $this->updateConfiguration('USE_JQUERY_UI_SLIDER', 'USE_JQUERY_UI_SLIDER', $data);
        }
        if (isset($data['DEFAULT_CATEGORY_TEMPLATE'])) {
            $this->updateConfiguration('DEFAULT_CATEGORY_TEMPLATE', 'DEFAULT_CATEGORY_TEMPLATE', $data);
        }

        return [];
    }
}
