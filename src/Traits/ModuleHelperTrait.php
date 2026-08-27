<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

namespace Onlineshopmodule\PrestaShop\Module\Facetedsearch\Traits;

use Onlineshopmodule\PrestaShop\Module\Facetedsearch\Module\OverrideTools;

trait ModuleHelperTrait
{
    public function smartyAssign(
        array $params,
        string $templateName = ''
    ): string {
        if ($params) {
            $this->smarty->assign($params);
        }

        if ($templateName) {
            $template = $this->getTemplateFromName($templateName);

            return $this->fetch($template);
        }

        return '';
    }

    public function smartyAssignCached(
        string $templateName,
        callable $generateFunction,
        ?string $cacheId = null
    ): string {
        $template = $this->getTemplateFromName($templateName);

        $cacheId = $this->getCacheId($this->name . '|' . $cacheId);

        if (!$this->isCached($template, $cacheId)) {
            $smartyVars = $generateFunction();

            if ($smartyVars) {
                $this->smarty->assign(
                    $smartyVars
                );
            }
        }

        return $this->fetch($template, $cacheId);
    }

    public function clearSmartyCache(string $templateName, string $cacheId = ''): int
    {
        if ($templateName === '*') {
            $templatesCleared = 0;

            foreach ($this->smartyTemplates as $template) {
                $templatesCleared += $this->_clearCache($template);
            }

            return $templatesCleared;
        }

        $template = $this->smartyTemplates[$templateName] ?? '';

        if (!$template) {
            return 0;
        }

        if ($cacheId === '') {
            $cacheId = null;
        }

        return (int) $this->_clearCache($template, $cacheId);
    }

    public static function isDevMode(): bool
    {
        if (
            !empty($_SERVER['SERVER_NAME'])
            && (
                $_SERVER['SERVER_NAME'] == 'localhost'
                || strpos($_SERVER['SERVER_NAME'], 'shopbetreiber.info') !== false
            )
        ) {
            return true;
        }

        return false;
    }

    public function getCookie(): \Cookie
    {
        return new \Cookie('bo_messages', '', time() + \Configuration::get('PS_COOKIE_LIFETIME_BO') * 60);
    }

    public function hasJavascriptFiles(bool $front = true): bool
    {
        $folder = $this->getLocalPath() . 'views/js/' . ($front ? 'front' : 'admin') . '/';

        return is_dir($folder) && count(glob($folder . '*.js')) > 0;
    }

    public function hasStylesheetFiles(bool $front = true): bool
    {
        $folder = $this->getLocalPath() . 'views/css/' . ($front ? 'front' : 'admin') . '/';

        return is_dir($folder) && count(glob($folder . '*.css')) > 0;
    }

    public function hasHummingbirdTemplates(): bool
    {
        return $this->hasTemplateFiles(true);
    }

    public function hasHummingbirdJavascriptOrStylesheets(): bool
    {
        $folderCss = $this->getLocalPath() . 'views/css/front/';
        $folderJs = $this->getLocalPath() . 'views/js/front/';

        if (is_dir($folderCss)) {
            $iteratorCss = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($folderCss, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iteratorCss as $file) {
                if ($file->isFile() && strpos($file->getFilename(), '.hb.css') !== false) {
                    return true;
                }
            }
        }

        if (is_dir($folderJs)) {
            $iteratorJs = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($folderJs, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iteratorJs as $file) {
                if ($file->isFile() && strpos($file->getFilename(), '.hb.js') !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    public function hasTemplateFiles(bool $hummingbird = false): bool
    {
        $folder = $this->getLocalPath() . 'views/templates/';

        if (!is_dir($folder)) {
            return false;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folder, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $extension = $hummingbird ? '.hb.tpl' : '.tpl';

        foreach ($iterator as $file) {
            if ($file->isFile() && strpos($file->getFilename(), $extension) !== false) {
                return true;
            }
        }

        return false;
    }

    public function getGCModuleVersion(): string
    {
        /*
         * Note: PHP trait constants are only available from version 8.2, so a property is used here instead of:
         * return self::GC_VERSION;
         */

        return $this->GC_VERSION;
    }

    public function getGCModuleSubversion(): string
    {
        /*
         * Note: PHP trait constants are only available from version 8.2, so a property is used here instead of:
         * return self::GC_SUBVERSION;
         */

        return $this->GC_SUBVERSION;
    }

    public function getOverrideTools(): OverrideTools
    {
        try {
            $overrideTools = $this->get('onlineshopmodule.module.facetedsearch.module.overridetools');
        } catch (\Throwable $e) {
            // Do nothing!
        }

        if (empty($overrideTools)) {
            $overrideTools = new OverrideTools($this);
        }

        return $overrideTools;
    }

    public function getTemplateFromName(string $templateName): string
    {
        $template = $templateName;

        if (!empty($this->smartyTemplates[$templateName])) {
            $template = $this->smartyTemplates[$templateName];
        }

        if (strpos($template, 'module:') !== 0) {
            $template = 'module:' . $this->name . '/' . $template;
        }

        if ($this->getConfig()->get('THEME') === 'hummingbird') {
            $templateHummingbird = str_replace('.tpl', '.hb.tpl', $template);

            if (file_exists(_PS_MODULE_DIR_ . str_replace('module:', '', $templateHummingbird))) {
                $template = $templateHummingbird;
            }
        }

        return $template;
    }

    public function getTemplateForController(string $templateName, string $relativePath = '../../../modules/'): string
    {
        $template = $templateName;

        if (strpos($template, 'module:') === 0) {
            $template = _PS_MODULE_DIR_ . str_replace('module:', '', $template);
        }

        if ($this->getConfig()->get('THEME') === 'hummingbird') {
            $templateHummingbird = str_replace('.tpl', '.hb.tpl', $template);

            if (file_exists($templateHummingbird)) {
                $template = $templateHummingbird;
            }
        }

        if (!file_exists($template)) {
            throw new \Exception(sprintf('Template file "%s" not found.', $template));
        } else {
            $template = str_replace(_PS_MODULE_DIR_, $relativePath, $template);
        }

        return $template;
    }

    public function isAdmin(): bool
    {
        return (bool) (new \Cookie('psAdmin'))->id_employee;
    }

    public function isSuperadmin(): bool
    {
        $idEmployee = (new \Cookie('psAdmin'))->id_employee;

        if (!$idEmployee) {
            return false;
        }

        $employee = new \Employee($idEmployee);

        return $employee->isSuperAdmin();
    }
}
