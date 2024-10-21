<?php

namespace Hananils;

use Hananils\Plus\LicenseManager;
use Kirby\Cms\app as Kirby;
use Kirby\Data\Json;
use Kirby\Toolkit\Str;

$licenseManager = new LicenseManager(
    'hananils/list-filters',
    'List Filters',
    root: __DIR__
);

/**
 * @todo: Decide if this should be merged with Choices or List Methods?
 */

class ListValues
{
    private $values = [];

    public function __construct(
        $collection,
        $field,
        $item,
        $test,
        $split = true
    ) {
        $values = $collection->getAttribute(
            $item,
            $field,
            is_string($split) ? $split : true
        );

        $values = array_map(function ($value) use ($test) {
            $type = is_array($test) ? $test[0] : $test;

            return Str::toType($value, $type);
        }, $values);

        if (option('hananils.list-filters.insensitive', true) === true) {
            $values = array_merge(
                array_map('Kirby\Toolkit\Str::slug', $values),
                $values
            );
        }

        $this->values = $values;
    }

    public function includes(string $test)
    {
        return in_array($test, $this->values);
    }

    public function includesSome(array $test)
    {
        foreach ($test as $value) {
            if ($this->includes($value) === true) {
                return true;
            }
        }

        return false;
    }

    public function includesAll(array $test)
    {
        $result = true;

        foreach ($test as $value) {
            if ($this->includes($value) !== true) {
                $result = false;
                break;
            }
        }

        return $result;
    }

    public function excludes($test)
    {
        return !$this->includes($test);
    }

    public function excludesSome($test)
    {
        return $this->includesSome($test) && !$this->includesAll($test);
    }

    public function excludesAll($test)
    {
        $result = true;

        foreach ($test as $value) {
            if ($this->includes($value) === true) {
                $result = false;
                break;
            }
        }

        return $result;
    }
}

Kirby::plugin(
    'hananils/list-filters',
    [
        'api' => [
            'routes' => [
                [
                    'pattern' => 'hananils/list-filters/license',
                    'action' => function () {
                        $licenseManager = new LicenseManager(
                            'hananils/list-filters',
                            'List Filters',
                            get('locale', 'en')
                        );

                        return $licenseManager->toResponse();
                    }
                ]
            ]
        ],
        'collectionFilters' => [
            /**
             * Returns `true` for items that include the given value (string in array).
             */
            'includes' => function ($collection, $field, $test, $split = true) {
                foreach ($collection->data as $key => $item) {
                    $values = new ListValues(
                        $collection,
                        $field,
                        $item,
                        $test,
                        $split
                    );

                    if ($values->includes($test)) {
                        continue;
                    }

                    unset($collection->$key);
                }

                return $collection;
            },
            /**
             * Returns `true` for items that include some of the given values (array intersection).
             */
            'includes some' => function (
                $collection,
                $field,
                $test,
                $split = true
            ) {
                foreach ($collection->data as $key => $item) {
                    $values = new ListValues(
                        $collection,
                        $field,
                        $item,
                        $test,
                        $split
                    );

                    if ($values->includesSome($test)) {
                        continue;
                    }

                    unset($collection->$key);
                }

                return $collection;
            },
            /**
             * Returns `true` for items that include all of the given values (array equality).
             */
            'includes all' => function (
                $collection,
                $field,
                $test,
                $split = true
            ) {
                foreach ($collection->data as $key => $item) {
                    $values = new ListValues(
                        $collection,
                        $field,
                        $item,
                        $test,
                        $split
                    );

                    if ($values->includesAll($test)) {
                        continue;
                    }

                    unset($collection->$key);
                }

                return $collection;
            },
            /**
             * Returns `true` for items that exclude the given values (string not in array).
             */
            'excludes' => function ($collection, $field, $test, $split = true) {
                foreach ($collection->data as $key => $item) {
                    $values = new ListValues(
                        $collection,
                        $field,
                        $item,
                        $test,
                        $split
                    );

                    if ($values->excludes($test)) {
                        continue;
                    }

                    unset($collection->$key);
                }

                return $collection;
            },
            /**
             * Returns `true` for items that exclude some of the given values (array difference).
             */
            'excludes some' => function (
                $collection,
                $field,
                $test,
                $split = true
            ) {
                foreach ($collection->data as $key => $item) {
                    $values = new ListValues(
                        $collection,
                        $field,
                        $item,
                        $test,
                        $split
                    );

                    if ($values->excludesSome($test)) {
                        continue;
                    }

                    unset($collection->$key);
                }

                return $collection;
            },
            /**
             * Returns `true` for items that exclude all of the given values (array inequality).
             */
            'excludes all' => function (
                $collection,
                $field,
                $test,
                $split = true
            ) {
                foreach ($collection->data as $key => $item) {
                    $values = new ListValues(
                        $collection,
                        $field,
                        $item,
                        $test,
                        $split
                    );

                    if ($values->excludesAll($test)) {
                        continue;
                    }

                    unset($collection->$key);
                }

                return $collection;
            }
        ]
    ],
    $licenseManager->toInfo()
);
