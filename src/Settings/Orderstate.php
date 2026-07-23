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
use OrderState as PS_OrderState;

class Orderstate implements SettingsInterface
{
    private $name = '';
    private $id = '';
    private $object;

    public function __construct(
        string $name,
        string $id,
        PS_OrderState $object
    ) {
        if (!\Validate::isName($name)) {
            throw new SettingException('Order State name is not valid');
        }

        $this->name = $name;
        $this->id = $id;
        $this->object = $object;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getObject(): PS_OrderState
    {
        return $this->object;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
