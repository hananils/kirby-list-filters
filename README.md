![Kirby List Filters](.github/title.png)

Kirby allows for the storage of value lists using its select, tags or checkboxes fields but it doesn't offer ways to filter items by those values. List Filters closes this gap by providing `includes` and `excludes` collection filters.

> [!NOTE]
> Please check out the online documentation at [kirby.hananils.de/plugins/list-filters](https://kirby.hananils.de/plugins/list-filters) for further information.

# Usage

The plugin adds the following filters:

- `includes`: finds items including the given values (like `in_array`)
- `includes some`: finds items including some of the given values
- `includes all`: finds items including all of the given values
- `excludes`: finds items excluding the given values (like `in_array`)
- `excludes some`: finds items excluding some of the given values
- `excludes all`: finds items excluding all of the given values

## Examples

```php
$plugins = $plugins->filterBy('category', 'includes all', ['field', 'section']);
```

## Options

- `hananils.list-filters.insensitive`: defaults to `true`, set to `false` if you want to enable case sensitive matching.

## Installation

### Download

Download and copy this repository to `/site/plugins/list-filters`.

### Git submodule

```
git submodule add https://github.com/hananils/kirby-list-filters.git site/plugins/list-filters
```

### Composer

```
composer require hananils/kirby-list-filters
```

# License

This plugin is provided freely under the [MIT license](LICENSE.md) by [hana+nils · Büro für Gestaltung](https://hananils.de).  
We create visual designs for digital and analog media.
