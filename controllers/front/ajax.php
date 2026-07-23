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

use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Response\Json;

/**
 * @property GC_Facetedsearch $module
 */
class GC_FacetedsearchAjaxModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    protected $logger;
    protected $response;

    public function __construct()
    {
        parent::__construct();

        $this->ajax = true;
        $this->response = new Json();

        $this->logger = $this->module->getLogger()->getInstanceOrCreate('ajax');
    }

    // Default behavior for ajax process is to use $_POST[action] or $_GET[action]
    // then using displayAjax[action]
    public function displayAjaxAction()
    {
        /*
         * Do something
         */

        $this->displayAjax();
    }

    public function displayAjax()
    {
        $this->response->echo();
    }
}
