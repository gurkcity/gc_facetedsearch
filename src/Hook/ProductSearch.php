<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace Onlineshopmodule\PrestaShop\Module\FacetedSearch\Hook;

use Configuration;
use Onlineshopmodule\PrestaShop\Module\FacetedSearch\Filters\Converter;
use Onlineshopmodule\PrestaShop\Module\FacetedSearch\Filters\DataAccessor;
use Onlineshopmodule\PrestaShop\Module\FacetedSearch\Filters\Provider;
use Onlineshopmodule\PrestaShop\Module\FacetedSearch\Product\SearchFactory;
use Onlineshopmodule\PrestaShop\Module\FacetedSearch\Product\SearchProvider;
use Onlineshopmodule\PrestaShop\Module\FacetedSearch\URLSerializer;
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
        if ((bool) Configuration::get('GC_USE_JQUERY_UI_SLIDER')) {
            $controller->addJqueryUi('ui.slider');
        }
        $controller->registerStylesheet(
            'facetedsearch_front',
            '/modules/gc_facetedsearch/views/dist/front.css'
        );
        $controller->registerJavascript(
            'facetedsearch_front',
            '/modules/gc_facetedsearch/views/dist/front.js',
            ['position' => 'bottom', 'priority' => 100]
        );

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
