<?php
namespace DavidNotBad\Support\ArraySet;

use \ArrayAccess;
use \Countable;
use DavidNotBad\Support\Build;
use DavidNotBad\Support\Contracts\Arrayable;
use DavidNotBad\Support\Contracts\Jsonable;
use \IteratorAggregate;
use \Traversable;



/**
 * Class Collection
 * @package DavidNotBad\Support\ArraySet
 *
 * @see \DavidNotBad\Support\ArraySet\Arr
 */
class Collection implements ArrayAccess, Arrayable, Countable, IteratorAggregate, Jsonable
{
    /**
     * @var array
     */
    protected $items = array();

    /**
     * 添加一个新的集合
     *
     * @param  mixed  $items
     * @return void
     */
    public function __construct($items = array())
    {
        $this->items = $this->getArrayableItems($items);
    }

    /**
     * 创建一个新的集合
     *
     * @param  mixed  $items
     * @return static
     */
    public static function make($items = array())
    {
        return new static($items);
    }


    /**
     * 函数将给定值包裹到集合
     *
     * @param  mixed  $value
     * @return static
     */
    public static function wrap($value)
    {
        return $value instanceof self
            ? new static($value)
            : new static(Arr::wrap($value));
    }

    /**
     * unwrap 方法会从给定值中返回集合项
     *
     * @param  array|static|mixed  $value
     * @return array
     */
    public static function unwrap($value)
    {
        return $value instanceof self ? $value->all() : $value;
    }


    /**
     * 通过静态 times() 方法可以通过调用指定次数的回调创建一个新的集合
     *
     * @param $number
     * @param callable|null $callback
     * @return static
     */
    public static function times($number, $callback = null)
    {
        return new static(Arr::times($number, $callback));
    }

    /**
     * 获取集合的所有元素
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }


    /**
     * 求出数组的平均值
     *
     * @param null $callback
     * @param bool $prefix
     * @return bool|float|int
     */
    public function avg($callback = null, $prefix = true)
    {
        return Arr::avg($this->items, $callback, $prefix);
    }

    /**
     * 求出数组的平均值
     * avg的别名
     *
     * @param null $callback
     * @param bool $prefix
     * @return bool|float|int
     */
    public function average($callback = null, $prefix = true)
    {
        return $this->avg($callback, $prefix);
    }


    /**
     * 返回给定键的中位数
     *
     * @param null $key
     * @return bool|float|int|mixed|null
     */
    public function median($key = null)
    {
        return Arr::median($this->items, $key);
    }


    /**
     * 返回给定键的众数
     *
     * @param null $key
     * @return array|null
     */
    public function mode($key = null)
    {
        return Arr::mode($this->items, $key);
    }

    /**
     * 将数组折叠到单个数组中
     *
     * @return static
     */
    public function collapse()
    {
        return new static(Arr::collapse($this->items));
    }


    /**
        return $this->route(__FUNCTION__, func_get_args());
     * 判断集合是否包含一个给定项
     *
     * @param $key
     * @param null $operator
     * @param null $value
     * @return bool
     */
    public function contains($key, $operator = null, $value = null)
    {
        return $this->route(__FUNCTION__, func_get_args());
    }



    /**
     * 判断集合是否包含一个给定项
     *
     * @param $key
     * @param null $value
     * @return bool
     */
    public function containsStrict($key, $value = null)
    {
        return $this->route(__FUNCTION__, func_get_args());
    }


    /**
     * 在给定数组或集合之间交叉组合集合值，然后返回所有可能排列组合的笛卡尔积
     *
     * @return static
     */
    public function crossJoin()
    {
        $arguments = array_map(array($this, 'getArrayableItems'), func_get_args());

        $items = $this->route(__FUNCTION__, $arguments);
        return new static($items);
    }


    /**
     * 获取集合中不存在于给定项目中的项目
     *
     * @param $items
     * @return static
     */
    public function diff($items)
    {
        return new static(array_diff($this->items, $this->getArrayableItems($items)));
    }


