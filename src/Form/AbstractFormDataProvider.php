<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

namespace Onlineshopmodule\PrestaShop\Module\Facetedsearch\Form;

use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Module\ConfigurationAdapter;
use PrestaShop\PrestaShop\Adapter\Feature\MultistoreFeature;
use PrestaShop\PrestaShop\Adapter\Shop\Context;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint;
use PrestaShopBundle\Service\Form\MultistoreCheckboxEnabler;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractFormDataProvider
{
    public function __construct(
        protected ConfigurationAdapter $configurationAdapter,
        protected Context $shopContext,
        protected MultistoreFeature $multistoreFeature,
        protected TranslatorInterface $translator
    ) {
    }

    protected function updateConfiguration(
        string $key,
        string $fieldName,
        $data,
        ?ShopConstraint $shopConstraint = null,
        array $options = []
    ): bool {
        $multistoreFieldPrefix = MultistoreCheckboxEnabler::MULTISTORE_FIELD_PREFIX;

        $configPrefix = isset($options['prefix']) ? $options['prefix'] : true;

        $isLang = $options['lang'] ?? false;
        $cast = $options['cast'] ?? 'string';

        if (is_array($data) && !array_key_exists($fieldName, $data)) {
            return false;
        }

        $value = $data[$fieldName];

        if (
            $this->multistoreFeature->isUsed()
            && !$this->shopContext->isAllShopContext()
            && empty($data[$multistoreFieldPrefix . $fieldName])
        ) {
            return $this->configurationAdapter->deleteFromContext($key, $shopConstraint, $configPrefix);
        } else {
            // If data is still an array, it means it's a multilingual field, so
            // we need to cast each value. If it's not an array, we just cast it
            // as is.
            if (is_array($value)) {
                $isLang = true;

                foreach ($value as $langId => $langValue) {
                    settype($langValue, $cast);
                    $value[$langId] = $langValue;
                }
            } else {
                settype($value, $cast);
            }

            if ($isLang) {
                return $this->configurationAdapter->setLang($key, $value, $options['html'] ?? false, $shopConstraint, $configPrefix);
            } else {
                return $this->configurationAdapter->set($key, $value, $options['html'] ?? false, $shopConstraint, $configPrefix);
            }
        }
    }
}
