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

use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use PrestaShopBundle\Translation\TranslatorInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Filters panel containing reusable filter item rows.
 */
class FiltersType extends TranslatorAwareType
{
    /**
     * @var int
     */
    private $languageId;

    public function __construct(
        TranslatorInterface $translator,
        array $locales,
        int $languageId
    ) {
        parent::__construct($translator, $locales);

        $this->languageId = $languageId;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('subcategories', FilterItemType::class, [
                'label' => $this->trans('Sub-categories filter', 'Modules.Gcfacetedsearch.Admin'),
                'data' => [
                    'enabled' => false,
                    'filter_show_limit' => 0,
                    'filter_type' => 0,
                ],
            ])
            ->add('stock', FilterItemType::class, [
                'label' => $this->trans('Product stock filter', 'Modules.Gcfacetedsearch.Admin'),
                'data' => [
                    'enabled' => false,
                    'filter_show_limit' => 0,
                    'filter_type' => 0,
                ],
            ])
            ->add('condition', FilterItemType::class, [
                'label' => $this->trans('Product condition filter', 'Modules.Gcfacetedsearch.Admin'),
                'data' => [
                    'enabled' => false,
                    'filter_show_limit' => 0,
                    'filter_type' => 0,
                ],
            ])
            ->add('manufacturer', FilterItemType::class, [
                'label' => $this->trans('Product brand filter', 'Modules.Gcfacetedsearch.Admin'),
                'data' => [
                    'enabled' => false,
                    'filter_show_limit' => 0,
                    'filter_type' => 0,
                ],
            ])
            ->add('weight_slider', FilterItemType::class, [
                'label' => $this->trans('Product weight filter (slider)', 'Modules.Gcfacetedsearch.Admin'),
                'data' => [
                    'enabled' => false,
                    'filter_show_limit' => 0,
                    'filter_type' => 0,
                ],
                'slider' => true,
            ])
            ->add('price_slider', FilterItemType::class, [
                'label' => $this->trans('Product price filter (slider)', 'Modules.Gcfacetedsearch.Admin'),
                'data' => [
                    'enabled' => false,
                    'filter_show_limit' => 0,
                    'filter_type' => 0,
                ],
                'slider' => true,
            ])
        ;

        $groups = $this->resolveProductAttributeGroups();
        foreach ($groups as $group) {
            if ($group['n'] > 1) {
                $label = $this->trans(
                    'Attribute group: %name% (%count% attributes)',
                    'Modules.Gcfacetedsearch.Admin',
                    [
                        '%name%' => $group['name'],
                        '%count%' => $group['n'],
                    ]
                );
            } else {
                $label = $this->trans(
                    'Attribute group: %name% (%count% attribute)',
                    'Modules.Gcfacetedsearch.Admin',
                    [
                        '%name%' => $group['name'],
                        '%count%' => $group['n'],
                    ]
                );
            }
            $builder->add('attribute_group_' . $group['id_attribute_group'], FilterItemType::class, [
                'label' => $label,
                'slider' => false,
                'data' => [
                    'enabled' => false,
                    'filter_show_limit' => 0,
                    'filter_type' => 0,
                ],
            ]);
        }

        $features = $this->resolveProductFeatures();
        foreach ($features as $feature) {
            if ($feature['n'] > 1) {
                $label = $this->trans(
                    'Feature: %name% (%count% values)',
                    'Modules.Gcfacetedsearch.Admin',
                    [
                        '%name%' => $feature['name'],
                        '%count%' => $feature['n'],
                    ]
                );
            } else {
                $label = $this->trans(
                    'Feature: %name% (%count% value)',
                    'Modules.Gcfacetedsearch.Admin',
                    [
                        '%name%' => $feature['name'],
                        '%count%' => $feature['n'],
                    ]
                );
            }
            $builder->add('feature_' . $feature['id_feature'], FilterItemType::class, [
                'label' => $label,
                'slider' => false,
                'data' => [
                    'enabled' => false,
                    'filter_show_limit' => 0,
                    'filter_type' => 0,
                ],
            ]);
        }

        // echo "<pre>"; print_r($groups); echo '</pre>';
        // exit;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'label' => false,
            'required' => false,
            'error_bubbling' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'gc_filters';
    }

    private function resolveProductAttributeGroups()
    {
        return \Db::getInstance()->executeS(
            'SELECT ag.id_attribute_group, agl.name, COUNT(DISTINCT(a.id_attribute)) n
            FROM ' . _DB_PREFIX_ . 'attribute_group ag
            LEFT JOIN ' . _DB_PREFIX_ . 'attribute_group_lang agl ON (agl.id_attribute_group = ag.id_attribute_group)
            LEFT JOIN ' . _DB_PREFIX_ . 'attribute a ON (a.id_attribute_group = ag.id_attribute_group)
            WHERE agl.id_lang = ' . (int) $this->languageId . '
            GROUP BY ag.id_attribute_group'
        );
    }

    private function resolveProductFeatures()
    {
        return \Db::getInstance()->executeS(
            'SELECT fl.id_feature, fl.name, COUNT(DISTINCT(fv.id_feature_value)) n
            FROM ' . _DB_PREFIX_ . 'feature_lang fl
            LEFT JOIN ' . _DB_PREFIX_ . 'feature_value fv ON (fv.id_feature = fl.id_feature)
            WHERE (fv.custom IS NULL OR fv.custom = 0) AND fl.id_lang = ' . (int) $this->languageId . '
            GROUP BY fl.id_feature'
        );
    }
}