    /**
     * 获取集合中不存在于给定项目中的项目
     *
     * @param $items
     * @param callable $callback
     * @return static
     */
    public function diffUsing($items, $callback)
    {
        return new static(array_udiff($this->items, $this->getArrayableItems($items), $callback));
    }


    /**
     * 获取集合中的项目，其中的键和值不在给定项目中
     *
     * @param $items
     * @return static
     */
    public function diffAssoc($items)
    {
        return new static(array_diff_assoc($this->items, $this->getArrayableItems($items)));
    }


    /**
     * 获取集合中的项目，其中的键和值不在给定项目中
     *
     * @param $items
     * @param callable $callback
     * @return static
     */
    public function diffAssocUsing($items, $callback)
    {
        return new static(array_diff_uassoc($this->items, $this->getArrayableItems($items), $callback));
    }


    /**
     * 获取集合中的项目，其中的键不在给定项目中
     *
     * @param $items
     * @return static
     */
    public function diffKeys($items)
    {
        return new static(array_diff_key($this->items, $this->getArrayableItems($items)));
    }


    /**
     * 获取集合中的项目，其中的键不在给定项目中
     *
     * @param $items
     * @param callable $callback
     * @return static
     */
    public function diffKeysUsing($items, $callback)
    {
        return new static(array_diff_ukey($this->items, $this->getArrayableItems($items), $callback));
    }


    /**
     * 对每个项目执行回调
     *
     * @param $callback
     * @return $this
     */
    public function each($callback)
    {
        Arr::each($this->items, $callback);
        return $this;
    }

    /**
     * 迭代集合项，传递每个嵌套数据项值到给定集合
     *
     * @param callable $callback
     * @return $this
     */
    public function eachSpread($callback)
    {
        Arr::eachSpread($this->items, $callback);
        return $this;
    }


    /**
     * 验证集合的所有元素能够通过给定的真理测试
     *
     * @param $key
     * @param null $operator
     * @param null $value
     * @return bool
     */
    public function every($key, $operator = null, $value = null)
    {
        return $this->route(__FUNCTION__, func_get_args());
    }


    /**
     * 获取除指定数组键之外的所有给定数组, 不改变原数组
     * 与之相对的only方法
     *
     * @param $keys
     * @return static
     */
    public function except($keys)
    {
        $items = Arr::except($this->items, $keys);
        return new static($items);
    }


    /**
     * 过滤数组或对象
     * where相对的方法是 reject
     *
     * @param null $callback
     * @return static
     */
    public function filter($callback = null)
    {
        $items = Arr::filter($this->items, $callback);
        return new static($items);
    }


    /**
     * 在传入的第一个参数执行结果为 true 时执行给定回调
     *
     * @param $value
     * @param callable $callback
     * @param callable|null $default
     * @return $this
     */
    public function when($value, $callback, $default = null)
    {
        if ($value) {
            return $callback($this, $value);
        } elseif ($default) {
            return $default($this, $value);
        }

        return $this;
    }


    /**
     * 执行给定回调，除非传递到该方法的第一个参数等于 true
     *
     * @param $value
     * @param callable $callback
     * @param callable|null $default
     * @return Collection
     */
    public function unless($value, $callback, $default = null)
    {
        return $this->when(! $value, $callback, $default);
    }


    /**
     * 通过给定键值对过滤集合
     *
     * @param $key
     * @param $operator
     * @param null $value
     * @return static
     */
    public function where($key, $operator, $value = null)
    {
        $items = $this->route(__FUNCTION__, func_get_args());
        return new static($items);
    }


    /**
     * 严格比较数组或对象
     *
     * @param $key
     * @param $value
     * @return static
     */
    public function whereStrict($key, $value)
    {
        $items = Arr::whereStrict($this->items, $key, $value);
        return new static($items);
    }


    /**
     * 通过包含在给定数组中的键值对集合进行过滤
     *
     * @param  string  $key
     * @param  mixed  $values
     * @param  bool  $strict
     * @return static
     */
    public function whereIn($key, $values, $strict = false)
    {
        $items = Arr::whereIn($this->items, $key, $values, $strict);
        return new static($items);
    }


