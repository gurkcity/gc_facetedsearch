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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Query\AbstractDoctrineQueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Query\DoctrineSearchCriteriaApplicatorInterface;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;

class FiltersQueryBuilder extends AbstractDoctrineQueryBuilder
{
    /**
     * @var DoctrineSearchCriteriaApplicatorInterface
     */
    private $searchCriteriaApplicator;

    public function __construct(
        Connection $connection,
        string $dbPrefix,
        DoctrineSearchCriteriaApplicatorInterface $searchCriteriaApplicator
    ) {
        parent::__construct($connection, $dbPrefix);

        $this->searchCriteriaApplicator = $searchCriteriaApplicator;
    }

    public function getSearchQueryBuilder(SearchCriteriaInterface $searchCriteria): QueryBuilder
    {
        $qb = $this->getQueryBuilder($searchCriteria->getFilters())
            ->select('
                a.id_gc_facetedsearch_filter,
                a.name,
                a.filters,
                a.n_categories,
                a.date_add
            ');

        $this->searchCriteriaApplicator
            ->applyPagination($searchCriteria, $qb)
            ->applySorting($searchCriteria, $qb)
        ;

        return $qb;
    }

    public function getCountQueryBuilder(SearchCriteriaInterface $searchCriteria): QueryBuilder
    {
        $qb = $this->getQueryBuilder($searchCriteria->getFilters())
            ->select('COUNT(DISTINCT a.`id_gc_facetedsearch_filter`)')
        ;

        return $qb;
    }

    private function getQueryBuilder(array $filters): QueryBuilder
    {
        $qb = $this->connection
            ->createQueryBuilder()
            ->from($this->dbPrefix . 'gc_facetedsearch_filter', 'a')
        ;

        $this->applyFilters($qb, $filters);

        return $qb;
    }

    private function applyFilters(QueryBuilder $qb, array $filters): void
    {
        $allowedFiltersAliasMap = [
            'name' => 'a.name',
            'id_gc_facetedsearch_filter' => 'a.id_gc_facetedsearch_filter',
            'n_categories' => 'a.n_categories',
            'date_add' => 'a.date_add',
        ];

        $exactMatchFilters = ['id_gc_facetedsearch_filter'];

        foreach ($filters as $filterName => $value) {
            if (!array_key_exists($filterName, $allowedFiltersAliasMap)) {
                return;
            }

            if (in_array($filterName, $exactMatchFilters, true)) {
                $qb->andWhere($allowedFiltersAliasMap[$filterName] . ' = :' . $filterName);
                $qb->setParameter($filterName, $value);

                continue;
            }

            if (in_array($filterName, ['date_add'])) {
                if (!empty($value['from'])) {
                    $qb->andWhere($allowedFiltersAliasMap[$filterName] . ' >= :' . $filterName . '_from')
                        ->setParameter(
                            $filterName . '_from',
                            $value['from'] . ' 00:00:00',
                            \Doctrine\DBAL\Types\Types::STRING
                        );
                }
                if (!empty($value['to'])) {
                    $qb->andWhere($allowedFiltersAliasMap[$filterName] . ' <= :' . $filterName . '_to')
                        ->setParameter(
                            $filterName . '_to',
                            $value['to'] . ' 23:59:59',
                            \Doctrine\DBAL\Types\Types::STRING
                        )
                    ;
                }
                continue;
            }

            $qb->andWhere($allowedFiltersAliasMap[$filterName] . ' LIKE :' . $filterName);
            $qb->setParameter($filterName, "%$value%");
        }
    }
}
