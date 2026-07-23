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

use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Log\LogLevel;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;

class LogFormDataProvider implements FormDataProviderInterface
{
    public function __construct(
        protected LogLevel $logLevel
    ) {
    }

    public function getData()
    {
        return [
            'loglevel' => $this->logLevel->get(),
        ];
    }

    public function setData(array $data)
    {
        $this->logLevel->set((int) $data['loglevel']);

        return [];
    }
}