    /**
     * 该方法和 whereIn 方法签名相同，不同之处在于 whereInStrict 在比较值的时候使用「严格」比较。
     *
     * @param  string  $key
     * @param  mixed  $values
     * @return static
     */
    public function whereInStrict($key, $values)
    {
        $items = Arr::whereInStrict($this->items, $key, $values);
        return new static($items);
    }


    /**
     * 通过给定键值过滤不在给定数组中的集合数据项
     *
     * @param  string  $key
     * @param  mixed  $values
     * @param  bool  $strict
     * @return static
     */
    public function whereNotIn($key, $values, $strict = false)
    {
        $items = Arr::whereNotIn($this->items, $key, $values, $strict);
        return new static($items);
    }

    /**
     * 该方法和 whereNotIn 方法签名一样，不同之处在于所有值都使用「严格」比较。
     *
     * @param  string  $key
     * @param  mixed  $values
     * @return static
     */
    public function whereNotInStrict($key, $values)
    {
        $items = Arr::whereNotInStrict($this->items, $key, $values);
        return new static($items);
    }

    /**
     * 通过给定类的类型过滤集合
     *
     * @param  string  $type
     * @return static
     */
    public function whereInstanceOf($type)
    {
        $items = Arr::whereInstanceOf($this->items, $type);
        return new static($items);
    }


    /**
     * 返回数组中的第一个元素.
     *
     * @param  callable|null  $callback
     * @param  mixed  $default
     * @return mixed
     */
    public function first($callback = null, $default = null)
    {
        return Arr::first($this->items, $callback, $default);
    }


    /**
     * 返回集合中的第一个元素，包含键值对
     *
     * @param  string  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return static
     */
    public function firstWhere($key, $operator, $value = null)
    {
        return Arr::firstWhere($this->items, $key, $operator, $value);
    }

    /**
     * 将多维数组转化为一维数组
     *
     * @param  int  $depth
     * @return static
     */
    public function flatten($depth = null)
    {
        $items = Arr::flatten($this->items, $depth);
        return new static($items);
    }

    /**
     * 翻转集合中的项目。
     *
     * @return static
     */
    public function flip()
    {
        return new static(array_flip($this->items));
    }

    /**
     * 使用“.”表示法从给定数组中删除一个或多个数组项, 改变原数组
     *
     * @param  string|array  $keys
     * @return $this
     */
    public function forget($keys)
    {
        $this->items = Arr::forget($this->items, $keys);
        return $this;
    }



    /**
     * 使用“点”表示法从数组中获取项目。
     *
     * @param  mixed  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return Arr::get($this->items, $key, $default);
    }

    /**
     * 给指定的数据分组
     *
     * @param  callable|string  $groupBy
     * @param  bool  $preserveKeys
     * @return static
     */
    public function groupBy($groupBy, $preserveKeys = false)
    {
        $items = Arr::groupBy($this->items, $groupBy, $preserveKeys);
        return new static($items);
    }


    /**
     * 将指定键的值作为集合的键
     * 如果多个数据项拥有同一个键，只有最后一个会出现在新集合里面
     *
     * @param  callable|string  $keyBy
     * @return static
     */
    public function keyBy($keyBy)
    {
        $items = Arr::keyBy($this->items, $keyBy);
        return new static($items);
    }


    /**
     * 使用“.”检查给定数据项是否在数组中存在
     *
     * @param  mixed  $key
     * @return bool
     */
    public function has($key)
    {
        return Arr::has($this->items, $key);
    }


    /**
     * 连接集合中的数据项
     *
     * @param  string  $value
     * @param  string  $glue
     * @return string
     */
    public function implode($value, $glue = null)
    {
        return Arr::implode($this->items, $value, $glue);
    }

    /**
     * 返回两个集合的交集，结果集合将保留原来集合的键
     *
     * @param  mixed  $items
     * @return static
     */
    public function intersect($items)
    {
        return new static(array_intersect($this->items, $this->getArrayableItems($items)));
    }


    /**
     * 会从原生集合中移除任意没有在给定数组或集合中出现的键
     *
     * @param  mixed  $items
     * @return static
     */
    public function intersectByKeys($items)
    {
        return new static(array_intersect_key(
            $this->items, $this->getArrayableItems($items)
        ));
    }


