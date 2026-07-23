<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

namespace Onlineshopmodule\PrestaShop\Module\Facetedsearch\Settings;

use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Exception\SettingException;

class Hook implements SettingsInterface
{
    public const POSITION_NULL = '';
    public const POSITION_TOP = 'top';
    public const POSITION_BOTTOM = 'bottom';

    private $name = '';
    private $alternatives = [];
    private $controllerWhitelist = [];
    private $position = self::POSITION_NULL;

    public function __construct(
        string $name,
        array $alternatives = [],
        array $controllerWhitelist = [],
        string $position = self::POSITION_NULL
    ) {
        if (!\Validate::isHookName($name)) {
            throw new SettingException('Hook name is not valid');
        }

        $this->name = $name;
        $this->alternatives = $alternatives;
        $this->controllerWhitelist = $controllerWhitelist;
        $this->position = $position;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAlternatives(): array
    {
        return $this->alternatives;
    }

    public function getControllerWhitelist(): array
    {
        return $this->controllerWhitelist;
    }

    public function getPosition(): string
    {
        return $this->position;
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function hasAlternatives(): bool
    {
        return !empty($this->alternatives);
    }

    public function hasControllerWhitelist(): bool
    {
        return !empty($this->controllerWhitelist);
    }

    public function isInAlternatives(string $hookName): bool
    {
        if (!$this->hasAlternatives()) {
            return false;
        }

        return in_array($hookName, $this->alternatives);
    }
}
