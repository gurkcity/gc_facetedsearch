<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

namespace Onlineshopmodule\PrestaShop\Module\Facetedsearch\Form\Type;

use PrestaShopBundle\Form\Admin\Type\MultistoreConfigurationType;
use PrestaShopBundle\Form\Admin\Type\SwitchType;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use PrestaShopBundle\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ConfigurationType extends TranslatorAwareType
{
    protected $context;
    protected $config;
    protected $module;

    public function __construct(
        TranslatorInterface $translator,
        array $locales,
        \Context $context,
        \GC_Facetedsearch $module
    ) {
        parent::__construct($translator, $locales);

        $this->context = $context;
        $this->module = $module;
        $this->config = $this->module->config;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->module->hasJavascriptFiles()) {
            $builder
                ->add('js_defer', SwitchType::class, [
                    'label' => $this->trans('Load JS deferred', 'Modules.Gcfacetedsearch.Admin'),
                    'help' => $this->trans('Enable this option to load JavaScript files with the "defer" attribute, improving page load performance.', 'Modules.Gcfacetedsearch.Admin'),
                    'multistore_configuration_key' => $this->config->getName('JS_DEFER'),
                    'required' => false,
                ])
            ;
        }

        if (
            $this->module->hasHummingbirdTemplates()
            || $this->module->hasHummingbirdJavascriptOrStylesheets()
        ) {
            $shop = $this->context->shop ?? null;
            $theme = $shop ? $shop->theme_name : null;

            $builder
                ->add('theme', ChoiceType::class, [
                    'label' => $this->trans('Theme', 'Modules.Gcfacetedsearch.Admin'),
                    'help' => $this->trans('This module uses front office templates optimized for the original PrestaShop themes "Classic" and "Hummingbird". Select which original PrestaShop theme your shop theme is based on. The theme of this shop "%shop%" is currently "%theme%".', 'Modules.Gcfacetedsearch.Admin', ['%shop%' => $shop ? $shop->name : '', '%theme%' => $theme]),
                    'choices' => [
                        $this->trans('Theme based on "classic"', 'Modules.Gcfacetedsearch.Admin') => 'classic',
                        $this->trans('Theme based on "hummingbird"', 'Modules.Gcfacetedsearch.Admin') => 'hummingbird',
                    ],
                    'multistore_configuration_key' => $this->config->getName('THEME'),
                    'required' => true,
                ])
            ;
        }
    }

    public function getParent(): string
    {
        return MultistoreConfigurationType::class;
    }
}
