<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

namespace Onlineshopmodule\PrestaShop\Module\Facetedsearch\Plugin;

class PluginLoader
{
    protected $pluginFolder;
    protected $context;

    private $loadedPlugins;

    public function __construct(
        string $pluginFolder,
        \Context $context
    ) {
        if (substr($pluginFolder, -1) !== '/') {
            $pluginFolder .= '/';
        }

        $this->pluginFolder = $pluginFolder;
        $this->context = $context;
    }

   public function exec($hookName, $params = [])
    {
        try {
            $plugins = $this->loadPlugins();

            if (empty($params['context'])) {
                $params['context'] = $this->context;
            }

            $method = 'hook' . ucfirst($hookName);

            $result = [];

            foreach ($plugins as $pluginName => $plugin) {
                if (method_exists($plugin, $method)) {
                    try {
                        $result[$pluginName] = $plugin->$method($params);
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }

            return $result;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getHooksFromPlugin(PluginInterface $plugin): array
    {
        $hooks = get_class_methods($plugin);

        return array_filter($hooks, function ($method) {
            return strpos($method, 'hook') === 0;
        });
    }

    public function getPluginsInformation(): array
    {
        $info = [];
        $plugins = $this->loadPlugins();

        foreach ($plugins as $pluginName => $plugin) {
            $info[$pluginName] = [
                'class' => $pluginName,
                'name' => $plugin->getName(),
                'description' => $plugin->getDescription(),
                'hooks' => $this->getHooksFromPlugin($plugin),
            ];
        }

        return $info;
    }

    protected function loadPlugins()
    {
        if ($this->loadedPlugins !== null) {
            return $this->loadedPlugins;
        }

        $this->loadedPlugins = [];

        if (!is_dir($this->pluginFolder)) {
            throw new \InvalidArgumentException("Plugin folder does not exist: {$this->pluginFolder}");
        }

        // Use Symfony Finder to get all PHP files except index.php
        $finder = new \Symfony\Component\Finder\Finder();
        $finder->files()
            ->in($this->pluginFolder)
            ->name('*.php')
            ->notName('index.php');

        foreach ($finder as $file) {
            $fileRealPath = $file->getRealPath();

            include_once $fileRealPath;

            $className = str_replace('.php', '', $file->getFilename());

            if (class_exists($className)) {
                $plugin = new $className();

                if ($plugin instanceof PluginInterface) {
                    $this->loadedPlugins[$className] = $plugin;
                }
            }
        }

        return $this->loadedPlugins;
    }
}
