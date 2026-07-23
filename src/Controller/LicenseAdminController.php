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
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LicenseAdminController extends AdminController
{
    /**
     * @AdminSecurity(
     *     "is_granted('read', request.get('_legacy_controller')) && is_granted('update', request.get('_legacy_controller')) && is_granted('create', request.get('_legacy_controller')) && is_granted('delete', request.get('_legacy_controller'))",
     *     message="Access denied."
     * )
     */
    public function indexAction(
        Request $request,
        #[Autowire(service: 'onlineshopmodule.module.facetedsearch.form.handler.license')]
        FormHandlerInterface $configurationFormHandler,
    ) {
        $this->setLayoutTitle($this->trans('License', [], 'Modules.Gcfacetedsearch.Admin'));

        return $this->processForm(
            $request,
            $configurationFormHandler,
            'gc_facetedsearch_configuration',
            'views/templates/admin/license/license.html.twig'
        );
    }

    protected function processForm(
        Request $request,
        FormHandlerInterface $formHandler,
        string $redirectRoute = 'gc_facetedsearch_configuration',
        string $template = '',
        array $templateParameters = [],
        array $callbacks = []
    ): Response {
        $form = $formHandler->getForm();
        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
        ) {
            $this->logger->info('Form submitted: ' . $form->getName());

            $saveErrors = $formHandler->save($form->getData());

            if (0 === count($saveErrors)) {
                $this->addFlash('success', $this->trans('Settings saved!', [], 'Modules.Gcfacetedsearch.Admin'));

                $this->logger->info('Form saved successfully');

                return $this->redirectToRoute($redirectRoute);
            } else {
                $this->addFlashErrors($saveErrors);

                $this->logger->error('Form save errors: ' . implode('; ', $saveErrors));
            }
        }

        $templateParameters = array_merge(
            $templateParameters,
            [
                'form' => $form->createView(),
            ]
        );

        $this->setLayoutTitle($this->trans('License', [], 'Modules.Gcfacetedsearch.Admin'));

        return $this->render($template, $templateParameters);
    }

    public function displayLicenseTextAction(Request $request)
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');

        return $this->render('views/templates/admin/license/content.html.twig', [
            'licenseText' => file_get_contents($this->module->getLocalPath() . 'licence.txt'),
        ], $response);
    }
}
