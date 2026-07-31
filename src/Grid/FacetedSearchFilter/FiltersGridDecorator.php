<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

namespace Onlineshopmodule\PrestaShop\Module\Facetedsearch\Grid\FacetedSearchFilter;

use GC_Facetedsearch;
use PrestaShop\PrestaShop\Core\Grid\Data\Factory\GridDataFactoryInterface;
use PrestaShop\PrestaShop\Core\Grid\Data\GridData;
use PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;
use Tools;

class FiltersGridDecorator implements GridDataFactoryInterface
{
    /**
     * @var GridDataFactoryInterface
     */
    private $dataFactory;

    /**
     * @var GC_Facetedsearch
     */
    private $module;

    /**
     * @param GridDataFactoryInterface $dataFactory
     */
    public function __construct(
        GridDataFactoryInterface $dataFactory,
        GC_Facetedsearch $module
    ) {
        $this->dataFactory = $dataFactory;
        $this->module = $module;
    }

    public function getData(SearchCriteriaInterface $searchCriteria)
    {
        $data = $this->dataFactory->getData($searchCriteria);
        $records = $data->getRecords()->all();

        $supportedControllers = $this->module->getSupportedControllers();

        foreach ($records as &$record) {
            $filters = Tools::unSerialize($record['filters']);

            if ($filters && isset($filters['controllers'])) {
                $record['controllers'] = [];
                foreach ($filters['controllers'] as $controller) {
                    $record['controllers'][] = (isset($supportedControllers[$controller]) ? $supportedControllers[$controller]['name'] : $controller);
                }
                $record['controllers'] = implode(', ', $record['controllers']);
            } else {
                $record['controllers'] = '---';
            }
        }

        return new GridData(
            new RecordCollection($records),
            $data->getRecordsTotal(),
            $data->getQuery()
        );
    }
}