    /**
     * 如果集合为空的话 isEmpty 方法返回 true；否则返回 false
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->items);
    }

    /**
     * 如果集合不为空的话 isNotEmpty 方法返回 true；否则返回 false
     *
     * @return bool
     */
    public function isNotEmpty()
    {
        return ! $this->isEmpty();
    }

    /**
     * 返回所有集合的键
     *
     * @return static
     */
    public function keys()
    {
        return new static(array_keys($this->items));
    }


    /**
     * 返回通过给定真值测试的数组中的最后一个元素
     *
     * @param  callable|null  $callback
     * @param  mixed  $default
     * @return mixed
     */
    public function last($callback = null, $default = null)
    {
        return Arr::last($this->items, $callback, $default);
    }


    /**
     * 从数组中获取值的数组
     *
     * @param  string|array  $value
     * @param  string|null  $key
     * @return static
     */
    public function pluck($value, $key = null)
    {
        return new static(Arr::pluck($this->items, $value, $key));
    }


    /**
     * 遍历并操作数组
     *
     * @param  callable  $callback
     * @return static
     */
    public function map($callback)
    {
        $items = Arr::map($this->items, $callback);
        return new static($items);
    }

    /**
     * 迭代集合项，传递每个嵌套集合项值到给定回调。
     * 在回调中我们可以修改集合项并将其返回，从而通过修改的值组合成一个新的集合
     *
     * @param  callable  $callback
     * @return static
     */
    public function mapSpread($callback)
    {
        $items = Arr::mapSpread($this->items, $callback);
        return new static($items);
    }

    /**
     * mapToGroups的别名
     *
     * @param  callable  $callback
     * @return static
     */
    public function mapToDictionary($callback)
    {
        $items = Arr::mapToGroups($this->items, $callback);
        return new static($items);
    }


    /**
     * 通过给定回调对集合项进行分组，回调会返回包含单个键值对的关联数组，从而将分组后的值组合成一个新的集合
     *
     * @param  callable  $callback
     * @return static
     */
    public function mapToGroups($callback)
    {
        $items = Arr::mapToGroups($this->items, $callback);
        return new static($items);
    }


    /**
     * 对集合进行迭代并传递每个值到给定回调，该回调会返回包含键值对的关联数组
     *
     * @param  callable  $callback
     * @return static
     */
    public function mapWithKeys($callback)
    {
        $items = Arr::mapWithKeys($this->items, $callback);
        return new static($items);
    }

    /**
     * 迭代集合并传递每个值到给定回调，该回调可以自由编辑数据项并将其返回，最后形成一个经过编辑的新集合。
     * 然后，这个数组在层级维度被扁平化
     *
     * @param  callable  $callback
     * @return static
     */
    public function flatMap($callback)
    {
        $items = Arr::flatMap($this->items, $callback);
        return new static($items);
    }

    /**
     * 迭代集合，通过传递值到构造器来为给定类创建新的实例
     *
     * @param  string  $class
     * @return static
     */
    public function mapInto($class)
    {
        $items = Arr::mapInto($this->items, $class);
        return new static($items);
    }


    /**
     * 求出最大值
     *
     * @param  callable|string|null  $callback
     * @return mixed
     */
    public function max($callback = null)
    {
        return Arr::max($this->items, $callback);
    }

    /**
     * 合并给定数组到集合。
     * 该数组中的任何字符串键匹配集合中的字符串键的将会重写集合中的值
     *
     * @param  mixed  $items
     * @return static
     */
    public function merge($items)
    {
        return new static(array_merge($this->items, $this->getArrayableItems($items)));
    }


    /**
     * 将一个集合的键和另一个数组或集合的值连接起来
     *
     * @param  mixed  $values
     * @return static
     */
    public function combine($values)
    {
        return new static(array_combine($this->all(), $this->getArrayableItems($values)));
    }

    /**
     * 添加给定数组到集合，如果给定数组包含已经在原来集合中存在的犍，原生集合的值会被保留
     *
     * @param  mixed  $items
     * @return static
     */
    public function union($items)
    {
        return new static($this->items + $this->getArrayableItems($items));
    }

