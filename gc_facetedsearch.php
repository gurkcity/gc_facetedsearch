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
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Traits\ModuleFunctionsTrait;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\HookDispatcher;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

class GC_Facetedsearch extends Module
{
    use ModuleTrait;
    use ModuleFunctionsTrait;
    use ModuleHelperTrait;
    use ModuleLicenseTrait;

    /**
     * @var string Name of the module running on PS 1.6.x. Used for data migration.
     */
    const PS_16_EQUIVALENT_MODULE = 'blocklayered';

    /**
     * @var string Official PrestaShop faceted search module to migrate from.
     */
    const PS_FACETEDSEARCH_MODULE = 'ps_facetedsearch';

    /**
     * Lock indexation if too many products
     *
     * @var int
     */
    const LOCK_TOO_MANY_PRODUCTS = 5000;

    /**
     * Lock template filter creation if too many products
     *
     * @var int
     */
    const LOCK_TEMPLATE_CREATION = 20000;

    /**
     * US iso code, used to prevent taxes usage while computing prices
     *
     * @var array
     */
    const ISO_CODE_TAX_FREE = [
        'US',
    ];

    /**
     * Number of digits for MySQL DECIMAL
     *
     * @var int
     */
    const DECIMAL_DIGITS = 6;

    /**
     * @var bool
     */
    private $ajax;

    /**
     * @var int
     */
    private $gcLayeredFullTree;

    /**
     * @var Db
     */
    protected $database;

    /**
     * @var HookDispatcher
     */
    protected $hookDispatcher;

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
        $this->displayNamePre = $this->trans('Faceted', [], 'Modules.Gcfacetedsearch.Admin');
        $this->displayNamePost = $this->trans('Search', [], 'Modules.Gcfacetedsearch.Admin');
        $this->description = $this->trans('Filter your catalog to help visitors picture the category tree and browse your store easily.', [], 'Modules.Gcfacetedsearch.Admin');
        $this->description_full = $this->trans('Filter your catalog to help visitors picture the category tree and browse your store easily.', [], 'Modules.Gcfacetedsearch.Admin');

        parent::__construct();

        $this->initModule();
    }

    public function parentInitModule()
    {
        $this->hookDispatcher = new HookDispatcher($this);
        $this->initializeSupportedControllers();
    }

    /**
     * Dispatch hooks
     *
     * @param string $methodName
     * @param array $arguments
     */
    public function __call($methodName, array $arguments)
    {
        if (strpos($methodName, 'hook') === false) {
            throw new Exception('Call missing method ::' . $methodName);
        }

        return $this->getHookDispatcher()->dispatch(
            $methodName,
            !empty($arguments[0]) ? $arguments[0] : []
        );
    }

    /**
     * @return HookDispatcher
     */
    public function getHookDispatcher()
    {
        return $this->hookDispatcher;
    }
}
