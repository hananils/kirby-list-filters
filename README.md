[![List Filters for Kirby CMS](header.png)](https://kirby.hananils.de/plugins/list-filters)

Kirby allows for the storage of value lists using its select, tags or checkboxes fields but it doesn’t offer ways to filter items by those values. List Filters closes this gap by providing `includes` and `excludes` collection filters.

## Usage

The plugin adds the following filters:

- `includes`: finds items including the given values (like `in_array`)
- `includes some`: finds items including some of the given values
- `includes all`: finds items including all of the given values
- `excludes`: finds items excluding the given values (like `in_array`)
- `excludes some`: finds items excluding some of the given values
- `excludes all`: finds items excluding all of the given values

### Examples

```php
$plugins = $plugins->filterBy('category', 'includes all', ['field', 'section']);
```

## Installation

By default, plugins in Kirby reside in a special folder located at `/site/plugins`. Each plugin is installed in its proprietary subfolder. This installation can be handled in four different ways: you can either install them manually or manage them using Kirby CLI, Git submodules or Composer. You can install List Filters either way and should choose the method suiting your project best.

Please note that all examples given here assume you are using the default plugin root. [If you changed your plugin root](https://getkirby.com/docs/reference/system/roots/plugins), e. g. with a custom folder setup, you’ll also have to adjust the paths given in this guide. For further information on how to manage plugins, please read the [official Kirby plugin introduction](https://getkirby.com/docs/guide/plugins/plugin-basics).

### Download

Download and copy this repository to `/site/plugins/list-filters`.

### Kirby CLI

```shell
kirby plugin:install hananils/kirby-list-filters
```

### Git submodule

```bash
git submodule add \
    https://github.com/hananils/kirby-list-filters.git \
    site/plugins/list-filters
```

### Composer

```shell
composer require hananils/kirby-list-filters
```

## Documentation

[![Find all documentation at kirby.hananils.de](footer.png)](https://kirby.hananils.de/plugins/list-filters)

Where possible, files contain inline annotations. For extended documentation, please visit our dedicated plugin site at [kirby.hananils.de/​plugins/​list-filters](https://kirby.hananils.de/plugins/list-filters).

### Reference

- [Collection Filters](https://kirby.hananils.de/plugins/list-filters/collection-filters)
- [Routes](https://kirby.hananils.de/plugins/list-filters/routes)

## License

This plugin is provided freely under the [MIT license](https://kirby.hananils.de/plugins/list-filters/license) by [hana+nils · Büro für Gestaltung](https://kirby.hananils.de). We create visual designs for digital and analog media.