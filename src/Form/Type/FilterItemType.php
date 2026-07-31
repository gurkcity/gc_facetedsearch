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

use PrestaShopBundle\Form\Admin\Type\SwitchType;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Single filter row: enable switch, result limit and display style.
 */
class FilterItemType extends TranslatorAwareType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('enabled', SwitchType::class, [
                'label' => false,
                'required' => false,
                'show_choices' => false,
                'attr' => [
                    'class' => 'filter-switch',
                ],
            ])
            ->add('position', HiddenType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'filter-position',
                ],
            ])
            ->add('filter_show_limit', ChoiceType::class, [
                'label' => $this->trans('Result limit:', 'Modules.Gcfacetedsearch.Admin'),
                'required' => false,
                'choices' => $this->resolveLimitChoices(),
                'choice_translation_domain' => false,
                'attr' => [
                    'class' => 'custom-select',
                ],
            ])
            ->add('filter_type', ChoiceType::class, [
                'label' => $this->trans('Style:', 'Modules.Gcfacetedsearch.Admin'),
                'required' => false,
                'choices' => [
                    $this->trans('Checkbox', 'Modules.Gcfacetedsearch.Admin') => 0,
                    $this->trans('Radio button', 'Modules.Gcfacetedsearch.Admin') => 1,
                    $this->trans('Drop-down list', 'Modules.Gcfacetedsearch.Admin') => 2,
                ],
                'choice_translation_domain' => false,
                'attr' => [
                    'class' => 'custom-select',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'label' => false,
            'required' => false,
            'error_bubbling' => false,
            'slider' => false,
            'default_empty_data' => [
                'enabled' => false,
                'filter_show_limit' => 0,
                'filter_type' => 0,
            ],
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['slider'] = $options['slider'] ?? false;
    }

    public function getBlockPrefix(): string
    {
        return 'gc_filter_item';
    }

    private function resolveLimitChoices(): array
    {
        $choices = [
            $this->trans('No limit', 'Modules.Gcfacetedsearch.Admin') => 0,
        ];

        for ($index = 2; $index <= 20; ++$index) {
            $choices[$index] = $index;
        }

        return $choices;
    }
}
