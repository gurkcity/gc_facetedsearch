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

class SpecificPrice extends AbstractHook
{
    /**
     * @var array
     */
    protected $productsBefore = null;

    const AVAILABLE_HOOKS = [
        'actionObjectSpecificPriceRuleUpdateBefore',
        'actionAdminSpecificPriceRuleControllerSaveAfter',
    ];

    /**
     * Before saving a specific price rule
     *
     * @param array $params
     */
    public function actionObjectSpecificPriceRuleUpdateBefore(array $params)
    {
        if (empty($params['object']->id)) {
            return;
        }

        /** @var \SpecificPriceRule */
        $specificPrice = $params['object'];
        $this->productsBefore = $specificPrice->getAffectedProducts();
    }

    /**
     * After saving a specific price rule
     *
     * @param array $params
     */
    public function actionAdminSpecificPriceRuleControllerSaveAfter(array $params)
    {
        if (empty($params['return']->id) || empty($this->productsBefore)) {
            return;
        }

        /** @var \SpecificPriceRule */
        $specificPrice = $params['return'];
        $affectedProducts = array_merge($this->productsBefore, $specificPrice->getAffectedProducts());
        foreach ($affectedProducts as $product) {
            $this->module->indexProductPrices((int) $product['id_product']);
            $this->module->indexAttributes((int) $product['id_product']);
        }

        $this->module->invalidateLayeredFilterBlockCache();
    }
}
