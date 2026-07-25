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

use Exception;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Form\Type\FilterTemplateType;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopBundle\Security\Annotation\ModuleActivated;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\Builder\FormBuilderInterface;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\Handler\FormHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @ModuleActivated(moduleName="gc_facetedsearch", redirectRoute="gc_facetedsearch_license")
 */
class FilterTemplateAdminController extends AdminController
{
    /**
     * Create a new filter template.
     *
     * @AdminSecurity(
     *     "is_granted('create', request.get('_legacy_controller'))",
     *     message="Access denied."
     * )
     */
    public function addAction(
        Request $request,
        #[Autowire(service: 'onlineshopmodule.module.facetedsearch.form.identifiable_object.handler.filter_template_form_handler')]
        FormHandlerInterface $formHandler
    ): Response {
        $this->setLayoutTitle($this->trans('Add filter template', [], 'Modules.Gcfacetedsearch.Admin'));

        try {
            $filterTemplateForm = $this->createForm(
                FilterTemplateType::class,
                [
                    'name' => '',
                    'categories' => [],
                    'shop_association' => [],
                    'filters' => [],
                ]
            );

            $filterTemplateForm->handleRequest($request);

            $formHandler->handle($filterTemplateForm);

            if ($filterTemplateForm->isSubmitted() && $filterTemplateForm->isValid()) {
                // $this->clearSmartyCache('*');

                $this->addFlash('success', $this->trans('Successful creation', [], 'Admin.Notifications.Success'));

                return $this->redirectToRoute('gc_facetedsearch_configuration');
            }
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render(
            'views/templates/admin/filter_template/form.html.twig',
            [
                'filterTemplateForm' => $filterTemplateForm->createView(),
                'layoutTitle' => $this->trans('Add filter template', [], 'Modules.Gcfacetedsearch.Admin'),
            ]
        );
    }   

    /**
     * Edit an existing filter template.
     *
     * @AdminSecurity(
     *     "is_granted('update', request.get('_legacy_controller'))",
     *     message="Access denied."
     * )
     */
    public function editAction(Request $request, int $idTemplate): Response
    {
        $this->setLayoutTitle($this->trans('Edit filter template', [], 'Modules.Gcfacetedsearch.Admin'));

        // TODO: load template by $idTemplate, render edit form and save changes

        return $this->redirectToRoute('gc_facetedsearch_configuration');
    }

    /**
     * Delete an existing filter template.
     *
     * @AdminSecurity(
     *     "is_granted('delete', request.get('_legacy_controller'))",
     *     message="Access denied."
     * )
     */
    public function deleteAction(Request $request, int $idTemplate): Response
    {
        // TODO: delete template by $idTemplate and flash result message

        return $this->redirectToRoute('gc_facetedsearch_configuration');
    }
}
