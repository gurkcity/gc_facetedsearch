<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

namespace Onlineshopmodule\PrestaShop\Module\Facetedsearch\Form\DataProvider;

use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Model\FacetedSearchFilter;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\DataProvider\FormDataProviderInterface;
use PrestaShopException;
use Shop;

/**
 * Provides default and existing data for the filter template form.
 */
class FilterTemplateFormDataProvider implements FormDataProviderInterface
{
    private const RESERVED_FILTER_KEYS = [
        'categories',
        'shop_list',
        'controllers',
        'filters_order',
    ];

    /**
     * {@inheritdoc}
     *
     * @throws PrestaShopException
     */
    public function getData($id)
    {
        $filterTemplate = new FacetedSearchFilter((int) $id);
        if (!\Validate::isLoadedObject($filterTemplate)) {
            throw new PrestaShopException(sprintf('Filter template with id "%d" was not found.', (int) $id));
        }

        $filterValues = $this->unserializeFilters($filterTemplate->filters);

        return [
            'name' => (string) $filterTemplate->name,
            'categories' => $this->normalizeIdList($filterValues['categories'] ?? []),
            'shop_association' => $this->resolveShopAssociation($filterTemplate, $filterValues),
            'controllers' => array_values($filterValues['controllers'] ?? []),
            'filters' => $this->mapFiltersForForm($filterValues),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultData()
    {
        return [
            'name' => '',
            'categories' => [],
            'shop_association' => array_map('intval', Shop::getContextListShopID()),
            'controllers' => [],
            'filters' => [],
        ];
    }

    private function unserializeFilters($filters): array
    {
        if (!is_string($filters) || $filters === '') {
            return [];
        }

        $decoded = @unserialize($filters);

        return is_array($decoded) ? $decoded : [];
    }

    private function mapFiltersForForm(array $filterValues): array
    {
        $filters = [];
        $order = $this->resolveFiltersOrder($filterValues);

        foreach ($order as $position => $filterKey) {
            $filterConfig = $filterValues[$filterKey] ?? null;
            $enabled = is_array($filterConfig);

            $filters[$filterKey] = [
                'enabled' => $enabled,
                'position' => (int) $position,
                'filter_type' => (int) ($filterConfig['filter_type'] ?? 0),
                'filter_show_limit' => (int) ($filterConfig['filter_show_limit'] ?? 0),
            ];
        }

        return $filters;
    }

    /**
     * Full UI order including disabled filters. Falls back to enabled keys order for legacy data.
     */
    private function resolveFiltersOrder(array $filterValues): array
    {
        $order = [];

        if (!empty($filterValues['filters_order']) && is_array($filterValues['filters_order'])) {
            foreach ($filterValues['filters_order'] as $filterKey) {
                if (is_string($filterKey) && $filterKey !== '') {
                    $order[] = $filterKey;
                }
            }
        } else {
            foreach ($filterValues as $filterKey => $filterConfig) {
                if (in_array($filterKey, self::RESERVED_FILTER_KEYS, true) || !is_array($filterConfig)) {
                    continue;
                }
                $order[] = $filterKey;
            }
        }

        foreach ($filterValues as $filterKey => $filterConfig) {
            if (in_array($filterKey, self::RESERVED_FILTER_KEYS, true) || !is_array($filterConfig)) {
                continue;
            }
            if (!in_array($filterKey, $order, true)) {
                $order[] = $filterKey;
            }
        }

        return $order;
    }

    private function resolveShopAssociation(FacetedSearchFilter $filterTemplate, array $filterValues): array
    {
        $associatedShops = $filterTemplate->getAssociatedShops();
        if (!empty($associatedShops)) {
            return $this->normalizeIdList($associatedShops);
        }

        return $this->normalizeIdList($filterValues['shop_list'] ?? []);
    }

    private function normalizeIdList($values): array
    {
        if (!is_array($values)) {
            return [];
        }

        $ids = array_map('intval', $values);

        return array_values(array_filter($ids));
    }
}
