<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Traits\ModuleHelperTrait;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Traits\ModuleLicenseTrait;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Traits\ModuleTrait;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

class GC_Facetedsearch extends Module
{
    use ModuleTrait;
    use ModuleHelperTrait;
    use ModuleLicenseTrait;

    public function __construct()
    {
        $this->version = '9.0.0';

        $this->name = 'gc_facetedsearch';

        $this->author = 'Gurkcity';

        $this->ps_versions_compliancy = [
            'min' => '9.0.0',
            'max' => _PS_VERSION_,
        ];

        $this->tab = 'front_office_features';

        $this->displayName = $this->trans('GC Facetedsearch', [], 'Modules.Gcfacetedsearch.Admin');
        $this->displayNamePre = $this->trans('Module Name 1', [], 'Modules.Gcfacetedsearch.Admin');
        $this->displayNamePost = $this->trans('Module Name 2', [], 'Modules.Gcfacetedsearch.Admin');
        $this->description = $this->trans('Module Description', [], 'Modules.Gcfacetedsearch.Admin');
        $this->description_full = $this->trans('Module Description Extended', [], 'Modules.Gcfacetedsearch.Admin');

        parent::__construct();

        $this->initModule();
    }
}
