<?php
namespace DavidNotBad\Support\ArraySet;

use \Closure;
use ArrayAccess;
use \stdClass;


/**
 * 操作数组
 * Class Arr
 * @package DavidNotBad\Support\ArraySet
 */
class Arr
{

    /**
     * 获取类的命名空间
     * @return string
     */
    public static function className()
    {
        return __CLASS__;
    }


    /**
     * 获取本类的一个实例
     * @return static
     */
    public static function instance()
    {
        return new static();
    }


    /**
     * 如果元素不存在，则使用“点”表示法向元素添加元素。
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     */
    public static function add($array, $key, $value)
    {
        //如果数组中没有该键, 则添加
        if (is_null(static::get($array, $key))) {
            static::set($array, $key, $value);
        }

        return $array;
    }


    /**
     * 使用“点”表示法从数组中获取项目。
     *
     * @param  \ArrayAccess|array  $array
     * @param  string|array  $key
     * @param  mixed   $default
     * @return mixed
     */
    public static function get($array, $key, $default = null)
    {
        //如果key参数为null, 返回原来的数组
        if (is_null($key)) {
            return $array;
        }

        //如果给定值不是可访问数组, 返回默认值
        if (! static::accessible($array)) {
            //如果是匿名函数, 则会调用该匿名函数并返回结果
            return static::value($default);
        }

        //当key是匿名函数
        if(is_callable($key)) {
            return array_reduce($array, function ($result, $item) use ($key) {
                return array_merge($result, (array) $key($item));
            }, array());
        }

        //当key为字符串时
        if(is_string($key)) {
            //如果数组的键存在, 则返回该键对应的值
            if (static::exists($array, $key)) {
                return $array[$key];
            }

            //如果key参数不是采用 "." 语法, 返回参数default的值
            if (strpos($key, '.') === false) {
                return isset($array[$key]) ? $array[$key] : static::value($default);
            }

            //将key拆分成数组
            $key = explode('.', $key);
        }


        //使用 "." 语法访问数组的元素
        $key = (array) $key;
        while (! is_null($segment = array_shift( $key))) {
            //如果key中包含*
            if ($segment === '*') {
                if (! is_array($array)) {
                    return static::value($default);
                }
                //获取指定的键
                $result = static::pluck($array, $key);
                //判断是否包含*
                return in_array('*', $key) ? static::collapse($result) : $result;
            }

            //如果array是个数组并且数组中存在键segment
            if (static::accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } elseif (is_object($array) && isset($array->{$segment})) {
                $array = $array->{$segment};
            } else {
                return static::value($default);
            }
        }

        return $array;
    }


