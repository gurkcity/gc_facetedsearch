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

        return [];
    }
}
