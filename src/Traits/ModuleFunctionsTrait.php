<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

namespace Onlineshopmodule\PrestaShop\Module\Facetedsearch\Traits;

trait ModuleFunctionsTrait
{
    /**
     * @var array|null List of controllers supported by this module
     */
    protected $supportedControllers;

    /**
     * Returns array with all controllers supported by this module
     */
    public function getSupportedControllers(): array
    {
        if ($this->supportedControllers === null) {
            $this->initializeSupportedControllers();
        }
        return $this->supportedControllers;
    }

    public function setSupportedControllers(array $supportedControllers): void
    {
        $this->supportedControllers = $supportedControllers;
    }

    public function initializeSupportedControllers(): void
    {
        $supportedControllers = [
            'category' => [
                'name' => $this->trans('Category', [], 'Modules.Facetedsearch.Admin'),
                'cacheable' => true,
            ],
            'manufacturer' => [
                'name' => $this->trans('Manufacturer', [], 'Modules.Facetedsearch.Admin'),
                'cacheable' => true,
            ],
            'supplier' => [
                'name' => $this->trans('Supplier', [], 'Modules.Facetedsearch.Admin'),
                'cacheable' => true,
            ],
            'new-products' => [
                'name' => $this->trans('New products', [], 'Modules.Facetedsearch.Admin'),
                'cacheable' => false,
            ],
            'best-sales' => [
                'name' => $this->trans('Best sales', [], 'Modules.Facetedsearch.Admin'),
                'cacheable' => false,
            ],
            'prices-drop' => [
                'name' => $this->trans('Prices drop', [], 'Modules.Facetedsearch.Admin'),
                'cacheable' => false,
            ],
            'search' => [
                'name' => $this->trans('Search', [], 'Modules.Facetedsearch.Admin'),
                'cacheable' => false,
            ],
        ];

        \Hook::exec(
            'actionFacetedSearchSetSupportedControllers',
            [
                'supportedControllers' => &$supportedControllers,
            ]
        );

        $this->setSupportedControllers($supportedControllers);
    }
}
