<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

namespace Onlineshopmodule\PrestaShop\Module\Facetedsearch\Adapter;

use Doctrine\Common\Collections\ArrayCollection;

abstract class AbstractAdapter implements InterfaceAdapter
{
    /**
     * @var ArrayCollection
     */
    protected $filters;

    /**
     * @var ArrayCollection
     */
    protected $operationsFilters;

    /**
     * @var ArrayCollection
     */
    protected $selectFields;

    /**
     * @var ArrayCollection
     */
    protected $groupFields;

    protected $orderField = 'id_product';

    protected $orderDirection = 'DESC';

    /** @var InterfaceAdapter */
    protected $initialPopulation = null;

    public function __construct()
    {
        $this->groupFields = new ArrayCollection();
        $this->selectFields = new ArrayCollection();
        $this->filters = new ArrayCollection();
        $this->operationsFilters = new ArrayCollection();
    }

    public function __clone()
    {
        $this->filters = clone $this->filters;
        $this->operationsFilters = clone $this->operationsFilters;
        $this->groupFields = clone $this->groupFields;
        $this->selectFields = clone $this->selectFields;
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialPopulation()
    {
        return $this->initialPopulation;
    }

    /**
     * {@inheritdoc}
     */
    public function resetFilter($filterName)
    {
        if ($this->filters->offsetExists($filterName)) {
            $this->filters->offsetUnset($filterName);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function resetOperationsFilter($filterName)
    {
        if ($this->operationsFilters->offsetExists($filterName)) {
            $this->operationsFilters->offsetUnset($filterName);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function resetOperationsFilters()
    {
        $this->operationsFilters = new ArrayCollection();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function resetAll()
    {
        $this->selectFields = new ArrayCollection();
        $this->groupFields = new ArrayCollection();
        $this->filters = new ArrayCollection();
        $this->operationsFilters = new ArrayCollection();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilter($filterName)
    {
        if (isset($this->filters[$filterName])) {
            return $this->filters[$filterName];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderDirection()
    {
        return $this->orderDirection;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderField()
    {
        return $this->orderField;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupFields()
    {
        return $this->groupFields;
    }

    /**
     * {@inheritdoc}
     */
    public function getSelectFields()
    {
        return $this->selectFields;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * {@inheritdoc}
     */
    public function getOperationsFilters()
    {
        return $this->operationsFilters;
    }

    /**
     * {@inheritdoc}
     */
    public function copyFilters(InterfaceAdapter $adapter)
    {
        $this->filters = clone $adapter->getFilters();
        $this->operationsFilters = clone $adapter->getOperationsFilters();
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter($filterName, $values, $operator = '=')
    {
        $filters = $this->filters->get($filterName);
        if (!isset($filters[$operator])) {
            $filters[$operator] = [];
        }

        $filters[$operator][] = $values;
        $this->filters->set($filterName, $filters);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addOperationsFilter($filterName, array $operations = [])
    {
        $this->operationsFilters->set($filterName, $operations);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addSelectField($fieldName)
    {
        if (!$this->selectFields->contains($fieldName)) {
            $this->selectFields->add($fieldName);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSelectFields($selectFields)
    {
        $this->selectFields = new ArrayCollection($selectFields);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function resetSelectField()
    {
        $this->selectFields = new ArrayCollection();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addGroupBy($groupField)
    {
        $this->groupFields->add($groupField);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setGroupFields($groupFields)
    {
        $this->groupFields = new ArrayCollection($groupFields);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function resetGroupBy()
    {
        $this->groupFields = new ArrayCollection();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setFilter($filterName, $value)
    {
        if ($value !== null) {
            $this->filters->set($filterName, $value);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderField($fieldName)
    {
        $this->orderField = $fieldName;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderDirection($direction)
    {
        $this->orderDirection = $direction === 'desc' ? 'desc' : 'asc';

        return $this;
    }
}
