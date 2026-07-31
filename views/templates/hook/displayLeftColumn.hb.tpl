{**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 *}

{* template version 1, do not remove this comment from template *}

{if isset($listing.rendered_facets) && !empty($listing.rendered_facets)}
  <div id="search_filters_wrapper" class="ps-facetedsearch d-none d-md-block left-block">
    <div id="_desktop_faceted">
      {$listing.rendered_facets nofilter}
    </div>
  </div>

  <div class="ps-facetedsearch ps-facetedsearch--mobile offcanvas offcanvas-start" tabindex="-1" id="offcanvas-faceted" aria-labelledby="faceted-offcanvas-label">
    <div class="offcanvas-header">
      <p class="h5 offcanvas-title" id="faceted-offcanvas-label">{l s='Filter By' d='Shop.Theme.Actions'}</p>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
      <div id="_mobile_faceted"></div>
    </div>
  </div>
{/if}
