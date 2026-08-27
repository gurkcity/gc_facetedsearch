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
                $this->module->cronIndexAttributes();

                $this->ajaxRender('1');
                break;
            case 'clearCache':
                $result = $this->module->cronClearCache();

                $this->ajaxRender($result);
                break;
            case 'indexPrices':
                $result = $this->module->cronIndexPrices((bool) Tools::getValue('full'));

                $this->ajaxRender($result);
                break;
            case 'indexBestSales':
                $result = $this->module->cronIndexBestsales();

                $this->ajaxRender($result);
                break;
            default:
                header('HTTP/1.1 403 Forbidden');
                header('Status: 403 Forbidden');

                $this->ajaxRender('Unknown action');
        }
    }
}
