<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

namespace Onlineshopmodule\PrestaShop\Module\Facetedsearch\Controller;

use PrestaShop\PrestaShop\Adapter\LegacyContext;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopBundle\Security\Annotation\ModuleActivated;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ModuleActivated(moduleName="gc_facetedsearch", redirectRoute="gc_facetedsearch_license")
 */
class CronAdminController extends AdminController
{
    /**
     * @AdminSecurity(
     *     "is_granted('read', request.get('_legacy_controller')) && is_granted('update', request.get('_legacy_controller')) && is_granted('create', request.get('_legacy_controller')) && is_granted('delete', request.get('_legacy_controller'))",
     *     message="Access denied."
     * )
     */
    public function cronAction(
        Request $request,
        #[Autowire(service: 'prestashop.adapter.legacy.context')]
        LegacyContext $legacyContext
    ) {
        $context = $legacyContext->getContext();

        $cronToken = substr(\Tools::hash('gc_facetedsearch/index'), 0, 10);

        return $this->render('views/templates/admin/cron/cron.html.twig', [
            'price_indexer_url_for_cron' => $context->link->getModuleLink('gc_facetedsearch', 'cron', ['action' => 'indexPrices', 'token' => $cronToken]),
            'full_price_indexer_url_for_cron' => $context->link->getModuleLink('gc_facetedsearch', 'cron', ['action' => 'indexPrices', 'token' => $cronToken]),
            'attribute_indexer_url_for_cron' => $context->link->getModuleLink('gc_facetedsearch', 'cron', ['action' => 'indexAttributes', 'token' => $cronToken]),
            'clear_cache_url_for_cron' => $context->link->getModuleLink('gc_facetedsearch', 'cron', ['action' => 'clearCache', 'token' => $cronToken]),
            'best_sales_indexer_url_for_cron' => $context->link->getModuleLink('gc_facetedsearch', 'cron', ['action' => 'indexBestSales', 'token' => $cronToken]),

            'price_indexer_url' => $context->link->getModuleLink('gc_facetedsearch', 'cron', ['ajax' => true, 'action' => 'indexPrices', 'token' => $cronToken]),
            'full_price_indexer_url' => $context->link->getModuleLink('gc_facetedsearch', 'cron', ['ajax' => true, 'action' => 'indexPrices', 'full' => 1, 'token' => $cronToken]),
            'attribute_indexer_url' => $context->link->getModuleLink('gc_facetedsearch', 'cron', ['ajax' => true, 'action' => 'indexAttributes', 'token' => $cronToken]),
            'clear_cache_url' => $context->link->getModuleLink('gc_facetedsearch', 'cron', ['ajax' => true, 'action' => 'clearCache', 'token' => $cronToken]),
            'best_sales_indexer_url' => $context->link->getModuleLink('gc_facetedsearch', 'cron', ['ajax' => true, 'action' => 'indexBestSales', 'token' => $cronToken]),
        ]);
    }
}
