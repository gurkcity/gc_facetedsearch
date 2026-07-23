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

use PrestaShop\PrestaShop\Adapter\Feature\MultistoreFeature;
use PrestaShopBundle\Form\Admin\Type\CategoryChoiceTreeType;
use PrestaShopBundle\Form\Admin\Type\ShopChoiceTreeType;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use PrestaShopBundle\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Symfony form for creating and editing filter templates.
 */
class FilterTemplateType extends TranslatorAwareType
{
    /**
     * @var MultistoreFeature
     */
    private $multistoreFeature;

    public function __construct(
        TranslatorInterface $translator,
        array $locales,
        MultistoreFeature $multistoreFeature
    ) {
        parent::__construct($translator, $locales);

        $this->multistoreFeature = $multistoreFeature;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => $this->trans('Template name', 'Modules.Gcfacetedsearch.Admin'),
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => $this->trans('The %s field is required.', 'Admin.Notifications.Error'),
                    ]),
                ],
                'help' => $this->trans('Only as a reminder', 'Modules.Gcfacetedsearch.Admin'),
            ])
            ->add('categories', CategoryChoiceTreeType::class, [
                'label' => $this->trans('Categories', 'Admin.Catalog.Feature'),
                'required' => true,
                'multiple' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => $this->trans('You must select at least one category.', 'Modules.Gcfacetedsearch.Admin'),
                    ]),
                ],
            ])
        ;

        if ($this->multistoreFeature->isUsed()) {
            $builder->add('shop_association', ShopChoiceTreeType::class, [
                'label' => $this->trans('Choose shop association', 'Modules.Gcfacetedsearch.Admin'),
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => $this->trans('You must select at least one shop.', 'Admin.Notifications.Error'),
                    ]),
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'form_theme' => '@PrestaShop/Admin/TwigTemplateForm/prestashop_ui_kit.html.twig',
        ]);
    }
}
