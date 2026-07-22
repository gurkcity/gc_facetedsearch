# Faceted search module (gc_facetedsearch)

Fork of PrestaShop `ps_facetedsearch`, rebranded for Onlineshopmodule.

## About

Filter your catalog to help visitors picture the category tree and browse your store easily.

**Technical name:** `gc_facetedsearch`  
**Main class:** `GC_FacetedSearch`  
**Namespace:** `Onlineshopmodule\PrestaShop\Module\FacetedSearch`

## Compatibility

PrestaShop: 8.2.0 or later

## Installation

Install dependencies (development):

```
npm install
composer install
npm run build
```

Copy the module folder to `modules/gc_facetedsearch` and install it from the Back Office.

### Migration from `ps_facetedsearch`

On install, if the official `ps_facetedsearch` module is installed:

1. Configuration (`PS_LAYERED_*` → `GC_LAYERED_*`, `PS_USE_JQUERY_UI_SLIDER` → `GC_USE_JQUERY_UI_SLIDER`) is copied
2. Filter templates and category filter assignments are transferred
3. Indexable attribute/feature URL & meta data is transferred
4. `ps_facetedsearch` is uninstalled
5. Price and attribute indexes are rebuilt

Theme overrides under `themes/*/modules/ps_facetedsearch/` must be moved to `themes/*/modules/gc_facetedsearch/`.

## Usage

```
npm run dev # Watch js/css files for changes
npm run build # Build for production
```

## License

This module is released under the [Academic Free License 3.0](https://opensource.org/licenses/AFL-3.0).
