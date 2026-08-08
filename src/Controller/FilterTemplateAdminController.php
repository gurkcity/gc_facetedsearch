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
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Grid\FacetedSearchFilter\FiltersFilter;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Model\FacetedSearchFilter;
use PrestaShop\PrestaShop\Core\Grid\GridFactory;
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
    public function indexAction(
        Request $request,
        FiltersFilter $filters,
        #[Autowire(service: 'onlineshopmodule.module.facetedsearch.grid.factory.filters')]
        GridFactory $filtersGridFactory
    ): Response {
        $this->setLayoutTitle($this->trans('Filter templates', [], 'Modules.Gcfacetedsearch.Admin'));

        $filtersGrid = $filtersGridFactory->getGrid($filters);

        return $this->render('views/templates/admin/filter_template/index.html.twig', [
            'filtersGrid' => $this->presentGrid($filtersGrid),
        ]);
    }

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

                return $this->redirectToRoute('gc_facetedsearch_filtersgrid');
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
    public function editAction(
        Request $request,
        int $idTemplate,
        #[Autowire(service: 'onlineshopmodule.module.facetedsearch.form.identifiable_object.builder.filter_template_form_builder')]
        FormBuilderInterface $formBuilder,
        #[Autowire(service: 'onlineshopmodule.module.facetedsearch.form.identifiable_object.handler.filter_template_form_handler')]
        FormHandlerInterface $formHandler
    ): Response {
        $this->setLayoutTitle($this->trans('Edit filter template', [], 'Modules.Gcfacetedsearch.Admin'));

        try {
            $templateForm = $formBuilder->getFormFor($idTemplate, []);

            $templateForm->handleRequest($request);

            $formHandler->handleFor($idTemplate, $templateForm);

            if ($templateForm->isSubmitted() && $templateForm->isValid()) {
                // $this->clearSmartyCache('*');

                $this->addFlash('success', $this->trans('Successful update', [], 'Admin.Notifications.Success'));

                return $this->redirectToRoute('gc_facetedsearch_filtersgrid');
            }
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render(
            'views/templates/admin/filter_template/form.html.twig',
            [
                'filterTemplateForm' => $templateForm->createView(),
                'layoutTitle' => $this->trans('Edit filter template', [], 'Modules.Gcfacetedsearch.Admin'),
            ]
        );
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
        try {
            $filterTemplate = new FacetedSearchFilter($idTemplate);
            if (!\Validate::isLoadedObject($filterTemplate)) {
                throw new Exception($this->trans('Filter template not found', [], 'Modules.Gcfacetedsearch.Admin'));
            }

            $templateName = $filterTemplate->name;

            if (!$filterTemplate->delete()) {
                throw new Exception($this->trans('An error occurred while deleting the filter template.', [], 'Modules.Gcfacetedsearch.Admin'));
            }

            $this->resetDefaultCategoryTemplateIfNeeded($idTemplate);
            $this->module->buildLayeredCategories();

            $this->addFlash(
                'success',
                $this->trans(
                    'Filter template deleted, categories updated (reverted to default Filter template).',
                    [],
                    'Modules.Gcfacetedsearch.Admin'
                ) . ' "' . $templateName . '"'
            );
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('gc_facetedsearch_filtersgrid');
    }

    private function resetDefaultCategoryTemplateIfNeeded(int $idTemplate): void
    {
        $defaultTemplateId = (int) $this->config->get('DEFAULT_CATEGORY_TEMPLATE');
        if ($defaultTemplateId === $idTemplate) {
            $this->config->set('DEFAULT_CATEGORY_TEMPLATE', 0);
        }
    }
}
