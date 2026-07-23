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

use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use PrestaShop\PrestaShop\Core\Form\FormHandlerInterface;
use PrestaShopBundle\Controller\Admin\PrestaShopAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends PrestaShopAdminController
{
    protected $config;
    protected $logger;
    protected $module;

    protected $layoutTitle = '';
    protected $layoutSubTitle = '';
    protected $helpLink = '';

    public function __construct(
        \GC_Facetedsearch $module
    ) {
        $this->module = $module;
        $this->config = $module->getConfig();
        $this->logger = $module->getLogger()->getInstanceOrCreate('admin');
    }

    protected function processForm(
        Request $request,
        FormHandlerInterface $formHandler,
        string $redirectRoute = 'gc_facetedsearch_configuration',
        string $template = '',
        array $templateParameters = [],
        array $callbacks = []
    ): Response {
        if (!$this->module->isLicensed() && !$this->module->isDevMode()) {
            $this->logger->warning('Module is not licensed, redirect to license page');

            return $this->redirectToRoute('gc_facetedsearch_license');
        }

        $form = $formHandler->getForm();
        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
        ) {
            if (!empty($callbacks['submitted'])) {
                call_user_func($callbacks['submitted']);
            }

            $this->logger->info('Form submitted: ' . $form->getName());

            $saveErrors = $formHandler->save($form->getData());

            if (0 === count($saveErrors)) {
                $this->addFlash('success', $this->trans('Settings saved!', [], 'Modules.Gcfacetedsearch.Admin'));

                if (!empty($callbacks['success'])) {
                    call_user_func($callbacks['success']);
                }

                $this->logger->info('Form saved successfully');

                return $this->redirectToRoute($redirectRoute);
            } else {
                $this->addFlashErrors($saveErrors);

                if (!empty($callbacks['error'])) {
                    call_user_func($callbacks['error']);
                }

                $this->logger->error('Form save errors: ' . implode('; ', $saveErrors));
            }
        }

        if (empty($template)) {
            return $this->redirectToRoute($redirectRoute);
        }

        $templateParameters = array_merge(
            $templateParameters,
            [
                'form' => $form->createView(),
            ]
        );

        if (!empty($callbacks['render'])) {
            call_user_func($callbacks['render']);
        }

        return $this->render($template, $templateParameters);
    }

    protected function getToolbarButtons(): array
    {
        $languageContext = $this->getLanguageContext();

        return [
            'hooks' => [
                'href' => $this->generateUrl('admin_modules_positions', ['show_modules' => (int) $this->module->id]),
                'desc' => $this->trans('Manage hooks', [], 'Modules.Gcfacetedsearch.Admin'),
                'icon' => 'anchor',
            ],
            'translation' => [
                'href' => $this->generateUrl(
                    'admin_international_translation_overview',
                    [
                        'lang' => $languageContext->getIsocode(),
                        'type' => 'modules',
                        'locale' => $languageContext->getLocale(),
                        'selected' => $this->module->name,
                    ]
                ),
                'desc' => $this->trans('Translate', [], 'Modules.Gcfacetedsearch.Admin'),
                'icon' => 'translate',
            ],
        ];
    }

    protected function getHeaderVars(): array
    {
        $licenseKey = $this->module->getLicenseKey();

        if ($this->module->isDevMode()) {
            $licenseKey = $this->trans('DEMO MODE', [], 'Modules.Gcfacetedsearch.Admin');
        }

        $moduleManagerBuilder = ModuleManagerBuilder::getInstance();
        $moduleManager = $moduleManagerBuilder->build();

        return [
            'module' => [
                'name' => $this->module->name,
                'display_name' => $this->module->displayName,
                'display_name_pre' => $this->module->displayNamePre,
                'display_name_post' => $this->module->displayNamePost,
                'description' => $this->module->description,
                'description_full' => $this->module->description_full,
                'version' => $this->module->version,
                'author' => $this->module->author,
                'gc_module_version' => $this->module->getGCModuleVersion(),
                'logo' => $this->module->getPathUri() . 'logo.png',
                'license_key' => $licenseKey,
                'path' => $this->module->getPathUri(),
                'hideConfigTab' => $this->module->redirectAdminConfigurationPermanentTo != '',
            ],
            'layoutHeaderToolbarBtn' => $this->getToolbarButtons(),
            'help_link' => $this->getHelpLink(),
            'layoutTitle' => $this->getLayoutTitle(),
            'layoutSubTitle' => $this->getLayoutSubTitle(),
            'menuIcons' => $this->getMenuIcons(),
            'isEnabled' => $moduleManager->isEnabled($this->module->name),
        ];
    }

    protected function render(
        string $view,
        array $parameters = [],
        ?Response $response = null
    ): Response {
        $parameters = array_merge(
            $this->getHeaderVars(),
            $parameters
        );

        if (!strpos($view, '@Modules')) {
            $view = '@Modules/' . $this->module->name . '/' . $view;
        }

        return parent::render($view, $parameters, $response);
    }

    protected function renderView(
        string $view,
        array $parameters = []
    ): string {
        if (!strpos($view, '@Modules')) {
            $view = '@Modules/' . $this->module->name . '/' . $view;
        }

        return parent::renderView($view, $parameters);
    }

    protected function isAjax(Request $request): bool
    {
        if (
            $request->isXmlHttpRequest()
            || $request->query->has('ajax')
            || $request->request->has('ajax')
        ) {
            return true;
        }

        return false;
    }

    protected function getLayoutTitle(): string
    {
        if (!empty($this->layoutTitle)) {
            return $this->layoutTitle;
        }

        return $this->module->displayName;
    }

    protected function setLayoutTitle(string $layoutTitle)
    {
        $this->layoutTitle = $layoutTitle;
    }

    protected function getLayoutSubTitle(): string
    {
        if (!empty($this->layoutSubTitle)) {
            return $this->layoutSubTitle;
        }

        return '';
    }

    protected function setLayoutSubTitle(string $layoutSubTitle)
    {
        $this->layoutSubTitle = $layoutSubTitle;
    }

    protected function getHelpLink(): string
    {
        if (!empty($this->helpLink)) {
            return $this->helpLink;
        }

        return $this->module->getSettings()->getHelp();
    }

    protected function setHelpLink(string $helpLink)
    {
        $this->helpLink = $helpLink;
    }

    protected function getMenuIcons(): array
    {
        $menuIcons = [];
        $tabs = \Tab::getCollectionFromModule($this->module->name);

        foreach ($tabs as $tab) {
            /**
             * @var \Tab $tab
             */
            if (empty($tab->icon)) {
                continue;
            }

            $menuIcons[$tab->class_name] = $tab->icon;
        }

        return $menuIcons;
    }

    protected function clearSmartyCache(string $template, string $cacheId = '')
    {
        $this->module->clearSmartyCache($template, $cacheId);
    }
}
