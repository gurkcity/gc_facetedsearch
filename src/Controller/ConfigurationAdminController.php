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

use PrestaShop\PrestaShop\Core\Form\FormHandlerInterface;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopBundle\Security\Annotation\ModuleActivated;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @ModuleActivated(moduleName="gc_facetedsearch", redirectRoute="gc_facetedsearch_license")
 */
class ConfigurationAdminController extends AdminController
{
    /**
     * @AdminSecurity(
     *     "is_granted('read', request.get('_legacy_controller')) && is_granted('update', request.get('_legacy_controller')) && is_granted('create', request.get('_legacy_controller')) && is_granted('delete', request.get('_legacy_controller'))",
     *     message="Access denied."
     * )
     */
    public function indexAction(
        Request $request,
        #[Autowire(service: 'onlineshopmodule.module.facetedsearch.form.handler.configuration')]
        FormHandlerInterface $configurationFormHandler
    ): Response {
        $this->setLayoutTitle($this->trans('Configuration', [], 'Modules.Gcfacetedsearch.Admin'));

        if ($this->module->redirectAdminConfigurationPermanentTo) {
            return $this->redirectToRoute($this->module->redirectAdminConfigurationPermanentTo);
        }

        return $this->processForm(
            $request,
            $configurationFormHandler,
            'gc_facetedsearch_configuration',
            'views/templates/admin/configuration.html.twig'
        );
    }
}
