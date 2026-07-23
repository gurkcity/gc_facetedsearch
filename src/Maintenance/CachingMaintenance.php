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

class CachingMaintenance implements MaintenanceInterface
{
    private $module;

    public function __construct(
        \GC_Facetedsearch $module
    ) {
        $this->module = $module;
    }

    public function get(): array
    {
        $files = [];
        $totalFiles = 0;
        $totalSize = 0;

        $cacheDir = $this->getCacheDir();
        $iterator = $this->getCacheDirIterator();

        if (!$iterator) {
            return [
                'files' => [],
                'total_count' => 0,
                'total_size' => 0,
            ];
        }

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $filePath = $file->getPath();
            $filePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filePath);
            $filePath = realpath($filePath . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..');

            $relativePath = str_replace($cacheDir, '', $filePath);
            $relativePath = trim($relativePath, DIRECTORY_SEPARATOR);

            $parts = explode(DIRECTORY_SEPARATOR, $relativePath);

            $sizeInKb = round($file->getSize() / 1024, 2);

            $files[] = [
                'filename' => $file->getFilename(),
                'path' => $file->getPath(),
                'size' => $sizeInKb, // Convert bytes to KB
                'relative_path' => $relativePath,
                'parts' => $parts,
            ];

            ++$totalFiles;
            $totalSize += $sizeInKb;
        }

        return [
            'files' => $files,
            'total_count' => $totalFiles,
            'total_size' => $totalSize,
        ];
    }

    public function reset(): bool
    {
        return true;
    }

    public function remove(): bool
    {
        $iterator = $this->getCacheDirIterator();

        if (!$iterator) {
            return true;
        }

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            unlink($file->getPathname());
        }

        return true;
    }

    public function isValid($versionModule, $isOverridden, $versionOverride): bool
    {
        return true;
    }

    protected function getCacheDirIterator(): ?\RecursiveIteratorIterator
    {
        $cacheDir = $this->getCacheDir();

        if (
            empty($cacheDir)
            || !is_dir($cacheDir)
        ) {
            return null;
        }

        return new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($cacheDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
    }

    protected function getCacheDir(): string
    {
        return \Context::getContext()->smarty->getCacheDir() . $this->module->name . DIRECTORY_SEPARATOR;
    }
}
