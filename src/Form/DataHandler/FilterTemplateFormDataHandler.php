<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

namespace Onlineshopmodule\PrestaShop\Module\Facetedsearch\Form\DataHandler;

use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Model\FacetedSearchFilter;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\DataHandler\FormDataHandlerInterface;
use PrestaShopException;
use Shop;

/**
 * Persists filter template form data (create / update).
 */
class FilterTemplateFormDataHandler implements FormDataHandlerInterface
{
    /**
     * @var \GC_Facetedsearch
     */
    private $module;

    public function __construct(
        \GC_Facetedsearch $module
    ) {
        $this->module = $module;
    }

    /**
     * {@inheritdoc}
     *
     * @throws PrestaShopException
     */
    public function create(array $data)
    {
        $filterTemplate = new FacetedSearchFilter();
        $this->hydrateFilter($filterTemplate, $data);

        if (!$filterTemplate->add()) {
            throw new PrestaShopException('Unable to create filter template.');
        }

        $this->module->buildLayeredCategories();

        return (int) $filterTemplate->id;
    }

    /**
     * {@inheritdoc}
     *
     * @throws PrestaShopException
     */
    public function update($id, array $data)
    {
        $filter = new FacetedSearchFilter((int) $id);
        if (!\Validate::isLoadedObject($filter)) {
            throw new PrestaShopException(sprintf('Filter template with id "%d" was not found.', (int) $id));
        }

        $this->hydrateFilter($filter, $data);

        if (!$filter->update()) {
            throw new PrestaShopException(sprintf('Unable to update filter template with id "%d".', (int) $id));
        }

        return (int) $filter->id;
    }

    private function hydrateFilter(FacetedSearchFilter $filter, array $data): void
    {
        $categories = $this->normalizeIdList($data['categories'] ?? []);
        $shopIds = $this->resolveShopIds($data);
        $filterValues = $this->buildFilterValues($data, $categories, $shopIds);

        $filter->name = (string) ($data['name'] ?? '');
        $filter->filters = serialize($filterValues);
        $filter->n_categories = count($categories);
        $filter->id_shop_list = $shopIds;
    }

    private function buildFilterValues(array $data, array $categories, array $shopIds): array
    {
        $filterValues = [
            'categories' => $categories,
            'shop_list' => $shopIds,
            'controllers' => array_values($data['controllers'] ?? []),
        ];

        foreach ($data['filters'] ?? [] as $filterKey => $filterConfig) {
            if (!is_array($filterConfig) || empty($filterConfig['enabled'])) {
                continue;
            }

            $filterValues[$filterKey] = [
                'filter_type' => (int) ($filterConfig['filter_type'] ?? 0),
                'filter_show_limit' => (int) ($filterConfig['filter_show_limit'] ?? 0),
            ];
        }

        return $filterValues;
    }

    private function resolveShopIds(array $data): array
    {
        if (!empty($data['shop_association']) && is_array($data['shop_association'])) {
            return $this->normalizeIdList($data['shop_association']);
        }

        return array_map('intval', Shop::getContextListShopID());
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
