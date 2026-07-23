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

use PrestaShop\PrestaShop\Core\Context\ShopContext;
use Symfony\Component\Finder\Finder;

class TemplateMaintenance implements MaintenanceInterface
{
    private $module;
    private $finder;
    private $shopContext;

    public function __construct(
        \GC_Facetedsearch $module,
        Finder $finder,
        ShopContext $shopContext
    ) {
        $this->module = $module;
        $this->finder = $finder;
        $this->shopContext = $shopContext;
    }

    public function get(): array
    {
        $templates = [];

        $modulePath = $this->module->getLocalPath();
        $directory = $modulePath . 'views/templates/';
        $templates = [];

        $this->finder->files()->in($directory)->name('*.tpl');

        foreach ($this->finder as $file) {
            $overrideFile = $this->getOverrideFile($file, $modulePath);

            $versionModule = $this->getTemplateVersion($file);
            $versionOverride = $this->getTemplateVersion($overrideFile);
            $isOverridden = $overrideFile !== null;

            $templates[] = [
                'filename' => $file->getFilename(),
                'path' => $this->getRelativePath($file, $modulePath),
                'version' => $versionModule,
                'override_exists' => $isOverridden,
                'version_override' => $versionOverride,
                'valid' => $this->isValid($versionModule, $isOverridden, $versionOverride),
            ];
        }

        return $templates;
    }

    public function reset(): bool
    {
        return true;
    }

    public function remove(): bool
    {
        return true;
    }

    public function isValid($versionModule, $isOverridden, $versionOverride): bool
    {
        if (empty($versionModule)) {
            return true;
        }

        if (!$isOverridden) {
            return true;
        }

        if ($versionModule != $versionOverride) {
            return false;
        }

        return true;
    }

    private function getRelativePath(\SplFileInfo $file, $localPath): string
    {
        $fileRelativePath = $file->getRealPath();

        $localPath = str_replace('\\', '/', $localPath);
        $fileRelativePath = str_replace('\\', '/', $fileRelativePath);

        return str_replace($localPath, '', $fileRelativePath);
    }

    private function getOverrideFile(\SplFileInfo $file, $localPath): ?\SplFileInfo
    {
        $relativePath = $this->getRelativePath($file, $localPath);
        $overridePath = _PS_ALL_THEMES_DIR_ . $this->shopContext->getThemeName() . '/modules/' . $this->module->name . '/' . $relativePath;

        if (file_exists($overridePath)) {
            return new \SplFileInfo($overridePath);
        }

        return null;
    }

    private function getTemplateVersion(?\SplFileInfo $file): string
    {
        if ($file === null) {
            return '';
        }

        $content = file_get_contents($file->getRealPath());

        if (preg_match('/\{\*.*version\s*([\d\.]+).*\*\}/i', $content, $matches)) {
            return $matches[1];
        }

        return '';
    }
}
