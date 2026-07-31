{**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 *}

{* template version 1, do not remove this comment from template *}

<section id="js-active-search-filters" class="{if $activeFilters|count}active_filters{else}hide{/if}">
  {block name='active_filters_title'}
    <p class="h6 {if $activeFilters|count}active-filter-title{else}hidden-xs-up{/if}">{l s='Active filters' d='Shop.Theme.Global'}</p>
  {/block}

  {if $activeFilters|count}
    <ul>
      {foreach from=$activeFilters item="filter"}
        {block name='active_filters_item'}
          <li class="filter-block">
            {l s='%1$s:' d='Shop.Theme.Catalog' sprintf=[$filter.facetLabel]}
            {$filter.label}
            <a class="js-search-link" href="{$filter.nextEncodedFacetsURL}"><i class="material-icons close">&#xE5CD;</i></a>
          </li>
        {/block}
      {/foreach}
    </ul>
  {/if}
</section>
