<?php
namespace Magepattern\Component\Tool;

class ArrayTool
{
    /**
     * @param mixed $var
     * @return \ArrayObject|\Traversable
     */
    public static function toIterator(mixed $var): \ArrayObject|\Traversable
    {
        if (!$var instanceof \Traversable && !$var instanceof \Iterator) $var = new \ArrayObject(is_array($var) ? $var : [$var]);
        return $var;
    }

    /**
     * @static
     * @param array|\Traversable $iterator
     * @param bool $recursive
     * @return array
     */
    public static function iteratorToArray(array|\Traversable $iterator, $recursive = true): array
    {
        if (!$recursive) {
            if (is_array($iterator)) return $iterator;
            return iterator_to_array($iterator);
        }

        if (method_exists($iterator, 'toArray')) return $iterator->toArray();

        $array = [];
        foreach ($iterator as $key => $value) {
            if (is_scalar($value)) {
                $array[$key] = $value;
                continue;
            }

            if ($value instanceof \Traversable) {
                $array[$key] = static::iteratorToArray($value, $recursive);
                continue;
            }

            if (is_array($value)) {
                $array[$key] = static::iteratorToArray($value, $recursive);
                continue;
            }

            $array[$key] = $value;
        }
        return $array;
    }

    /**
     * @param array $arr
     * @param array $new_arr
     * @return array
     */
    public static function replaceArray(array $arr, array $new_arr): array
    {
        if (!function_exists('array_replace')){
            foreach($new_arr as $key=>$value)
                $arr[$key]=$value;
            return $arr;
        }
        else{
            return array_replace($arr, $new_arr);
        }
    }

    /**
     * Returns the values from a single column of the input array, identified by
     * the $columnKey.
     *
     * Optionally, you may provide an $indexKey to index the values in the returned
     * array by the values from the $indexKey column in the input array.
     *
     * @param array $input A multi-dimensional array (record set) from which to pull
     *                     a column of values.
     * @param string|int $columnKey The column of values to return. This value may be the
     *                         integer key of the column you wish to retrieve, or it
     *                         may be the string key name for an associative array.
     * @param string|int $indexKey (Optional.) The column to use as the index/keys for
     *                        the returned array. This value may be the integer key
     *                        of the column, or it may be the string key name.
     * @return array
     */
    public static function array_column(array $input, string|int $columnKey, string|int $indexKey = ''): array
    {
        if (!function_exists('array_column')) {
                // Using func_get_args() in order to check for proper number of
                // parameters and trigger errors exactly as the built-in array_column()
                // does in PHP 5.5.
                $argc = func_num_args();
                $params = func_get_args();
                if ($argc < 2) {
                    trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);
                    return [];
                }
                if (!is_array($params[0])) {
                    trigger_error(
                        'array_column() expects parameter 1 to be array, ' . gettype($params[0]) . ' given',
                        E_USER_WARNING
                    );
                    return [];
                }
                if (!is_int($params[1])
                    && !is_float($params[1])
                    && !is_string($params[1])
                    && $params[1] !== null
                    && !(is_object($params[1]) && method_exists($params[1], '__toString'))
                ) {
                    trigger_error('array_column(): The column key should be either a string or an integer', E_USER_WARNING);
                    return [];
                }
                if (isset($params[2])
                    && !is_int($params[2])
                    && !is_float($params[2])
                    && !is_string($params[2])
                    && !(is_object($params[2]) && method_exists($params[2], '__toString'))
                ) {
                    trigger_error('array_column(): The index key should be either a string or an integer', E_USER_WARNING);
                    return [];
                }
                $paramsInput = $params[0];
                $paramsColumnKey = ($params[1] !== null) ? (string) $params[1] : null;
                $paramsIndexKey = null;
                if (isset($params[2])) {
                    if (is_float($params[2]) || is_int($params[2])) {
                        $paramsIndexKey = (int) $params[2];
                    }
                    else {
                        $paramsIndexKey = (string) $params[2];
                    }
                }
                $resultArray = array();
                foreach ($paramsInput as $row) {
                    $key = $value = null;
                    $keySet = $valueSet = false;
                    if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
                        $keySet = true;
                        $key = (string) $row[$paramsIndexKey];
                    }
                    if ($paramsColumnKey === null) {
                        $valueSet = true;
                        $value = $row;
                    }
                    elseif (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
                        $valueSet = true;
                        $value = $row[$paramsColumnKey];
                    }
                    if ($valueSet) {
                        if ($keySet) {
                            $resultArray[$key] = $value;
                        }
                        else {
                            $resultArray[] = $value;
                        }
                    }
                }
                return $resultArray;
        }
        else{
            $resultArray = array_column($input, $columnKey, $indexKey);
        }
        return $resultArray;
    }

    /**
     * Sort an array by values
     * @param string|int $field
     * @param array $array
     * @param string $direction
     */
    public static function array_sortBy(string|int $field, array &$array, string $direction = 'asc')
    {
        usort($array,function ($a, $b) use ($field,$direction) {
            $at = $a[$field];
            $bt = $b[$field];

            if ($at == $bt)
            {
                return 0;
            }

            if($direction === 'desc') {
                return ($at > $bt ? -1 : 1);
            }
            else {
                return ($at < $bt ? -1 : 1);
            }
        });
    }
}