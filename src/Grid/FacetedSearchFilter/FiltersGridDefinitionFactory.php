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

use PrestaShop\PrestaShop\Adapter\Shop\Context;
use PrestaShop\PrestaShop\Core\Grid\Action\Bulk\BulkActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Bulk\BulkActionCollectionInterface;
use PrestaShop\PrestaShop\Core\Grid\Action\GridActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\GridActionCollectionInterface;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\LinkRowAction;
use PrestaShop\PrestaShop\Core\Grid\Action\Type\SimpleGridAction;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollectionInterface;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ActionColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\AbstractGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\BulkDeleteActionTrait;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\DeleteActionTrait;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\FilterableGridDefinitionFactoryInterface;
use PrestaShop\PrestaShop\Core\Grid\Filter\Filter;
use PrestaShop\PrestaShop\Core\Grid\Filter\FilterCollection;
use PrestaShop\PrestaShop\Core\Grid\Filter\FilterCollectionInterface;
use PrestaShop\PrestaShop\Core\Hook\HookDispatcherInterface;

class FiltersGridDefinitionFactory extends AbstractGridDefinitionFactory implements FilterableGridDefinitionFactoryInterface
{
	use BulkDeleteActionTrait;
    use DeleteActionTrait;

    public const GRID_ID = 'gc_facetedsearch_filtersgrid';

    /**
     * @param HookDispatcherInterface $hookDispatcher
     */
    public function __construct(
        HookDispatcherInterface $hookDispatcher
    ) {
        parent::__construct($hookDispatcher);
    }

    public function getFilterId(): string
    {
        return self::GRID_ID;
    }

    protected function getId(): string
    {
        return self::GRID_ID;
    }

    protected function getName(): string
    {
        return $this->trans('Filters templates', [], 'Modules.Gcfacetedsearch.Admin');
    }

    protected function getColumns(): ColumnCollectionInterface
    {
        $columns = (new ColumnCollection())
            ->add(
                (new DataColumn('id_gc_facetedsearch_filter'))
                    ->setName($this->trans('Id', [], 'Modules.Gcfacetedsearch.Admin'))
                    ->setOptions([
                        'field' => 'id_gc_facetedsearch_filter',
                    ])
            )
            ->add(
                (new DataColumn('name'))
                    ->setName($this->trans('Name', [], 'Modules.Gcfacetedsearch.Admin'))
                    ->setOptions([
                        'field' => 'name',
                    ])
            )
            ->add(
                (new DataColumn('controllers'))
                    ->setName($this->trans('Pages', [], 'Modules.Gcfacetedsearch.Admin'))
                    ->setOptions([
                        'field' => 'controllers',
                    ])
            )
            ->add(
                (new DataColumn('n_categories'))
                    ->setName($this->trans('Categories', [], 'Modules.Gcfacetedsearch.Admin'))
                    ->setOptions([
                        'field' => 'n_categories',
                    ])
            )
            ->add(
                (new DataColumn('date_add'))
                    ->setName($this->trans('Created on', [], 'Modules.Gcfacetedsearch.Admin'))
                    ->setOptions([
                        'field' => 'date_add',
                    ])
            )
            ->add(
                (new ActionColumn('actions'))
                    ->setName($this->trans('Actions', [], 'Modules.Gcfacetedsearch.Admin'))
                    ->setOptions([
                        'actions' => (new RowActionCollection())
                            ->add(
                                (new LinkRowAction('edit'))
                                ->setIcon('edit')
                                ->setName($this->trans('Edit', [], 'Modules.Gcfacetedsearch.Admin'))
                                ->setOptions([
                                    'route' => 'gc_facetedsearch_filtersgrid_edit',
                                    'route_param_name' => 'idTemplate',
                                    'route_param_field' => 'id_gc_facetedsearch_filter',
                                ])
                            )
                    ])
            )
        ;

        return $columns;
    }

    protected function getFilters(): FilterCollectionInterface
    {
        return new FilterCollection();
    }

    protected function getGridActions(): GridActionCollectionInterface
    {
        return (new GridActionCollection())
            ->add(
                (new SimpleGridAction('common_refresh_list'))
                    ->setName($this->trans('Refresh list', [], 'Modules.Gcfacetedsearch.Admin'))
                    ->setIcon('refresh')
            )
            ->add(
                (new SimpleGridAction('common_show_query'))
                    ->setName($this->trans('Show SQL query', [], 'Modules.Gcfacetedsearch.Admin'))
                    ->setIcon('code')
            )
            ->add(
                (new SimpleGridAction('common_export_sql_manager'))
                    ->setName($this->trans('Export to SQL Manager', [], 'Modules.Gcfacetedsearch.Admin'))
                    ->setIcon('storage')
            )
        ;
    }

    protected function getBulkActions(): BulkActionCollectionInterface
    {
        return new BulkActionCollection();
    }
}