    /**
     * 返回集合中给定键的最小值
     *
     * @param  callable|string|null  $callback
     * @return mixed
     */
    public function min($callback = null)
    {
        return Arr::min($this->items, $callback);
    }

    /**
     * 组合集合中第 n-th 个元素创建一个新的集合
     *
     * @param  int  $step
     * @param  int  $offset
     * @return static
     */
    public function nth($step, $offset = 0)
    {
        $items = Arr::nth($this->items, $step, $offset);
        return new static($items);
    }


    /**
     * 从给定数组中获取项目的子集。
     * 与之相对的是except方法
     *
     * @param  mixed  $keys
     * @return static
     */
    public function only($keys)
    {
        $items = Arr::only($this->items, $keys);
        return new static($items);
    }

    /**
     * 返回新的包含给定页数数据项的集合
     *
     * @param  int  $page
     * @param  int  $perPage
     * @return static
     */
    public function forPage($page, $perPage)
    {
        $items = Arr::forPage($this->items, $page, $perPage);
        return new static($items);
    }

    /**
     * 和 PHP 函数 list 一起使用，从而将通过真理测试和没通过的分割开来
     *
     * @param  callable|string  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return static
     */
    public function partition($key, $operator = null, $value = null)
    {
        $items = $this->route(__FUNCTION__, func_get_args());
        return new static($items);
    }

    /**
     * 使用回调函数处理数组并返回结果
     *
     * @param  callable $callback
     * @return mixed
     */
    public function pipe($callback)
    {
        return Arr::pipe($this->items, $callback);
    }

    /**
     * 获取并删除集合中的最后一项。
     *
     * @return mixed
     */
    public function pop()
    {
        return array_pop($this->items);
    }

    /**
     * 将数据项推入数组开头
     *
     * @param  mixed  $value
     * @param  mixed  $key
     * @return $this
     */
    public function prepend($value, $key = null)
    {
        $this->items = Arr::prepend($this->items, $value, $key);

        return $this;
    }

    /**
     * 将项目推到集合的末尾。
     *
     * @param  mixed  $value
     * @return $this
     */
    public function push($value)
    {
        $this->offsetSet(null, $value);

        return $this;
    }


    /**
     * 追加给定数据到数组末尾
     *
     * @param  \Traversable|array  $source
     * @return $this
     */
    public function concat($source)
    {
        $items = Arr::concat($this->items, $source);
        return new static($items);
    }


    /**
     * 从数组中返回并移除键值对
     *
     * @param  mixed  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function pull($key, $default = null)
    {
        return Arr::pull($this->items, $key, $default);
    }

    /**
     * 将项目放入集合中。
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return $this
     */
    public function put($key, $value)
    {
        $this->offsetSet($key, $value);

        return $this;
    }

    /**
     * 从数组中返回随机值
     *
     * @param  int|null  $number
     * @return static|mixed
     *
     * @throws \InvalidArgumentException
     */
    public function random($number = null)
    {
        if (is_null($number)) {
            return Arr::random($this->items);
        }

        return new static(Arr::random($this->items, $number));
    }


    /**
     * 用于减少集合到单个值，传递每个迭代结果到子迭代
     *
     * @param  callable  $callback
     * @param  mixed  $initial
     * @return mixed
     */
    public function reduce($callback, $initial = null)
    {
        return array_reduce($this->items, $callback, $initial);
    }


    /**
     * 使用给定回调过滤集合，该回调应该为所有它想要从结果集合中移除的数据项返回 true
     * 和 reject 方法相对的方法是 filter 方法
     *
     * @param  callable|mixed  $callback
     * @return static
     */
    public function reject($callback)
    {
        $items = Arr::reject($this->items, $callback);
        return new static($items);
    }


    /**
     * 反转集合
     *
     * @return static
     */
    public function reverse()
    {
        return new static(array_reverse($this->items, true));
    }


    /**
     * 为给定值查询集合，如果找到的话返回对应的键，如果没找到，则返回 false
     *
     * @param  mixed  $value
     * @param  bool  $strict
     * @return mixed
     */
    public function search($value, $strict = false)
    {
        return Arr::search($this->items, $value, $strict);
    }

