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

use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Maintenance\Maintenance;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Plugin\PluginLoaderFactory;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopBundle\Security\Annotation\ModuleActivated;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ModuleActivated(moduleName="gc_facetedsearch", redirectRoute="gc_facetedsearch_license")
 */
class MaintenanceAdminController extends AdminController
{
    /**
     * @AdminSecurity(
     *     "is_granted('read', request.get('_legacy_controller')) && is_granted('update', request.get('_legacy_controller')) && is_granted('create', request.get('_legacy_controller')) && is_granted('delete', request.get('_legacy_controller'))",
     *     message="Access denied."
     * )
     */
    public function maintenanceAction(Request $request, Maintenance $maintenance)
    {
        $this->setLayoutTitle($this->trans('Maintenance', [], 'Modules.Gcfacetedsearch.Admin'));

        return $this->render('views/templates/admin/maintenance/maintenance.html.twig', [
            'hooks' => $maintenance->getHooks(),
            'tabs' => $maintenance->getTabs(),
            'sql' => $maintenance->getSql(),
            'config' => $maintenance->getConfig(),
            'controller' => $maintenance->getController(),
            'orderstates' => $maintenance->getOrderstates(),
            'templates' => $maintenance->getTemplates(),
            'caching' => $maintenance->getCaching(),
            'themeName' => $this->getShopContext()->getThemeName(),
            'shopName' => $this->getShopContext()->getName(),
            'shopId' => $this->getShopContext()->getId(),
            'isMultiShopEnabled' => $this->getShopContext()->isMultiShopEnabled(),
            'plugins' => $this->getPlugins(),
            'php_version' => PHP_VERSION,
            'module_version' => $this->module->version,
            'module_gcversion' => $this->module->getGCModuleVersion(),
            'module_gcsubversion' => $this->module->getGCModuleSubversion(),
        ]);
    }

    /**
     * @AdminSecurity(
     *     "is_granted('update', request.get('_legacy_controller'))",
     *     message="Access denied."
     * )
     */
    public function maintenanceHooksResetAction(Request $request, Maintenance $maintenance)
    {
        if (!$maintenance->resetHooks()) {
            $this->addFlash('error', $this->trans('Reset failed on some hooks!', [], 'Modules.Gcfacetedsearch.Admin'));

            $this->logger->error('Reset failed on some hooks!');
        } else {
            $this->addFlash('success', $this->trans('All hooks have been reset successfully', [], 'Modules.Gcfacetedsearch.Admin'));

            $this->logger->info('All hooks have been reset successfully');
        }

        return $this->redirectToRoute('gc_facetedsearch_maintenance');
    }

    /**
     * @AdminSecurity(
     *     "is_granted('update', request.get('_legacy_controller'))",
     *     message="Access denied."
     * )
     */
    public function maintenanceTabsResetAction(Request $request, Maintenance $maintenance)
    {
        if (!$maintenance->resetTabs()) {
            $this->addFlash('error', $this->trans('Reset failed on some tabs!', [], 'Modules.Gcfacetedsearch.Admin'));

            $this->logger->error('Reset failed on some tabs!');
        } else {
            $this->addFlash('success', $this->trans('All tabs have been reset successfully', [], 'Modules.Gcfacetedsearch.Admin'));

            $this->logger->info('All tabs have been reset successfully');
        }

        return $this->redirectToRoute('gc_facetedsearch_maintenance');
    }

    /**
     * @AdminSecurity(
     *     "is_granted('update', request.get('_legacy_controller'))",
     *     message="Access denied."
     * )
     */
    public function maintenanceSqlResetAction(Request $request, Maintenance $maintenance)
    {
        try {
            $maintenance->resetSql();

            $this->addFlash('success', $this->trans('SQL queries has been executed successfully', [], 'Modules.Gcfacetedsearch.Admin'));

            $this->logger->info('SQL queries have been executed successfully');
        } catch (\Throwable $e) {
            $this->addFlash('error', $this->trans('Reset SQL failed! %error%', ['%error%' => $e->getMessage()], 'Modules.Gcfacetedsearch.Admin'));

            $this->logger->error('Reset SQL failed: ' . $e->getMessage());
        }

        return $this->redirectToRoute('gc_facetedsearch_maintenance');
    }

    /**
     * @AdminSecurity(
     *     "is_granted('update', request.get('_legacy_controller'))",
     *     message="Access denied."
     * )
     */
    public function maintenanceConfigResetAction(Request $request, Maintenance $maintenance)
    {
        if (!$maintenance->resetConfig()) {
            $this->addFlash('error', $this->trans('Install missing configuration failed!', [], 'Modules.Gcfacetedsearch.Admin'));

            $this->logger->error('Install missing configuration failed!');
        } else {
            $this->addFlash('success', $this->trans('Missing configuration installed successfully', [], 'Modules.Gcfacetedsearch.Admin'));

            $this->logger->info('Missing configuration installed successfully');
        }

        return $this->redirectToRoute('gc_facetedsearch_maintenance');
    }

    /**
     * @AdminSecurity(
     *     "is_granted('update', request.get('_legacy_controller'))",
     *     message="Access denied."
     * )
     */
    public function maintenanceControllerResetAction(Request $request, Maintenance $maintenance)
    {
        if (!$maintenance->resetController()) {
            $this->addFlash('error', $this->trans('Reset of controller failed!', [], 'Modules.Gcfacetedsearch.Admin'));

            $this->logger->error('Reset of controller failed!');
        } else {
            $this->addFlash('success', $this->trans('Controller reseted successfully', [], 'Modules.Gcfacetedsearch.Admin'));

            $this->logger->info('Controller reseted successfully');
        }

        return $this->redirectToRoute('gc_facetedsearch_maintenance');
    }

    /**
     * @AdminSecurity(
     *     "is_granted('update', request.get('_legacy_controller'))",
     *     message="Access denied."
     * )
     */
    public function maintenanceOrderstatesResetAction(Request $request, Maintenance $maintenance)
    {
        if (!$maintenance->resetOrderstates()) {
            $this->addFlash('error', $this->trans('Reset of order states failed!', [], 'Modules.Gcfacetedsearch.Admin'));

            $this->logger->error('Reset of order states failed!');
        } else {
            $this->addFlash('success', $this->trans('Order states reseted successfully', [], 'Modules.Gcfacetedsearch.Admin'));

            $this->logger->info('Order states reseted successfully');
        }

        return $this->redirectToRoute('gc_facetedsearch_maintenance');
    }

    /**
     * @AdminSecurity(
     *     "is_granted('update', request.get('_legacy_controller'))",
     *     message="Access denied."
     * )
     */
    public function maintenanceCachingClearAction(Request $request, Maintenance $maintenance)
    {
        if (!$maintenance->clearCaching()) {
            $this->addFlash('error', $this->trans('Reset of caching failed!', [], 'Modules.Gcfacetedsearch.Admin'));

            $this->logger->error('Reset of caching failed!');
        } else {
            $this->addFlash('success', $this->trans('Caching reset successfully', [], 'Modules.Gcfacetedsearch.Admin'));
            $this->logger->info('Caching reseted successfully');
        }

        return $this->redirectToRoute('gc_facetedsearch_maintenance');
    }

    private function getPlugins(): array
    {
        try {
            return PluginLoaderFactory::getInstance()->getPluginsInformation();
        } catch (\Exception $e) {
            $this->logger->warning('Error loading plugins: ' . $e->getMessage());

            return [];
        }
    }
}
