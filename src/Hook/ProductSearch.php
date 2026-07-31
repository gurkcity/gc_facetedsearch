<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

namespace Onlineshopmodule\PrestaShop\Module\Facetedsearch\Hook;

use Configuration;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Filters\Converter;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Filters\DataAccessor;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Filters\Provider;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Product\SearchFactory;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Product\SearchProvider;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\URLSerializer;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;

class ProductSearch extends AbstractHook
{
    const AVAILABLE_HOOKS = [
        'productSearchProvider',
    ];

    /**
     * This method returns the search provider to the controller who requested it.
     *
     * @param array $params
     *
     * @return SearchProvider|null
     */
    public function productSearchProvider(array $params)
    {
        /*
         * Backward compatibility, required for versions < 8.0
         * We need to assign missing queryType to some controllers, which don't report it.
         * Remove when module minimum compatibility reaches 8.0.
         */
        if (empty($params['query']->getQueryType())) {
            $params['query'] = $this->assignMissingQueryType($params['query']);
        }

        /*
         * Check if the type of query (controller) is supported by our module. If not, we
         * let the core do the search.
         */
        if ($this->module->isControllerSupported($params['query']->getQueryType()) === false) {
            return null;
        }

        // Initialize provider, we will need it right away to check if there are filters setup
        $provider = new Provider($this->module->getDatabase());

        /*
         * If search controller is not specifically enabled, we don't return the instance.
         * This condition will be removed when search controller support is fully implemented.
         */
        if ($params['query']->getQueryType() === 'search'
            && empty($provider->getFiltersForQuery($params['query'], (int) $this->context->shop->id))) {
            return null;
        }

        /*
         * Fix wrong reporting of desired best sales order. BestSalesProductSearchProvider overrides
         * the sort set on the query in BestSalesControllerCore.
         */
        if ($params['query']->getQueryType() == 'best-sales') {
            $params['query']->setSortOrder(new SortOrder('product', 'sales', 'desc'));
        }

        // Assign assets
        /** @var \FrontController $controller */
        $controller = $this->context->controller;
        if ((bool) Configuration::get('GC_FACETEDSEARCH_USE_JQUERY_UI_SLIDER')) {
            $controller->addJqueryUi('ui.slider');
        }

        $urlSerializer = new URLSerializer();
        $dataAccessor = new DataAccessor($this->module->getDatabase());

        // Return an instance of our searcher, ready to accept requests
        return new SearchProvider(
            $this->module,
            new Converter(
                $this->module->getContext(),
                $this->module->getDatabase(),
                $urlSerializer,
                $dataAccessor,
                $provider
            ),
            $urlSerializer,
            $dataAccessor,
            new SearchFactory(),
            $provider
        );
    }

    /**
     * Assign missing queryType, required for PS versions < 8.0
     *
     * @param ProductSearchQuery $query
     *
     * @return ProductSearchQuery
     */
    private function assignMissingQueryType(ProductSearchQuery $query)
    {
        if (!empty($query->getIdCategory())) {
            $query->setQueryType('category');
        } elseif (!empty($query->getIdManufacturer())) {
            $query->setQueryType('manufacturer');
        } elseif (!empty($query->getIdSupplier())) {
            $query->setQueryType('supplier');
        } elseif (!empty($query->getSearchString()) || !empty($query->getSearchTag())) {
            $query->setQueryType('search');
        }

        return $query;
    }
}