    /**
     * 确定给定值是否可访问数组。
     *
     * @param  mixed  $value
     * @return bool
     */
    public static function accessible($value)
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * 返回给定值的默认值。
     *
     * @param  mixed  $value
     * @return mixed
     */
    public static function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }

    /**
     * 确定提供的数组中是否存在给定的键。
     *
     * @param  \ArrayAccess|array  $array
     * @param  string|int  $key
     * @return bool
     */
    public static function exists($array, $key)
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        if(!(is_int($key) or is_string($key))) {
            return false;
        }

        return array_key_exists($key, $array);
    }

    /**
     * 使用“点”表示法将数组项设置为给定值。
     * 如果没有为该方法提供key参数，则将替换整个数组。
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     */
    public static function set(&$array, $key, $value)
    {
        //如果key参数为null, 则替换整个数组
        if (is_null($key)) {
            return $array = $value;
        }

        //当key参数采用 "." 语法时,
        return $array = array_merge_recursive($array, static::create($key, $value));
    }

    /**
     * 使用 "." 语法创建一个数组
     *
     * @param $key
     * @param $value
     * @return mixed
     */
    public static function create($key, $value)
    {
        return array_reduce(array_reverse(explode('.', $key)), function($carry, $item){
            return array($item => $carry);
        }, $value);
    }

    /**
     * 将多维数组压缩成以 "." 分割的一维数组
     *
     * @param $array
     * @return array
     */
    public static function dot($array)
    {
        $return = array();
        $closure = function ($array, $sign='')use(&$return, &$closure){
            $sign = empty($sign) ? '' : $sign . '.';

            foreach ($array as $key=>$value)
            {
                $key = $sign . $key;
                $return = array_merge($return, is_array($value) ? $closure($value, $key) : array($key => $value));
            }
            return $return;
        };
        return $closure($array);
    }

    /**
     * 将以 "." 分割的一维数组拆分成多维数组
     *
     * @param $array
     * @return array
     */
    public static function undot($array)
    {
        $return = array();
        foreach ($array as $key=>$val)
        {
            $return = strpos($key, '.') ?
                array_merge_recursive($return, static::create($key, $val)) :
                array_merge($return, array($key => $val));
        }
        return $return;
    }

    /**
     * 纵向压缩索引数组
     *
     * @return array
     */
    public static function zip()
    {
        $arguments = func_get_args();

        if(func_num_args() == 1) {
            return call_user_func_array(array(new static(), 'zip'), current($arguments));
        }

        $count = min(array_map('count', $arguments));

        $return = array();

        for ($i=0; $i < $count; $i++)
        {
            $return[] = array_map(function($item)use($i){
                return isset($item[$i]) ? $item[$i] : null;
            }, $arguments);
        }
        return $return;
    }

    /**
     * 将数组折叠到单个数组中
     *
     * @param $array
     * @return array
     */
    public static function collapse($array)
    {
        $results = array();

        foreach ($array as $values) {
            if (! is_array($values)) {
                continue;
            }

            $results = array_merge($results, $values);
        }

        return $results;
    }

    /**
     * 将数组划分为两个数组。 一个带键，另一个带值
     *
     * @param  array  $array
     * @return array
     */
    public static function divide($array)
    {
        return array(array_keys($array), array_values($array));
    }

    /**
     * 获取除指定数组键之外的所有给定数组, 不改变原数组
     * 与之相对的only方法
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return array
     */
    public static function except($array, $keys)
    {
        static::forget($array, $keys);

        return $array;
    }

    /**
     * 使用“.”表示法从给定数组中删除一个或多个数组项, 改变原数组
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return array
     */
    public static function forget(&$array, $keys)
    {
        $original = &$array;

        $keys = (array) $keys;

        if (count($keys) === 0) {
            return $original;
        }

        foreach ($keys as $key) {
            // if the exact key exists in the top-level, remove it
            if (static::exists($array, $key)) {
                unset($array[$key]);

                continue;
            }

            $parts = explode('.', $key);

            // clean up before each pass
            $array = &$original;

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }

            unset($array[array_shift($parts)]);
        }

        return $original;
    }

    /**
     * 返回数组中的第一个元素.
     *
     * @param  array  $array
     * @param  callable|null  $callback
     * @param  mixed  $default
     * @return mixed
     */
    public static function first($array, $callback = null, $default = null)
    {
        if (is_null($callback)) {
            if (empty($array)) {
                return static::value($default);
            }

            return current($array);
        }

        foreach ($array as $key => $value) {
            if (call_user_func($callback, $value, $key)) {
                return $value;
            }
        }

        return static::value($default);
    }

    /**
     * 返回通过给定真值测试的数组中的最后一个元素。
     *
     * @param  array  $array
     * @param  callable|null  $callback
     * @param  mixed  $default
     * @return mixed
     */
    public static function last($array, $callback = null, $default = null)
    {
        if (is_null($callback)) {
            return empty($array) ? self::value($default) : end($array);
        }

        return static::first(array_reverse($array, true), $callback, $default);
    }

    /**
     * 将多维数组转化为一维数组
     *
     * @param  array $array
     * @param int $depth
     * @return array
     */
    public static function flatten($array, $depth=null)
    {
        $result = array();

        foreach ($array as $item) {
            if (! is_array($item)) {
                $result[] = $item;
            } elseif($depth == 1) {
                $result = array_merge($result, array_values($item));
            } else {
                $result = array_merge($result, static::flatten($item));
            }
        }

        return $result;
    }


    /**
     * 使用“.”检查给定数据项是否在数组中存在
     *
     * @param  \ArrayAccess|array  $array
     * @param  string|array  $keys
     * @return bool
     */
    public static function has($array, $keys)
    {
        if (is_null($keys)) {
            return false;
        }

        $keys = (array) $keys;

        if (! $array) {
            return false;
        }

        if ($keys === array()) {
            return false;
        }

        foreach ($keys as $key) {
            $subKeyArray = $array;

            if (static::exists($array, $key)) {
                continue;
            }

            foreach (explode('.', $key) as $segment) {
                if (static::accessible($subKeyArray) && static::exists($subKeyArray, $segment)) {
                    $subKeyArray = $subKeyArray[$segment];
                } else {
                    return false;
                }
            }
        }

        return true;
    }


    /**
     * 从给定数组中获取项目的子集。
     * 与之相对的是except方法
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return array
     */
    public static function only($array, $keys)
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }

    /**
     * 从数组中获取值的数组。
     *
     * @param  array  $array
     * @param  string|array  $value
     * @param  string|array|null  $key
     * @return array
     */
    public static function pluck($array, $value, $key = null)
    {
        $results = array();

        foreach ($array as $item) {
            $itemValue = static::get($item, $value);

            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey = static::get($item, $key);

                if (is_object($itemKey) && method_exists($itemKey, '__toString')) {
                    $itemKey = (string) $itemKey;
                }

                $results[$itemKey] = $itemValue;
            }
        }

        return $results;
    }


    /**
     * 将数据项推入数组开头
     *
     * @param  array  $array
     * @param  mixed  $value
     * @param  mixed  $key
     * @return array
     */
    public static function prepend($array, $value, $key = null)
    {
        if (is_null($key)) {
            array_unshift($array, $value);
        } else {
            $array = array($key => $value) + $array;
        }

        return $array;
    }

    /**
     * 从数组中返回并移除键值对
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public static function pull(&$array, $key, $default = null)
    {
        $value = static::get($array, $key, $default);

        static::forget($array, $key);

        return $value;
    }

    /**
     * 从数组中返回随机值
     *
     * @param  array $array
     * @param  int|null $number
     * @return mixed
     */
    public static function random($array, $number = null)
    {
        $count = count($array);
        $number = ($number > $count) ? $count : $number;

        if (is_null($number)) {
            return $array[array_rand($array)];
        }

        if ((int) $number === 0) {
            return array();
        }

        $keys = array_rand($array, $number);

        $results = array();

        foreach ((array) $keys as $key) {
            $results[] = $array[$key];
        }

        return $results;
    }


    /**
     * 给数组排序
     *
     * @param $array
     * @param null $callback
     * @return array
     */
    public static function sort($array, $callback = null)
    {
        $callback ? uasort($array, $callback) : asort($array);

        return $array;
    }


    /**
     * 通过值对数组进行排序
     *
     * @param $array
     * @param  callable|string $callback
     * @param  int $options
     * @param  bool $descending
     * @return array
     */
    public static function sortBy($array, $callback=null, $options = SORT_REGULAR, $descending = false)
    {
        $results = array();

        if(is_callable($callback)) {
            foreach ($array as $key => $value) {
                $results[$key] = $callback($value, $key);
            }
        } else {
            foreach ($array as $key => $value) {
                $results[$key] = static::get($value, $callback);
            }
        }

        $descending ? arsort($results, $options) : asort($results, $options);

        foreach (array_keys($results) as $key) {
            $results[$key] = $array[$key];
        }

        return $results;
    }

    /**
     * 通过值对数组进行倒序排序
     *
     * @param $array
     * @param  callable|string $callback
     * @param  int $options
     * @return array
     */
    public static function sortByDesc($array, $callback, $options = SORT_REGULAR)
    {
        return static::sortBy($array, $callback, $options, true);
    }

    /**
     * 对数组的键进行排序
     *
     * @param $array
     * @param  int $options
     * @param  bool $descending
     * @return array
     */
    public static function sortKeys($array, $options = SORT_REGULAR, $descending = false)
    {
        $descending ? krsort($array, $options) : ksort($array, $options);

        return $array;
    }

    /**
     * 对数组的键进行倒序排序
     *
     * @param $array
     * @param  int $options
     * @return array
     */
    public static function sortKeysDesc($array, $options = SORT_REGULAR)
    {
        return static::sortKeys($array, $options, true);
    }


    /**
     * 过滤数组或对象
     * where相对的方法是 reject
     *
     * @param $array
     * @param callable|null $callback 回调函数
     * @return array
     */
    public static function filter($array, $callback = null)
    {
        //php版本大于5.6.0
        if(defined('ARRAY_FILTER_USE_BOTH') && !is_null($callback)) {
            return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
        }

        $callback = static::useAsCallable($callback) ? $callback : function($item) {
            return !! $item;
        };

        $result = array();
        foreach ($array as $key=>$item) {
            if( call_user_func($callback, $item, $key) ){
                $result[$key] = $item;
            }
        }

        return $result;
    }


    /**
     * 比较数组或对象
     *
     * @param $array
     * @param $key
     * @param $operator
     * @param null $value
     * @return array
     */
    public static function where($array, $key, $operator, $value = null)
    {
        $operator = static::operater(get_called_class(), func_get_args());
        return static::filter($array, $operator);
    }

    /**
     * 严格比较数组或对象
     *
     * @param $array
     * @param $key
     * @param $value
     * @return array
     */
    public static function whereStrict($array, $key, $value)
    {
        return static::where($array, $key, '===', $value);
    }


    /**
     * 获取条件操作的回调函数
     *
     * @param $class
     * @param $arguments
     * @return mixed
     */
    protected static function operater($class, $arguments)
    {
        return call_user_func_array(array($class, 'operatorForWhere'), array_slice($arguments, 1));
    }

    /**
     * 遍历并操作数组
     *
     * @param array $array
     * @param callable $callback
     * @return array
     */
    public static function map($array, $callback)
    {
        $keys = array_keys($array);
        $items = array_map($callback, $array, $keys);

        return array_combine($keys, $items);
    }


    /**
     * 使用 sort 函数对数组进行递归排序
     *
     * @param  array  $array
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
     * 判断数组是否是关联的
     *
     * 如果数组没有以零开头的连续数字键，则该数组是“关联的”
     *
     * @param  array  $array
     * @return bool
     */
    public static function isAssoc(array $array)
    {
        $keys = array_keys($array);

        return array_keys($keys) !== $keys;
    }

    /**
     * 函数将给定值包裹到数组中，如果给定值已经是数组则保持不变
     *
     * @param  mixed  $value
     * @return array
     */
    public static function wrap($value)
    {
        if (is_null($value)) {
            return array();
        }

        return ! is_array($value) ? array($value) : $value;
    }

    /**
     * 返回集合中所有数据项的和
     *
     * @param $array
     * @param  callable|string|null $callback
     * @return mixed
     */
    public static function sum($array, $callback = null)
    {
        if (is_null($callback)) {
            return array_sum($array);
        }

        if(is_string($callback) && strpos($callback, '*') !== false) {
            return static::sum(static::get($array, $callback));
        }

        $callback = static::valueRetriever($callback);

        return array_reduce($array, function ($result, $item) use ($callback) {
            return $result + $callback($item);
        }, 0);
    }

    /**
     * 求出数组的平均值
     *
     * @param $array
     * @param null|callable $callback
     * @param bool $prefix
     * @return bool|float|int
     */
    public static function avg($array, $callback = null, $prefix = true)
    {
        $prefix && is_string($callback) && $callback = '*.' . $callback;
        if ($count = count(static::get($array, $callback, array()))) {
            return static::sum($array, $callback) / $count;
        }
        return false;
    }


    /**
     * avg别名
     *
     * @param $array
     * @param null $callback
     * @return bool|float|int
     */
    public static function average($array, $callback = null)
    {
        return static::avg($array, $callback);
    }


    /**
     * 将数组分割成相同个数的小数组
     *
     * @param $array
     * @param  int $size
     * @return array|Arr
     */
    public static function chunk($array, $size)
    {
        if ($size <= 0) {
            return array();
        }

        $chunks = array();

        foreach (array_chunk($array, $size, true) as $chunk) {
            $chunks[] = $chunk;
        }

        return $chunks;
    }

    /**
     * 合并数组
     *
     * @param $keys
     * @param $values
     * @return array
     */
    public static function combine($keys, $values)
    {
        if(func_num_args() == 2) {
            return array_combine($keys, $values);
        }

        $arguments = array_slice(func_get_args(), 1);
        return array_combine($keys, static::zip($arguments));
    }


    /**
     * 追加给定数据到数组末尾
     *
     * @param $array
     * @param array|string $source
     * @return $this
     */
    public static function concat($array, $source)
    {
        foreach ((array) $source as $item) {
            array_push($array, $item);
        }

        return $array;
    }


    /**
     * 确定集合中是否存在项目。
     *
     * @param $array
     * @param  mixed $key
     * @param  mixed $operator
     * @param  mixed $value
     * @return bool
     */
    public static function contains($array, $key, $operator = null, $value = null)
    {
        if(func_num_args() == 2) {
            if (static::useAsCallable($key)) {
                $placeholder = new stdClass;

                return static::first($array, $key, $placeholder) !== $placeholder;
            }

            return in_array($key, $array);
        }

        $operator = static::operater(get_called_class(), func_get_args());
        return static::contains($array, $operator);
    }


    /**
     * 获得一个验证的回调函数
     *
     * @param  string  $key
     * @param  string  $operator
     * @param  mixed  $value
     * @return \Closure
     */
    protected static function operatorForWhere($key, $operator=null, $value = null)
    {
        if (func_num_args() == 2) {
            $value = $operator;

            $operator = '=';
        }


        /** @var Arr $class */
        $class = get_called_class();
        return function ($item) use ($key, $operator, $value, $class) {
            $retrieved = $class::get($item, $key);

            $strings = array_filter(array($retrieved, $value), function ($value) {
                return is_string($value) || (is_object($value) && method_exists($value, '__toString'));
            });

            if (count($strings) < 2 && count(array_filter(array($retrieved, $value), 'is_object')) == 1) {

                return in_array($operator, array('!=', '<>', '!=='));
            }


            switch ($operator) {
                default:
                case '=':
                case '==':  return $retrieved == $value;
                case '!=':
                case '<>':  return $retrieved != $value;
                case '<':   return $retrieved < $value;
                case '>':   return $retrieved > $value;
                case '<=':  return $retrieved <= $value;
                case '>=':  return $retrieved >= $value;
                case '===': return $retrieved === $value;
                case '!==': return $retrieved !== $value;
            }
        };
    }


    /**
     * 判断集合是否包含一个给定项
     *
     * @param $array
     * @param  mixed $key
     * @param  mixed $value
     * @return bool
     */
    public static function containsStrict($array, $key, $value = null)
    {
        if (func_num_args() == 3) {
            /**
             * @var $class Arr
             */
            $class = get_called_class();
            return static::contains($array, function ($item) use ($key, $value, $class) {
                return $class::get($item, $key) === $value;
            });
        }

        if (static::useAsCallable($key)) {
            return ! is_null(static::first($array, $key));
        }

        return in_array($key, $array, true);
    }


    /**
     * 在给定数组或集合之间交叉组合集合值，然后返回所有可能排列组合的笛卡尔积
     *
     * @return array
     */
    public static function crossJoin()
    {
        $arrays = func_get_args();
        $results = array(array());

        foreach ($arrays as $index => $array) {
            $append = array();

            foreach ($results as $product) {
                foreach ($array as $item) {
                    $product[$index] = $item;

                    $append[] = $product;
                }
            }

            $results = $append;
        }

        return $results;
    }


    /**
     * 迭代集合中的数据项并传递每个数据项到给定回调
     * 当回调函数遇到false时, 停止迭代
     *
     * @param $array
     * @param callable $callback
     */
    public static function each($array, $callback)
    {
        foreach ($array as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }
    }


    /**
     * 迭代集合项，传递每个嵌套数据项值到给定集合
     *
     * @param $array
     * @param $callback
     */
    public static function eachSpread($array, $callback)
    {
        static::each($array, function ($chunk, $key) use ($callback) {
            $chunk[] = $key;
            return call_user_func_array($callback, $chunk);
        });
    }


    /**
     * 验证集合的所有元素能够通过给定的真理测试
     *
     * @param $array
     * @param $key
     * @param null $operator
     * @param null $value
     * @return bool
     */
    public static function every($array, $key, $operator = null, $value = null)
    {
        if (func_num_args() == 2) {
            $callback = static::valueRetriever($key);

            foreach ($array as $k => $v) {
                if (! $callback($v, $k)) {
                    return false;
                }
            }

            return true;
        }


        $operator = static::operater(get_called_class(), func_get_args());
        return static::every($array, $operator);
    }


    /**
     * 使用给定回调过滤集合，该回调应该为所有它想要从结果集合中移除的数据项返回 true
     * 和 reject 方法相对的方法是 filter/where 方法
     *
     * @param $array
     * @param $callback
     * @return array
     */
    public static function reject($array, $callback=true)
    {
        if (static::useAsCallable($callback)) {
            return static::filter($array, function ($value, $key) use ($callback) {
                /** @var callable $callback */
                return ! $callback($value, $key);
            });
        }

        return static::filter($array, function ($item) use ($callback) {
            return $item != $callback;
        });
    }


    /**
     * 判断给定值是否是回调函数
     *
     * @param $value
     * @return bool
     */
    protected static function useAsCallable($value)
    {
        return ! is_string($value) && is_callable($value);
    }


    /**
     * 返回集合中的第一个元素，包含键值对
     *
     * @param $array
     * @param $key
     * @param $operator
     * @param null $value
     * @return mixed
     */
    public static function firstWhere($array, $key, $operator, $value = null)
    {
        $operator = static::operater(get_called_class(), func_get_args());
        return static::first($array, $operator);
    }


    /**
     * 迭代集合并传递每个值到给定回调，该回调可以自由编辑数据项并将其返回，最后形成一个经过编辑的新集合。
     * 然后，这个数组在层级维度被扁平化
     *
     * @param $array
     * @param callable $callback
     * @return array
     */
    public static function flatMap($array, $callback)
    {
        return static::collapse(static::map($array, $callback));
    }


    /**
     * 返回新的包含给定页数数据项的集合
     *
     * @param $array
     * @param $page
     * @param $perPage
     * @return array
     */
    public static function forPage($array, $page, $perPage)
    {
        $offset = max(0, ($page - 1) * $perPage);

        return array_slice($array, $offset, $perPage, true);
    }


    /**
     * 给指定的数据分组
     *
     * @param $array
     * @param $groupBy
     * @param bool $preserveKeys
     * @return array
     */
    public static function groupBy($array, $groupBy, $preserveKeys = false)
    {
        if (is_array($groupBy)) {
            $nextGroups = $groupBy;

            $groupBy = array_shift($nextGroups);
        }

        $groupBy = static::valueRetriever($groupBy);

        $results = array();

        foreach ($array as $key => $value) {
            $groupKeys = $groupBy($value, $key);

            if (! is_array($groupKeys)) {
                $groupKeys = (array) $groupKeys;
            }

            foreach ($groupKeys as $groupKey) {
                $groupKey = is_bool($groupKey) ? (int) $groupKey : $groupKey;

                if (! array_key_exists($groupKey, $results)) {
                    $results[$groupKey] = array();
                }

                if(! $preserveKeys) {
                    $results[$groupKey][] = $value;
                } else {
                    $results[$groupKey][$key] = $value;
                }
            }
        }

        if (! empty($nextGroups)) {
            /** @var Arr $class */
            $class = get_called_class();
            return $class::map($results, function($value)use($nextGroups, $preserveKeys, $class) {
                return $class::groupBy($value, $nextGroups, $preserveKeys);
            });
        }

        return $results;
    }


    /**
     * 获取值检索回调
     *
     * @param $value
     * @return Closure
     */
    protected static function valueRetriever($value)
    {
        if (static::useAsCallable($value)) {
            return $value;
        }

        /** @var Arr $class */
        $class = get_called_class();
        return function ($item) use ($value, $class) {
            return $class::get($item, $value);
        };
    }


    /**
     * 连接集合中的数据项。
     *
     * @param $array
     * @param $value
     * @param null $glue
     * @return string
     */
    public static function implode($array, $value, $glue = null)
    {
        $first = static::first($array);

        if (is_array($first) || is_object($first)) {
            return implode($glue, static::pluck($array, $value));
        }

        return implode($value, $array);
    }


    /**
     * 将指定键的值作为集合的键
     * 如果多个数据项拥有同一个键，只有最后一个会出现在新集合里面
     *
     * @param $array
     * @param $keyBy
     * @return array
     */
    public static function keyBy($array, $keyBy)
    {
        $keyBy = static::valueRetriever($keyBy);

        $results = array();

        foreach ($array as $key => $item) {
            $resolvedKey = $keyBy($item, $key);

            if (is_object($resolvedKey)) {
                $resolvedKey = (string) $resolvedKey;
            }

            $results[$resolvedKey] = $item;
        }

        return $results;
    }


    /**
     * 迭代集合，通过传递值到构造器来为给定类创建新的实例
     *
     * @param $array
     * @param $class
     * @return array
     */
    public static function mapInto($array, $class)
    {
        return static::map($array, function ($value, $key) use ($class) {
            return new $class($value, $key);
        });
    }


    /**
     * 迭代集合项，传递每个嵌套集合项值到给定回调。
     * 在回调中我们可以修改集合项并将其返回，从而通过修改的值组合成一个新的集合
     *
     * @param $array
     * @param $callback
     * @return array
     */
    public static function mapSpread($array, $callback)
    {
        return static::map($array, function ($chunk, $key) use ($callback) {
            $chunk[] = $key;

            return call_user_func_array($callback, $chunk);
        });
    }


    /**
     * 通过给定回调对集合项进行分组，回调会返回包含单个键值对的关联数组，从而将分组后的值组合成一个新的集合
     *
     * @param $array
     * @param $callback
     * @return array
     */
    public static function mapToGroups($array, $callback)
    {
        $dictionary = array();

        foreach ($array as $key => $item) {
            $pair = $callback($item, $key);

            $key = key($pair);

            $value = reset($pair);

            if (! isset($dictionary[$key])) {
                $dictionary[$key] = array();
            }

            $dictionary[$key][] = $value;
        }

        return $dictionary;
    }


    /**
     * mapToGroups 的别名
     *
     * @param $array
     * @param $callback
     * @return array
     */
    public static function mapToDictionary($array, $callback)
    {
        return static::mapToGroups($array, $callback);
    }


    /**
     * 对集合进行迭代并传递每个值到给定回调，该回调会返回包含键值对的关联数组
     *
     * @param $array
     * @param callable $callback
     * @return array
     */
    public static function mapWithKeys($array, $callback)
    {
        $result = array();

        foreach ($array as $key => $value) {
            $assoc = $callback($value, $key);

            foreach ($assoc as $mapKey => $mapValue) {
                $result[$mapKey] = $mapValue;
            }
        }

        return $result;
    }


    /**
     * 求出最大值
     *
     * @param $array
     * @param null $callback
     * @return mixed
     */
    public static function max($array, $callback = null)
    {
        if(is_null($callback)) {
            return max($array);
        }

        $callback = static::valueRetriever($callback);

        $array = static::filter($array, function ($value) {
            return ! is_null($value);
        });

        return array_reduce($array, function ($result, $item) use ($callback) {
            $value = $callback($item);

            return is_null($result) || $value > $result ? $value : $result;
        });
    }


    /**
     * 返回给定键的中位数
     *
     * @param $array
     * @param null $key
     * @return bool|float|int|mixed|null
     */
    public static function median($array, $key = null)
    {
        $count = count($array);

        if ($count == 0) {
            return null;
        }

        $data = is_null($key) ? $array : static::pluck($array, $key);
        $values = array_values(static::sort($data));

        $middle = (int) ($count / 2);

        if ($count % 2) {
            return static::get($values, $middle);
        }

        $range = array(
            static::get($values, $middle - 1),
            static::get($values, $middle)
            );
        return static::avg($range);
    }


    /**
     * 返回集合中给定键的最小值
     *
     * @param $array
     * @param null $callback
     * @return mixed
     */
    public static function min($array, $callback = null)
    {
        if(is_null($callback)) {
            return min($array);
        }

        $callback = static::valueRetriever($callback);

        $data = static::filter($array, function ($value) {
            return ! is_null($value);
        });
        return array_reduce($data, function ($result, $item) use ($callback) {
            $value = $callback($item);
            return is_null($result) || $value < $result ? $value : $result;
        });
    }


    /**
     * 返回给定键的众数
     *
     * @param $array
     * @param null $key
     * @return array|null
     */
    public static function mode($array, $key = null)
    {
        $count = count($array);

        if ($count == 0) {
            return null;
        }

        $collection = is_null($key) ? $array : static::pluck($array, $key);

        $counts = array();
        static::each($collection, function ($value) use (&$counts) {
            $counts[$value] = isset($counts[$value]) ? $counts[$value] + 1 : 1;
        });

        $sorted = static::sort($counts);

        $highestValue = static::last($sorted);

        $filter = static::filter($sorted, function ($value) use ($highestValue) {
            return $value == $highestValue;
        });

        return array_keys(static::sort($filter));
    }


    /**
     * 组合集合中第 n-th 个元素创建一个新的集合
     *
     * @param $array
     * @param $step
     * @param int $offset
     * @return array
     */
    public static function nth($array, $step, $offset = 0)
    {
        $new = array();

        $position = 0;

        foreach ($array as $key=>$item) {
            if ($position % $step === $offset) {
                $new[$key] = $item;
            }

            $position++;
        }

        return $new;
    }


    /**
     * 和 PHP 函数 list 一起使用，从而将通过真理测试和没通过的分割开来
     *
     * @param $array
     * @param $key
     * @param null $operator
     * @param null $value
     * @return array
     */
    public static function partition($array, $key, $operator = null, $value = null)
    {
        $partitions = array(array(), array());

        $callback = func_num_args() == 2
            ? static::valueRetriever($key)
            : static::operater(get_called_class(), func_get_args());

        foreach ($array as $key => $item) {
            $partitions[(int) ! $callback($item, $key)][$key] = $item;
        }

        return $partitions;
    }


    /**
     * 使用回调函数处理数组并返回结果
     *
     * @param $array
     * @param $callback
     * @return mixed
     */
    public static function pipe($array, $callback)
    {
        return $callback($array);
    }


    /**
     * 使用回调函数处理数组并返回结果
     *
     * @param $array
     * @param $callback
     * @return mixed
     */
    public static function tap($array, $callback)
    {
        return $callback($array);
    }


    /**
     * 通过给定数值对集合进行分组
     *
     * @param $array
     * @param $numberOfGroups
     * @return array|Arr
     */
    public static function split($array, $numberOfGroups)
    {
        if (empty($array)) {
            return array();
        }

        $groupSize = ceil(count($array) / $numberOfGroups);

        return static::chunk($array, $groupSize);
    }


    /**
     * 通过调用指定次数的回调创建一个新的集合
     *
     * @param $number
     * @param null $callback
     * @return array
     */
    public static function times($number, $callback = null)
    {
        if ($number < 1) {
            return array();
        }

        if (is_null($callback)) {
            return range(1, $number);
        }

        return static::map(range(1, $number), $callback);
    }


    /**
     * 集合中所有的唯一数据项， 返回的集合保持原来的数组键
     *
     * @param $array
     * @param $items
     * @return mixed
     */
    public static function union($array, $items)
    {
        return $array + $items;
    }


    /**
     * 返回集合中所有的唯一数据项， 返回的集合保持原来的数组键
     *
     * @param $array
     * @param null $key
     * @param bool $strict
     * @return array
     */
    public static function unique($array, $key = null, $strict = false)
    {
        $callback = static::valueRetriever($key);

        $exists = array();

        return static::reject($array, function ($item, $key) use ($callback, $strict, &$exists) {
            if (in_array($id = $callback($item, $key), $exists, $strict)) {
                return true;
            }

            $exists[] = $id;
            return null;
        });
    }


    /**
     * 返回集合中所有的唯一数据项, 进行严格比较
     *
     * @param $array
     * @param null $key
     * @return array
     */
    public static function uniqueStrict($array, $key = null)
    {
        return static::unique($array, $key, true);
    }


    /**
     * 通过包含在给定数组中的键值对集合进行过滤
     *
     * @param $array
     * @param $key
     * @param $values
     * @param bool $strict
     * @return array
     */
    public static function whereIn($array, $key, $values, $strict = false)
    {
        /** @var Arr $class */
        $class = get_called_class();
        return static::filter($array, function ($item) use ($key, $values, $strict, $class) {
            return in_array($class::get($item, $key), $values, $strict);
        });
    }


    /**
     * 该方法和 whereIn 方法签名相同，不同之处在于 whereInStrict 在比较值的时候使用「严格」比较。
     *
     * @param $array
     * @param $key
     * @param $values
     * @return array
     */
    public static function whereInStrict($array, $key, $values)
    {
        return static::whereIn($array, $key, $values, true);
    }


    /**
     * 通过给定类的类型过滤集合
     *
     * @param $array
     * @param $type
     * @return array
     */
    public static function whereInstanceOf($array, $type)
    {
        return static::filter($array, function ($value) use ($type) {
            return $value instanceof $type;
        });
    }


    /**
     * 通过给定键值过滤不在给定数组中的集合数据项
     *
     * @param $array
     * @param $key
     * @param $values
     * @param bool $strict
     * @return array
     */
    public static function whereNotIn($array, $key, $values, $strict = false)
    {
        /** @var Arr $class */
        $class = get_called_class();
        return static::reject($array, function ($item) use ($key, $values, $strict, $class) {
            return in_array($class::get($item, $key), $values, $strict);
        });
    }


    /**
     * 该方法和 whereNotIn 方法签名一样，不同之处在于所有值都使用「严格」比较。
     *
     * @param $array
     * @param $key
     * @param $values
     * @return array
     */
    public static function whereNotInStrict($array, $key, $values)
    {
        return static::whereNotIn($array, $key, $values, true);
    }


    /**
     * 为给定值查询集合，如果找到的话返回对应的键，如果没找到，则返回 false
     *
     * @param $array
     * @param  mixed $value
     * @param  bool $strict
     * @return mixed
     */
    public static function search($array, $value, $strict = false)
    {
        if (! static::useAsCallable($value)) {
            return array_search($value, $array, $strict);
        }

        foreach ($array as $key => $item) {
            if (call_user_func($value, $item, $key)) {
                return $key;
            }
        }

        return false;
    }


    /**
     * 随机打乱集合中的数据项
     *
     * @param  array  $array
     * @param  int|null  $seed
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
     * 从给定位置开始移除并返回数据项切片
     *
     * @param $array
     * @param  int $offset
     * @param  int|null $length
     * @param  mixed $replacement
     * @return static
     */
    public static function splice($array, $offset, $length = null, $replacement = array())
    {
        if (func_num_args() === 2) {
            return array_splice($array, $offset);
        }

        return array_splice($array, $offset, $length, $replacement);
    }
















}