    /**
     * 从集合中移除并返回第一个数据项
     *
     * @return mixed
     */
    public function shift()
    {
        return array_shift($this->items);
    }

    /**
     * 随机打乱集合中的数据项
     *
     * @param  int  $seed
     * @return static
     */
    public function shuffle($seed = null)
    {
        return new static(Arr::shuffle($this->items, $seed));
    }


    /**
     * 从给定索引开始返回集合的一个切片
     *
     * @param  int  $offset
     * @param  int  $length
     * @return static
     */
    public function slice($offset, $length = null)
    {
        return new static(array_slice($this->items, $offset, $length, true));
    }

    /**
     * 通过给定数值对集合进行分组
     *
     * @param  int  $numberOfGroups
     * @return static
     */
    public function split($numberOfGroups)
    {
        $items = Arr::split($this->items, $numberOfGroups);
        return new static($items);
    }

    /**
     * 将数组分割成相同个数的小数组
     *
     * @param  int  $size
     * @return static
     */
    public function chunk($size)
    {
        $items = Arr::chunk($this->items, $size);
        return new static($items);
    }

    /**
     * 给数组排序
     *
     * @param  callable|null  $callback
     * @return static
     */
    public function sort($callback = null)
    {
        $items = Arr::sort($this->items, $callback);
        return new static($items);
    }

    /**
     * 通过值对数组进行排序
     *
     * @param  callable|string  $callback
     * @param  int  $options
     * @param  bool  $descending
     * @return static
     */
    public function sortBy($callback, $options = SORT_REGULAR, $descending = false)
    {
        $items = Arr::sortBy($this->items, $callback, $options, $descending);
        return new static($items);
    }


    /**
     * 通过值对数组进行倒序排序
     *
     * @param  callable|string  $callback
     * @param  int  $options
     * @return static
     */
    public function sortByDesc($callback, $options = SORT_REGULAR)
    {
        $items = Arr::sortByDesc($this->items, $callback, $options);
        return new static($items);
    }

    /**
     * 对数组的键进行排序
     *
     * @param  int  $options
     * @param  bool  $descending
     * @return static
     */
    public function sortKeys($options = SORT_REGULAR, $descending = false)
    {
        $items = Arr::sortKeys($this->items, $options, $descending);
        return new static($items);
    }

    /**
     * 对数组的键进行倒序排序
     *
     * @param  int $options
     * @return static
     */
    public function sortKeysDesc($options = SORT_REGULAR)
    {
        $items = Arr::sortKeysDesc($this->items, $options);
        return new static($items);
    }


    /**
     * 从给定位置开始移除并返回数据项切片
     *
     * @param  int  $offset
     * @param  int|null  $length
     * @param  mixed  $replacement
     * @return static
     */
    public function splice($offset, $length = null, $replacement = array())
    {
        $items = $this->route(__FUNCTION__, func_get_args());
        return new static($items);
    }


    /**
     * 返回集合中所有数据项的和
     *
     * @param  callable|string|null  $callback
     * @return mixed
     */
    public function sum($callback = null)
    {
        return Arr::sum($this->items, $callback);
    }


    /**
     * 使用指定数目的数据项返回一个新的集合
     *
     * @param  int  $limit
     * @return static
     */
    public function take($limit)
    {
        if ($limit < 0) {
            return $this->slice($limit, abs($limit));
        }

        return $this->slice(0, $limit);
    }


    /**
     * 传递集合到给定回调，从而允许你在指定入口进入集合并对集合项进行处理而不影响集合本身
     *
     * @param  callable  $callback
     * @return $this
     */
    public function tap($callback)
    {
        $callback(new static($this->items));

        return $this;
    }

    /**
     * 迭代集合并对集合中每个数据项调用给定回调。集合中的数据项将会被替代成从回调中返回的值
     *
     * @param  callable  $callback
     * @return $this
     */
    public function transform($callback)
    {
        $this->items = $this->map($callback)->all();

        return $this;
    }

