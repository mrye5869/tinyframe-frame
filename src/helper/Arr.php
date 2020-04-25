<?php
// +----------------------------------------------------------------------
// | zibi [ WE CAN DO IT MORE SIMPLE]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2019 http://xmzibi.com All rights reserved.
// +----------------------------------------------------------------------
// | Author：MrYe       <email：55585190@qq.com>
// +----------------------------------------------------------------------
namespace og\helper;

use ArrayAccess;
use InvalidArgumentException;
use og\db\Collection;
use RecursiveIteratorIterator;
use RecursiveArrayIterator;

class Arr
{
    /**
     * Get array dimension
     *
     * @param array $array
     * @return int
     */
    public  static function dimension(array $array)
    {
        $it = new RecursiveIteratorIterator(new RecursiveArrayIterator($array));
        $d = 0;
        foreach ( $it as $v ) {
            $it->getDepth() >= $d and $d = $it->getDepth();
        }

        return ++ $d;
    }

    /**
     * Get the last element of the array
     *
     * @param $array
     * @return 0|bool
     */
    public static function end($array)
    {
        $count = count($array);
        $count = $count > 0 ? $count - 1 : 0;
        $newArr = array_slice($array, $count, 1);

        return !empty($newArr[0]) ? $newArr[0] : false;
    }

    /**
     * Get the first element of data
     *
     * @param $array
     * @return 0|bool
     */
    public static function first($array)
    {
        $newArr = array_slice($array, 0, 1);

        return !empty($newArr[0]) ? $newArr[0] : false;
    }

    /**
     * Reverse order
     *
     * @param $array
     * @return array
     */
    public static function reverse($array)
    {
        return array_reverse($array);
    }

    /**
     * Determine whether the given value is array accessible.
     *
     * @param mixed $value
     * @return bool
     */
    public static function accessible($value)
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * Collapse an array of arrays into a single array.
     *
     * @param array $array
     * @return array
     */
    public static function collapse($array)
    {
        $results = [];

        foreach ($array as $values) {
            if ($values instanceof Collection) {
                $values = $values->all();
            } elseif (!is_array($values)) {
                continue;
            }

            $results = array_merge($results, $values);
        }

        return $results;
    }

    /**
     * Divide an array into two arrays. One with keys and the other with values.
     *
     * @param array $array
     * @return array
     */
    public static function divide($array)
    {
        return [array_keys($array), array_values($array)];
    }

    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param array  $array
     * @param string $prepend
     * @return array
     */
    public static function dot($array, $prepend = '')
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $results = array_merge($results, static::dot($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @param \ArrayAccess|array $array
     * @param string|int         $key
     * @return bool
     */
    public static function exists($array, $key)
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }

    /**
     * Get one or a specified number of random values from an array.
     *
     * @param array    $array
     * @param int|null $number
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public static function random($array, $number = null)
    {
        $requested = is_null($number) ? 1 : $number;

        $count = count($array);

        if ($requested > $count) {
            throw new InvalidArgumentException(
                "You requested {$requested} items, but there are only {$count} items available."
            );
        }

        if (is_null($number)) {
            return $array[array_rand($array)];
        }

        if ((int) $number === 0) {
            return [];
        }

        $keys = array_rand($array, $number);

        $results = [];

        foreach ((array) $keys as $key) {
            $results[] = $array[$key];
        }

        return $results;
    }

    /**
     * Shuffle the given array and return the result.
     *
     * @param array    $array
     * @param int|null $seed
     * @return array
     */
    public static function shuffle($array, $seed = null)
    {
        if (is_null($seed)) {
            shuffle($array);
        } else {
            srand($seed);

            usort($array, function () {
                return rand(-1, 1);
            });
        }

        return $array;
    }

    /**
     * Sort the array using the given callback or "dot" notation.
     *
     * @param array                $array
     * @param callable|string|null $callback
     * @return array
     */
    public static function sort($array, $callback = null)
    {
        return Collection::make($array)->sort($callback)->all();
    }

    /**
     * Recursively sort an array by keys and values.
     *
     * @param array $array
     * @return array
     */
    public static function sortRecursive($array)
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = static::sortRecursive($value);
            }
        }

        if (static::isAssoc($array)) {
            ksort($array);
        } else {
            sort($array);
        }

        return $array;
    }

    /**
     * Convert the array into a query string.
     *
     * @param array $array
     * @return string
     */
    public static function query($array)
    {
        return http_build_query($array, null, '&', PHP_QUERY_RFC3986);
    }

    /**
     * If the given value is not an array and not null, wrap it in one.
     *
     * @param mixed $value
     * @return array
     */
    public static function wrap($value)
    {
        if (is_null($value)) {
            return [];
        }

        return is_array($value) ? $value : [$value];
    }
}