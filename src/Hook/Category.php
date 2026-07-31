<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

namespace Onlineshopmodule\PrestaShop\Module\Facetedsearch\Hook;

use Configuration;
use Tools;

class Category extends AbstractHook
{
    const AVAILABLE_HOOKS = [
        'actionCategoryAdd',
        'actionCategoryDelete',
    ];

    /**
     * Category addition
     *
     * @param array $params
     */
    public function actionCategoryAdd(array $params)
    {
        $this->addCategoryToDefaultFilter((int) $params['category']->id);

        // Flush filter block cache in all cases, so a new category shows up
        $this->module->invalidateLayeredFilterBlockCache();
    }

    /**
     * Category deletion
     *
     * @param array $params
     */
    public function actionCategoryDelete(array $params)
    {
        $this->removeCategoryFromFilterTemplates((int) $params['category']->id);
    }

    /**
     * Clean and rebuild category filters
     *
     * @param int $idCategory
     */
    private function removeCategoryFromFilterTemplates(int $idCategory)
    {
        // Get all filter templates
        $filterTemplates = $this->database->executeS(
            'SELECT * FROM ' . _DB_PREFIX_ . 'gc_facetedsearch_filter'
        );

        $rebuildNeeded = false;

        // Go through each template, check if our category is set for this template.
        // If yes, remove it and update the template.
        foreach ($filterTemplates as $template) {
            $filters = Tools::unSerialize($template['filters']);
            if (!in_array((int) $idCategory, $filters['categories'])) {
                continue;
            }
            unset($filters['categories'][array_search((int) $idCategory, $filters['categories'])]);
            $rebuildNeeded = true;
            $this->database->execute(
                'UPDATE `' . _DB_PREFIX_ . 'gc_facetedsearch_filter` 
                SET `filters` = "' . pSQL(serialize($filters)) . '", 
                n_categories = ' . (int) count($filters['categories']) . ' 
                WHERE `id_gc_facetedsearch_filter` = ' . (int) $template['id_gc_facetedsearch_filter']
            );
        }

        // Rebuild filter table only if a category was removed from a filter
        if ($rebuildNeeded) {
            $this->module->buildLayeredCategories();
        }

        // Flush cache all the time, because the category could be cached in a category filter block
        $this->module->invalidateLayeredFilterBlockCache();
    }

    /**
     * Checks if module is configured to automatically add some filter to new categories.
     * If so, it adds the new category.
     *
     * @param int $idCategory ID of category being created
     */
    public function addCategoryToDefaultFilter(int $idCategory)
    {
        // Get default template
        $defaultFilterTemplateId = (int) Configuration::get('GC_FACETEDSEARCH_DEFAULT_CATEGORY_TEMPLATE');
        if (empty($defaultFilterTemplateId)) {
            return;
        }

        // Try to get it's data
        $template = $this->module->getFilterTemplate($defaultFilterTemplateId);
        if (empty($template)) {
            return;
        }

        // Unserialize filters, add our category
        $filters = Tools::unSerialize($template['filters']);
        $filters['categories'][] = $idCategory;

        // Update it in database
        $this->database->execute(
            'UPDATE `' . _DB_PREFIX_ . 'gc_facetedsearch_filter` 
            SET `filters` = "' . pSQL(serialize($filters)) . '", 
            n_categories = ' . (int) count($filters['categories']) . ' 
            WHERE `id_gc_facetedsearch_filter` = ' . $defaultFilterTemplateId
        );

        $this->module->buildLayeredCategories();
    }
}