    /**
     * 返回集合中所有的唯一数据项， 返回的集合保持原来的数组键
     *
     * @param  string|callable|null  $key
     * @param  bool  $strict
     * @return static
     */
    public function unique($key = null, $strict = false)
    {
        $items = Arr::unique($this->items, $key, $strict);
        return new static($items);
    }

    /**
     * 返回集合中所有的唯一数据项, 进行严格比较
     *
     * @param  string|callable|null  $key
     * @return static
     */
    public function uniqueStrict($key = null)
    {
        $items = Arr::uniqueStrict($this->items, $key);
        return new static($items);
    }


    /**
     * 通过将集合键重置为连续整型数字的方式返回新的集合
     *
     * @return static
     */
    public function values()
    {
        return new static(array_values($this->items));
    }

    /**
     * 在与集合的值对应的索引处合并给定数组的值
     *
     * e.g. new Collection([1, 2, 3])->zip([4, 5, 6]);
     *      => [[1, 4], [2, 5], [3, 6]]
     *
     * @param  mixed ...$items
     * @return static
     */
    public function zip($items)
    {
        $items = Arr::zip($this->items, $items);
        return new static($items);
    }

    /**
     * 将给定值填充数组直到达到指定的最大长度
     *
     * @param  int  $size
     * @param  mixed  $value
     * @return static
     */
    public function pad($size, $value)
    {
        return new static(array_pad($this->items, $size, $value));
    }

    /**
     * 将集合转化为一个原生的 PHP 数组
     *
     * @return array
     */
    public function toArray()
    {
        return array_map(function ($value) {
            return $value instanceof Arrayable ? $value->toArray() : $value;
        }, $this->items);
    }


    /**
     * 将集合转化为 JSON
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        $items = array_map(function ($value) {
            return (array) $value;
        }, $this->items);
        return json_encode($items, $options);
    }

    /**
     * 获取项目的迭代器
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * 获取CachingIterator实例
     *
     * @param  int  $flags
     * @return \CachingIterator
     */
    public function getCachingIterator($flags = \CachingIterator::CALL_TOSTRING)
    {
        return new \CachingIterator($this->getIterator(), $flags);
    }

    /**
     * 返回集合中所有项的总数
     *
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * 从此集合中获取基本支持集合实例。
     *
     * @return self
     */
    public function toBase()
    {
        return new self($this);
    }

    /**
     * 确定项目是否存在于偏移处。
     *
     * @param  mixed  $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->items);
    }


    /**
     * 获取给定偏移量的项目
     *
     * @param  mixed  $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->items[$key];
    }

    /**
     * 将项目设置为给定的偏移量
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    /**
     * 在给定的偏移处取消设置项目
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->items[$key]);
    }

    /**
     * 将集合转换为其字符串表示形式
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * 结果Collection或Arrayable中的项数组
     *
     * @param  mixed  $items
     * @return array
     */
    protected function getArrayableItems($items)
    {
        if (is_array($items)) {
            return $items;
        } elseif ($items instanceof self) {
            return $items->all();
        } elseif ($items instanceof Arrayable) {
            return $items->toArray();
        } elseif ($items instanceof Jsonable) {
            return json_decode($items->toJson(), true);
        } elseif ($items instanceof Traversable) {
            return iterator_to_array($items);
        }

        return (array) $items;
    }




    /**
     * 扩展集合
     *
     * @param $name
     * @param $macro
     * @throws \Exception
     */
    public static function macro($name, $macro)
    {
        Build::bind($name, $macro, get_called_class());
    }





    /**
     * 动态调用Arr的方法
     *
     * @param $func
     * @param $arguments
     * @return mixed
     */
    protected function route($func, $arguments)
    {
        array_unshift($arguments, $this->items);
        return call_user_func_array(array(Arr::className(), $func), $arguments);
    }




    /**
     * 当调用不存在的动态方法时
     *
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        return Build::call($name, $arguments, $this);
    }


    /**
     * 当调用不存在的静态方法时
     *
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws \Exception
     */
    public static function __callStatic($name, $arguments)
    {
        return Build::callStatic($name, $arguments, get_called_class());
    }



}