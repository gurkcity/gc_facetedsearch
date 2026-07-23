<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

namespace Onlineshopmodule\PrestaShop\Module\Facetedsearch\Maintenance;

class Maintenance
{
    private $hookMaintenance;
    private $tabMaintenance;
    private $sqlMaintenance;
    private $configMaintenance;
    private $controllerMaintenance;
    private $orderStateMaintenance;
    private $templateMaintenance;
    private $cachingMaintenance;

    public function __construct(
        HookMaintenance $hookMaintenance,
        TabMaintenance $tabMaintenance,
        SqlMaintenance $sqlMaintenance,
        ConfigMaintenance $configMaintenance,
        ControllerMaintenance $controllerMaintenance,
        OrderstateMaintenance $orderStateMaintenance,
        TemplateMaintenance $templateMaintenance,
        CachingMaintenance $cachingMaintenance
    ) {
        $this->hookMaintenance = $hookMaintenance;
        $this->tabMaintenance = $tabMaintenance;
        $this->sqlMaintenance = $sqlMaintenance;
        $this->configMaintenance = $configMaintenance;
        $this->controllerMaintenance = $controllerMaintenance;
        $this->orderStateMaintenance = $orderStateMaintenance;
        $this->templateMaintenance = $templateMaintenance;
        $this->cachingMaintenance = $cachingMaintenance;
    }

    public function getHooks(): array
    {
        return $this->hookMaintenance->get();
    }

    public function resetHooks(): bool
    {
        return $this->hookMaintenance->reset();
    }

    public function getTabs(): array
    {
        return $this->tabMaintenance->get();
    }

    public function resetTabs(): bool
    {
        return $this->tabMaintenance->reset();
    }

    public function getSql(): array
    {
        return [
            'tables' => $this->sqlMaintenance->get(),
            'differences' => $this->sqlMaintenance->getDifferencesSql(),
        ];
    }

    public function resetSql(): bool
    {
        return $this->sqlMaintenance->reset();
    }

    public function getConfig(): array
    {
        return $this->configMaintenance->get();
    }

    public function resetConfig(): bool
    {
        return $this->configMaintenance->reset();
    }

    public function getController(): array
    {
        return $this->controllerMaintenance->get();
    }

    public function resetController(): bool
    {
        return $this->controllerMaintenance->reset();
    }

    public function getOrderstates(): array
    {
        return $this->orderStateMaintenance->get();
    }

    public function resetOrderstates(): bool
    {
        return $this->orderStateMaintenance->reset();
    }

    public function getTemplates(): array
    {
        return $this->templateMaintenance->get();
    }

    public function getCaching(): array
    {
        return $this->cachingMaintenance->get();
    }

    public function clearCaching(): bool
    {
        return $this->cachingMaintenance->remove();
    }

    public function resetAll(): bool
    {
        $result = $this->hookMaintenance->reset();
        $result = $this->tabMaintenance->reset() && $result;
        $result = $this->sqlMaintenance->reset() && $result;
        $result = $this->configMaintenance->reset() && $result;
        $result = $this->controllerMaintenance->reset() && $result;
        $result = $this->orderStateMaintenance->reset() && $result;
        $result = $this->cachingMaintenance->reset() && $result;

        return $result;
    }
}
