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

class Config implements SettingsInterface
{
    private $name = '';
    private $value;
    private $html = false;
    private $global = false;
    private $uninstall = false;
    private $usePrefix = true;

    /**
     * @param string $name
     * @param string|array|int|float|bool $value
     * @param bool $html
     * @param bool $global
     * @param bool $uninstall
     * @param bool $usePrefix
     *
     * @return void
     *
     * @throws SettingException
     */
    public function __construct(
        string $name,
        string|array|int|float|bool $value,
        bool $html = false,
        bool $global = false,
        bool $uninstall = true,
        bool $usePrefix = true
    ) {
        if (!\Validate::isConfigName($name)) {
            throw new SettingException('Config name is not valid');
        }

        $this->name = strtoupper($name);
        $this->value = $value;
        $this->html = $html;
        $this->global = $global;
        $this->uninstall = $uninstall;
        $this->usePrefix = $usePrefix;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|array|int|float|bool
     */
    public function getValue()
    {
        return $this->value;
    }

    public function isHtml(): bool
    {
        return $this->html;
    }

    public function isGlobal(): bool
    {
        return $this->global;
    }

    public function canUninstall(): bool
    {
        return $this->uninstall;
    }

    public function usePrefix(): bool
    {
        return $this->usePrefix;
    }
}
