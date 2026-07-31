{**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 *}

{* template version 1, do not remove this comment from template *}

{if isset($listing.rendered_active_filters)}
  {$listing.rendered_active_filters nofilter}
{/if}

{if isset($listing.rendered_facets)}
  {$listing.rendered_facets nofilter}
{/if}
