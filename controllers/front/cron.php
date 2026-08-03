<?php

/**
 * GC Facetedsearch
 *
 * Module for Prestashop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

declare(strict_types=1);

class Gc_FacetedSearchCronModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        parent::__construct();
        $this->ajax = true;
    }

    public function postProcess()
    {
        if (substr(Tools::hash('gc_facetedsearch/index'), 0, 10) != Tools::getValue('token')) {
            header('HTTP/1.1 403 Forbidden');
            header('Status: 403 Forbidden');
            $this->ajaxRender('Bad token');

            return;
        }

        $action = Tools::getValue('action');
        switch ($action) {
            case 'indexAttributes':
                Shop::setContext(Shop::CONTEXT_ALL);

                $psFacetedsearch = new GC_Facetedsearch();
                $psFacetedsearch->indexAttributes();
                $psFacetedsearch->indexFeatures();
                $psFacetedsearch->indexAttributeGroup();

                $this->ajaxRender('1');
                break;
            case 'clearCache':
                $psFacetedsearch = new GC_Facetedsearch();
                $this->ajaxRender($psFacetedsearch->invalidateLayeredFilterBlockCache());
                break;
            case 'indexPrices':
                Shop::setContext(Shop::CONTEXT_ALL);

                $module = new GC_Facetedsearch();
                if (Tools::getValue('full')) {
                    $this->ajaxRender($module->fullPricesIndexProcess((int) Tools::getValue('cursor'), (bool) Tools::getValue('ajax'), true));
                } else {
                    $this->ajaxRender($module->pricesIndexProcess((int) Tools::getValue('cursor'), (bool) Tools::getValue('ajax')));
                }

                break;
            case 'indexBestSales':
                Shop::setContext(Shop::CONTEXT_ALL);

                $module = new GC_Facetedsearch();
                $this->ajaxRender(
                    $module->bestSalesIndexProcess(
                        (int) Tools::getValue('cursor'),
                        (bool) Tools::getValue('ajax')
                    )
                );
                break;
            default:
                header('HTTP/1.1 403 Forbidden');
                header('Status: 403 Forbidden');
                $this->ajaxRender('Unknown action');
        }
    }
}
