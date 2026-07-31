{**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 *}

{* template version 1, do not remove this comment from template *}

{$componentName = 'active-filters'}

<div id="js-active-search-filters" class="{$componentName}">
  {if $activeFilters|count}
    <ul class="{$componentName}__list">
      {block name='active_filters_title'}
        <li class="{$componentName}__item">
          <span class="{$componentName}__title">{l s='Active filters' d='Shop.Theme.Global'}</span>
        </li>
      {/block}
      {foreach from=$activeFilters item="filter"}
        {block name='active_filters_item'}
          <li class="{$componentName}__item">
            <a
              class="{$componentName}__link btn btn-outline-tertiary rounded-pill btn-sm js-search-link"
              href="{$filter.nextEncodedFacetsURL}"
              rel="nofollow"
              aria-label="{l s='Remove %1$s filter: %2$s' d='Shop.Theme.Catalog' sprintf=[$filter.facetLabel|lower, $filter.label]}"
            >
              {l s='%1$s:' d='Shop.Theme.Catalog' sprintf=[$filter.facetLabel]} {$filter.label}
              <i class="material-icons" aria-hidden="true">&#xE14C;</i>
            </a>
          </li>
        {/block}
      {/foreach}
    </ul>
  {/if}
</div>
