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

use Doctrine\DBAL\Connection;
use Hook as PS_Hook;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Settings\Hook;
use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Settings\SettingsInterface;

class HookMaintenance implements MaintenanceInterface
{
    private $module;
    private $shopId;
    private $connection;
    private $dbPrefix = '';
    private $hooks = [];

    public function __construct(
        \GC_Facetedsearch $module,
        int $shopId,
        Connection $connection,
        string $dbPrefix
    ) {
        $this->module = $module;
        $this->shopId = $shopId;
        $this->connection = $connection;
        $this->dbPrefix = $dbPrefix;

        $this->hooks = $this->module->getSettings()->getHooks();
    }

    public function get(): array
    {
        $hooks = [
            'module' => [],
            'unnecessary' => [],
        ];

        $positionsExists = false;

        foreach ($this->hooks as $hook) {
            $position = $hook->getPosition();

            if ($position !== Hook::POSITION_NULL) {
                $positionsExists = true;
            }

            $hooks['module'][(string) $hook] = [
                'name' => (string) $hook,
                'position' => $position,
                'registered' => $this->isValid($hook, $this->shopId),
                'alternatives' => $hook->getAlternatives(),
                'position_real' => $this->getHookPosition($hook),
            ];
        }

        $hooks['unnecessary'] = $this->getUnnecassaryHooks();

        ksort($hooks['module']);
        sort($hooks['unnecessary']);

        $hooks['shops'] = \Shop::getShops(false);
        $hooks['positions_exists'] = $positionsExists;

        return $hooks;
    }

    public function reset(): bool
    {
        $registered = true;

        foreach ($this->hooks as $hook) {
            if ($this->isValid($hook, $this->shopId)) {
                continue;
            }

            $registered = $this->module->registerHook((string) $hook) && $registered;
        }

        foreach ($this->getUnnecassaryHooks() as $hooksUnnessesary) {
            $this->module->unregisterHook($hooksUnnessesary) && $registered;
        }

        return $registered;
    }

    public function remove(): bool
    {
        $idModule = (int) $this->connection->fetchOne('
            SELECT id_module
            FROM `' . $this->dbPrefix . 'module`
            WHERE name = \'' . $this->module->name . '\'
        ');

        if (empty($idModule)) {
            return true;
        }

        $this->connection->executeStatement('
            DELETE FROM `' . $this->dbPrefix . 'hook_module`
            WHERE id_module = ' . $idModule . '
            AND `id_shop` = ' . $this->shopId . '
        ');

        return true;
    }

    public function isValid(SettingsInterface $hook, int $shopId): bool
    {
        /** @var Hook $hook */
        if (PS_Hook::isModuleRegisteredOnHook(
            $this->module,
            (string) $hook,
            $shopId
        )) {
            return true;
        }

        foreach ($hook->getAlternatives() as $alternative) {
            if (PS_Hook::isModuleRegisteredOnHook(
                $this->module,
                (string) $alternative,
                $shopId
            )) {
                return true;
            }
        }

        return false;
    }

    protected function getUnnecassaryHooks(): array
    {
        $allHookNames = [];

        foreach ($this->hooks as $hook) {
            $allHookNames[] = (string) $hook;

            foreach ($hook->getAlternatives() as $alternativeHookNames) {
                $allHookNames[] = $alternativeHookNames;
            }
        }

        $allHookNames = array_unique($allHookNames);

        return $this->connection->fetchFirstColumn('
            SELECT h.`name`
            FROM `' . $this->dbPrefix . 'hook_module` AS hm
            LEFT JOIN `' . $this->dbPrefix . 'hook` AS h ON (h.`id_hook` = hm.`id_hook`)
            WHERE hm.`id_hook` IN (
                SELECT `id_hook`
                FROM `' . $this->dbPrefix . 'hook`
                WHERE `name` NOT IN (\'' . implode('\',\'', $allHookNames) . '\')
            )
            AND hm.`id_module` = ' . (int) $this->module->id . '
            AND hm.`id_shop` = ' . $this->shopId . '
        ');
    }

    protected function getHookPosition(SettingsInterface $hook): string
    {
        /** @var Hook $hook */
        if ($hook->getPosition() === Hook::POSITION_NULL) {
            return Hook::POSITION_NULL;
        }

        $hookName = (string) $hook;

        $idHook = (int) $this->connection->fetchOne('
            SELECT `id_hook`
            FROM `' . $this->dbPrefix . 'hook`
            WHERE `name` = \'' . $hookName . '\'
        ');

        if ($idHook > 0) {
            $idModule = (int) $this->connection->fetchOne('
                SELECT id_module
                FROM `' . $this->dbPrefix . 'hook_module`
                WHERE `id_hook` = ' . $idHook . '
                AND `id_shop` = ' . $this->shopId . '
                ORDER BY `position` ASC
            ');

            if ($idModule == $this->module->id) {
                return Hook::POSITION_TOP;
            }

            $idModule = (int) $this->connection->fetchOne('
                SELECT id_module
                FROM `' . $this->dbPrefix . 'hook_module`
                WHERE `id_hook` = ' . $idHook . '
                AND `id_shop` = ' . $this->shopId . '
                ORDER BY `position` DESC
            ');

            if ($idModule == $this->module->id) {
                return Hook::POSITION_BOTTOM;
            }
        }

        return '';
    }
}
