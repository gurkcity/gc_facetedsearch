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
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

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
        $builder
            ->add('CACHE_ENABLED', SwitchType::class, [
                'label' => $this->trans('Enable cache system', 'Modules.Gcfacetedsearch.Admin'),
                'required' => false,
                'help' => $this->trans('This option caches filtering blocks, so the module does not have to query for matching products all the time. The cache is invalidated on every modification on your store. If you encounter some incosistencies, disable this cache or make sure to flush it if needed.', 'Modules.Gcfacetedsearch.Admin'),
            ])
            ->add('SHOW_QUIES', SwitchType::class, [
                'label' => $this->trans('Show the number of matching products', 'Modules.Gcfacetedsearch.Admin'),
                'required' => false,
                'help' => $this->trans('Enable or disable display of matching products after filters. Disabling this won\'t bring any performance benefit, because matching products need to be calculated anyway.', 'Modules.Gcfacetedsearch.Admin'),
            ])
            ->add('FULL_TREE', SwitchType::class, [
                'label' => $this->trans('Show products from subcategories', 'Modules.Gcfacetedsearch.Admin'),
                'required' => false,
                'help' => $this->trans('Enable this, if you want to display products from subcategories, even if they are not specifically assigned to the currently browsed category.', 'Modules.Gcfacetedsearch.Admin'),
            ])
            ->add('FILTER_BY_DEFAULT_CATEGORY', SwitchType::class, [
                'label' => $this->trans('Show products only from default category', 'Modules.Gcfacetedsearch.Admin'),
                'required' => false,
                'help' => $this->trans('Works only if "Show products from subcategories" is off.', 'Modules.Gcfacetedsearch.Admin'),
            ])
            ->add('FILTER_CATEGORY_DEPTH', NumberType::class, [
                'label' => $this->trans('Category filter depth', 'Modules.Gcfacetedsearch.Admin'),
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => $this->trans('The %s field is required.', 'Admin.Notifications.Error'),
                    ]),
                ],
                'help' => $this->trans('This option controls the behavior of category filter block - how deep children of the currently browsed category you want to display? The default value is 1 - only the direct children. Use 0 for unlimited depth.', 'Modules.Gcfacetedsearch.Admin'),
            ])
            ->add('FILTER_PRICE_USETAX', SwitchType::class, [
                'label' => $this->trans('Use tax to filter price', 'Modules.Gcfacetedsearch.Admin'),
                'required' => false,
            ])
            ->add('FILTER_PRICE_ROUNDING', SwitchType::class, [
                'label' => $this->trans('Use rounding to filter price', 'Modules.Gcfacetedsearch.Admin'),
                'required' => false,
            ])
            ->add('FILTER_SHOW_OUT_OF_STOCK_LAST', SwitchType::class, [
                'label' => $this->trans('Show unavailable, out of stock last', 'Modules.Gcfacetedsearch.Admin'),
                'required' => false,
            ])
            ->add('USE_JQUERY_UI_SLIDER', SwitchType::class, [
                'label' => $this->trans('Use Jquery UI slider', 'Modules.Gcfacetedsearch.Admin'),
                'required' => false,
                'help' => $this->trans('Switch this off only if your theme does not use jQuery UI slider. It is recommended to keep it on when using classic theme.', 'Modules.Gcfacetedsearch.Admin'),
            ])
            ->add('DEFAULT_CATEGORY_TEMPLATE', ChoiceType::class, [
                'required' => false,
                'label' => $this->trans('Default filter template for new categories', 'Modules.Gcfacetedsearch.Admin'),
                'help' => $this->trans('If you want to automatically assign a filter template to new categories, select it here..', 'Modules.Gcfacetedsearch.Admin'),
                'placeholder' => false,
                'choices' => $this->resolveFilterTemplateChoices(),
                'choice_translation_domain' => false,
            ])
            ->add('OMIT_COUNTRIES', SwitchType::class, [
                'label' => $this->trans('Omit country indexation', 'Modules.Gcfacetedsearch.Admin'),
                'required' => false,
                'help' => $this->trans('If enabled, the country indexation will be omitted. This results in a smaller index and less storage space consumption in the database.', 'Modules.Gcfacetedsearch.Admin'),
            ])

            ->add('POSITION_SORTING', SwitchType::class, [
                'label' => $this->trans('Sort by Relevance', 'Modules.Gcfacetedsearch.Admin'),
                'required' => false,
                'default_empty_data' => 0,
            ])
            ->add('BEST_SALES_SORTING', SwitchType::class, [
                'label' => $this->trans('Sort by Best Seller', 'Modules.Gcfacetedsearch.Admin'),
                'required' => false,
                'default_empty_data' => 0,
            ])
            ->add('BEST_SALES_DAYS', NumberType::class, [
                'label' => $this->trans('Best sales days', 'Modules.Gcfacetedsearch.Admin'),
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => $this->trans('The %s field is required.', 'Admin.Notifications.Error'),
                    ]),
                ],
                'default_empty_data' => 60,
                'help' => $this->trans('If set to zero, the complete PrestaShop history should be used (PrestaShops default for the sales cache)', 'Modules.Gcfacetedsearch.Admin'),
            ])
            ->add('DATE_ADD_SORTING', SwitchType::class, [
                'label' => $this->trans('Sort by Newest Arrivals', 'Modules.Gcfacetedsearch.Admin'),
                'required' => false,
                'default_empty_data' => 0,
            ])
            ->add('PRICE_LOW_TO_HIGH_SORTING', SwitchType::class, [
                'label' => $this->trans('Sort by Price, low to high', 'Modules.Gcfacetedsearch.Admin'),
                'required' => false,
                'default_empty_data' => 0,
            ])
            ->add('PRICE_HIGH_TO_LOW_SORTING', SwitchType::class, [
                'label' => $this->trans('Sort by Price, high to low', 'Modules.Gcfacetedsearch.Admin'),
                'required' => false,
                'default_empty_data' => 0,
            ])
            ->add('QUANTITY_SORTING', SwitchType::class, [
                'label' => $this->trans('Sort by Most QTY Available in Stock', 'Modules.Gcfacetedsearch.Admin'),
                'required' => false,
                'default_empty_data' => 0,
            ])
            ->add('PRODUCT_REFERENCE_ASC_SORTING', SwitchType::class, [
                'label' => $this->trans('Sort by Refernce, A to Z', 'Modules.Gcfacetedsearch.Admin'),
                'required' => false,
                'default_empty_data' => 0,
            ])
            ->add('PRODUCT_REFERENCE_DESC_SORTING', SwitchType::class, [
                'label' => $this->trans('Sort by Refernce, Z to A', 'Modules.Gcfacetedsearch.Admin'),
                'required' => false,
                'default_empty_data' => 0,
            ])
        ;

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

    /**
     * @return array<string, int>
     */
    private function resolveFilterTemplateChoices(): array
    {
        $templates = \Db::getInstance()->executeS(
            'SELECT `id_gc_facetedsearch_filter`, `name`
            FROM `' . _DB_PREFIX_ . 'gc_facetedsearch_filter`
            ORDER BY `name` ASC'
        );

        if (!$templates) {
            return [
                $this->trans('None', 'Admin.Global') => 0,
            ];
        }

        $choices = [
            $this->trans('None', 'Admin.Global') => 0,
        ];
        foreach ($templates as $template) {
            $choices[$template['name']] = (int) $template['id_gc_facetedsearch_filter'];
        }

        return $choices;
    }
}
