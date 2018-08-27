<?php
namespace DavidNotBad\Support\Tests\ArraySet;

use DavidNotBad\Support\ArraySet\Arr;
use DavidNotBad\Support\Tests\Data\Access;
use DavidNotBad\Support\Tests\Data\Currency;
use PHPUnit\Framework\TestCase;

class ArrTest extends TestCase
{

    public function testAdd()
    {
        //元素不存在, 添加元素
        $array = array('a'=>'apple');
        $expected = array('a'=>'apple', 'b'=>'banana');
        $actual = Arr::add($array, 'b', 'banana');

        $this->assertEquals($expected, $actual);


        //元素存在, 添加元素
        $array = array('a'=>'apple', 'b'=>'banana');
        $expected = array('a'=>'apple', 'b'=>'banana');
        $actual = Arr::add($array, 'b', 'banana');

        $this->assertEquals($expected, $actual);
    }


    public function testGet()
    {
        //前两个参数为null时
        $expected = null;
        $actual = Arr::get(null, null, 'default');

        $this->assertEquals($expected, $actual);


        //第二个参数为null时
        $array = array('a'=>array('b'=>array('c'=>'d'), 'e'=>'f'), 'g'=>'h');
        $expected = $array;
        $actual = Arr::get($array, null, 'default');
        $this->assertEquals($expected, $actual);


        //key存在的情况
        $array = array('a'=>array('b'=>array('c'=>'d'), 'e'=>'f'), 'g'=>'h');
        $expected = 'h';
        $actual = Arr::get($array, 'g');

        $this->assertEquals($expected, $actual);


        //key不存在的情况
        $array = array('a'=>array('b'=>array('c'=>'d'), 'e'=>'f'), 'g'=>'h');
        $expected = 'default';
        $actual = Arr::get($array, 'i', 'default');

        $this->assertEquals($expected, $actual);


        //使用点语法的情况
        $array = array('a'=>array('b'=>array('c'=>'d'), 'e'=>'f'), 'g'=>'h');
        $expected = 'd';
        $actual = Arr::get($array, 'a.b.c');

        $this->assertEquals($expected, $actual);


        //使用"*"语法的情况
        $array = array(
            array('price'=>1),
            array('price'=>2),
            array('price'=>3),
        );

        $expected = range(1, 3);
        $actual = Arr::get($array, '*.price');

        $this->assertEquals($expected, $actual);
    }

    public function testAccessible()
    {
        //测试数组
        $this->assertTrue(Arr::accessible(array()));
        //测试arrayAccess的实例
        $this->assertTrue(Arr::accessible(new Access()));
    }

    public function testValue()
    {
        //测试参数为匿名函数
        $expected = 'apple';
        $actual = Arr::value(function(){
            return 'apple';
        });

        $this->assertEquals($expected, $actual);


        //测试参数为字符串
        $expected = 'apple';
        $actual = Arr::value('apple');

        $this->assertEquals($expected, $actual);
    }

    public function testExists()
    {
        //测试参数为arrayAccess的实例的情况
        $access = new Access();
        $access['a'] = 'apple';

        $this->assertTrue(
            Arr::exists($access, 'a')
        );


        //测试参数为数组的情况
        $risk = array('a'=>array('b'=>array('c'=>'d'), 'e'=>'f'), 'g'=>'h');
        $this->assertTrue(
            Arr::exists($risk, 'a')
        );
    }

    public function testSet()
    {
        //设置值
        $array = array('a'=>'apple');
        $expected = array('a'=>'apple', 'b'=>'banana');
        $actual = Arr::set($array, 'b', 'banana');

        $this->assertEquals($expected, $actual);


        //使用点语法设置值
        $array = array('a'=>'apple');
        $expected = array('a'=>'apple','b'=>array('c'=>array('d'=>'e')));
        $actual = Arr::set($array, 'b.c.d', 'e');

        $this->assertEquals($expected, $actual);
    }

    public function testCreate()
    {
        //创建数组
        $expected = array('a'=>'apple');
        $actual = Arr::create('a', 'apple');

        $this->assertEquals($expected, $actual);


        //使用点语法创建数组
        $expected = array('a'=>array('b'=>array('c'=>'d')));
        $actual = Arr::create('a.b.c', 'd');

        $this->assertEquals($expected, $actual);
    }


    public function testDot()
    {
        //将多维数组压缩成点语法表示的一维数组
        $expected = array('a.b.c'=>'d', 'e.f'=>'g', 'h'=>'i');
        $actual = Arr::dot(array('a'=>array('b'=>array('c'=>'d')), 'e'=>array('f'=>'g'), 'h'=>'i'));

        $this->assertEquals($expected, $actual);
    }


    public function testUndot()
    {
        //将点语法表示的一维数组拆分成多维数组
        $expected = array('a'=>array('b'=>array('c'=>'d')), 'e'=>array('f'=>'g'), 'h'=>'i');
        $actual = Arr::undot(array('a.b.c'=>'d', 'e.f'=>'g', 'h'=>'i'));

        $this->assertEquals($expected, $actual);
    }


    public function testZip()
    {
        //传递二维数组作为参数
        $expected = array(
            array(1, 4, 7),
            array(2, 5, 8),
            array(3, 6, 9)
        );
        $actual = Arr::zip(array(
            array(1, 2, 3),
            array(4, 5, 6),
            array(7, 8, 9)
        ));

        $this->assertEquals($expected, $actual);


        //传递多个一维数组作为参数
        $expected = array(
            array(1, 4, 7),
            array(2, 5, 8),
            array(3, 6, 9)
        );
        $actual = Arr::zip(array(1, 2, 3), array(4, 5, 6), array(7, 8, 9));

        $this->assertEquals($expected, $actual);


        //传递一个长度不一的二维数组
        $expected = array(
            array(1, 7, -1, 4),
            array(2, 8, -2, 5),
            array(3, 9, 0, 6)
        );
        $actual = Arr::zip(array(
            array(1, 2, 3, 11),
            array(7, 8, 9, 10, 12),
            array(-1, -2, 0, -3),
            array(4, 5, 6),
        ));

        $this->assertEquals($expected, $actual);


        //传递多个数组长度不一的一维数组
        $expected = array(
            array(1, 7, -1, 4),
            array(2, 8, -2, 5),
            array(3, 9, 0, 6)
        );
        $actual = Arr::zip(
            array(1, 2, 3, 11),
            array(7, 8, 9, 10, 12),
            array(-1, -2, 0, -3),
            array(4, 5, 6)
        );

        $this->assertEquals($expected, $actual);
    }


    public function testCollapse()
    {
        //将不包含键的二维数组合并成一维数组
        $expected = range(1, 9);
        $actual = Arr::collapse(array(
            array(1, 2, 3),
            array(4, 5, 6),
            array(7, 8, 9)
        ));

        $this->assertEquals($expected, $actual);


        //将包含键的二维数组合并成一维数组
        $expected = range(1, 9);
        $actual = Arr::collapse(array(
            'a'=>array(1, 2, 3),
            'b'=>array(4, 5, 6),
            'c'=>array(7, 8, 9)
        ));

        $this->assertEquals($expected, $actual);


        //将多维数组合并成一维数组
        $expected = array(
            'a'=>array('apple'),
            'b'=>array('banana'),
            'c'=>array('cat')
        );
        $actual = Arr::collapse(array(
            array('a'=>array('apple')),
            array('b'=>array('banana')),
            array('c'=>array('cat'))
        ));

        $this->assertEquals($expected, $actual);
    }


    public function testDevide()
    {
        //检查keys
        list($keys) = Arr::divide(array('a'=>'apple', 'b'=>'banana'));
        $expected = array('a', 'b');
        $actual = $keys;

        $this->assertEquals($expected, $actual);


        //检查values
        list(, $values) = Arr::divide(array('a'=>'apple', 'b'=>'banana'));
        $expected = array('apple', 'banana');
        $actual = $values;

        $this->assertEquals($expected, $actual);


        //检查提取数组的键和值
        $expected = array(
            array('a', 'b'),
            array('apple', 'banana')
        );
        $actual = Arr::divide(array('a'=>'apple', 'b'=>'banana'));

        $this->assertEquals($expected, $actual);
    }

    public function testExcept()
    {
        //删除数组中的某个成员, 第二个参数为数组
        $array = array('name' => 'Desk', 'price' => 100);
        $expected = array('name'=>'Desk');
        $actual = Arr::except($array, array('price'));

        $this->assertEquals($expected, $actual);


        //删除数组中的某个成员, 第二个参数为字符串
        $array = $temp = array('name' => 'Desk', 'price' => 100);
        $expected = array('name'=>'Desk');
        $actual = Arr::except($array, 'price');

        $this->assertEquals($expected, $actual);

        //测试是否改变原数组, 不改变为正确的
        $this->assertEquals($temp, $array);
    }

    public function testForget()
    {
        //当第二个参数为空时的情况
        $array = array('products' => array('desk' => array('price' => 100)));
        $expected = $array;
        $actual = Arr::forget($array, '');

        $this->assertEquals($expected, $actual);


        //当第二个参数为字符串的情况
        $array = array('products' => array('desk' => array('price' => 100)));
        $expected = array();
        $actual = Arr::forget($array, 'products');

        $this->assertEquals($expected, $actual);
        //测试原数组是否改变
        $this->assertEquals($array, array());


        //测试第二个参数使用点语法的情况
        $array = array('products' => array('desk' => array('price' => 100)));
        $expected = array('products' => array());
        $actual = Arr::forget($array, 'products.desk');

        $this->assertEquals($expected, $actual);


        //测试原数组是否改变
        $array = array('products' => array('desk' => array('price' => 100)));
        Arr::forget($array, 'products.desk');
        $expected = array('products' => array());

        $this->assertEquals($expected, $array);
    }

    public function testFirst()
    {
        //测试第一个参数为空数组, 第二个参数为null, 并且设置了第三个参数的情况
        $expected = 'default';
        $actual = Arr::first(array(), null, 'default');

        $this->assertEquals($expected, $actual);


        //测试第二个参数为null的情况
        $array = array('a'=>'apple', 'b'=>'banana');
        $expected = 'apple';
        $actual = Arr::first($array, null, 'default');

        $this->assertEquals($expected, $actual);


        //测试第二个参数为回调函数的情况
        $array = array(100, 200, 300);
        $expected = 200;
        $actual = Arr::first($array, function($value){
            return $value > 150;
        });

        $this->assertEquals($expected, $actual);
    }


    public function testFlatten()
    {
        //没有传递第二个参数
        $time = time();
        $array = array('name' => 'Joe', 'languages' => array('PHP', 'Ruby', 'time'=>array($time)));
        $expected = array('Joe', 'PHP', 'Ruby', $time);
        $actual = Arr::flatten($array);

        $this->assertEquals($expected, $actual);


        //第二个参数传递1
        $array = array(
            'Apple' => array(
                array('name' => 'iPhone 6S', 'brand' => 'Apple'),
            ),
            'Samsung' => array(
                array('name' => 'Galaxy S7', 'brand' => 'Samsung')
            ),
        );
        $expected = array(
            array('name' => 'iPhone 6S', 'brand' => 'Apple'),
            array('name' => 'Galaxy S7', 'brand' => 'Samsung')
        );
        $actual = Arr::flatten($array, 1);

        $this->assertEquals($expected, $actual);


        //第二个参数传递2
        $array = array(
            'Apple' => array(
                array('name' => 'iPhone 6S', 'brand' => 'Apple'),
            ),
            'Samsung' => array(
                array('name' => 'Galaxy S7', 'brand' => 'Samsung')
            ),
        );
        $expected = array(
            'iPhone 6S', 'Apple', 'Galaxy S7', 'Samsung'
        );
        $actual = Arr::flatten($array, 2);

        $this->assertEquals($expected, $actual);
    }


    public function testHas()
    {
        //测试keys为null的情况
        $array = array('a'=>'apple', 'b'=>'banana');
        $this->assertFalse(Arr::has($array, null));


        //测试array和keys都为null的情况
        $this->assertFalse(Arr::has(null, null));


        //测试keys为空数组的情况
        $array = array('a'=>'apple', 'b'=>'banana');
        $this->assertFalse(Arr::has($array, array()));


        //测试keys为字符串的情况
        $array = array('a'=>'apple', 'b'=>'banana');
        $this->assertTrue(Arr::has($array, 'a'));


        //测试keys包含点语法的情况
        $array = array('products' => array('desk' => array('price' => 100)));
        $this->assertTrue(Arr::has($array, 'products.desk.price'));
    }

    public function testLast()
    {
        //测试array为空数组, callback为null的情况
        $expected = 'default';
        $actual = Arr::last(array(), null, 'default');

        $this->assertEquals($expected, $actual);


        //测试callback为null的情况
        $array = array('a'=>'apple', 'b'=>'banana');
        $expected = 'banana';
        $actual = Arr::last($array, null, 'default');

        $this->assertEquals($expected, $actual);


        //测试callback为匿名函数的情况
        $array = array(100, 200, 300);
        $expected = 200;
        $actual = Arr::last($array, function($value){
            return $value < 300;
        });

        $this->assertEquals($expected, $actual);
    }


    public function testOnly()
    {
        //测试keys为字符串的情况
        $array = array('a'=>'apple', 'b'=>'banana');
        $expected = array('a'=>'apple');
        $actual = Arr::only($array, 'a');

        $this->assertEquals($expected, $actual);


        //测试keys为数组的情况
        $array = array('a'=>'apple', 'b'=>'banana', 'c'=>'cat');
        $expected = array('a'=>'apple', 'b'=>'banana');
        $actual = Arr::only($array, array('a','b'));

        $this->assertEquals($expected, $actual);
    }


    public function testPluck()
    {
        //测试不包含点语法并且值存在的情况
        $array = array(
            array('developer' => array('id' => 1, 'name' => 'Taylor'), 'type' => 1),
            array('developer' => array('id' => 2, 'name' => 'Abigail'), 'type' => 2),
        );
        $expected = array(1, 2);
        $actual = Arr::pluck($array, 'type');

        $this->assertEquals($expected, $actual);

        //测试包含点语法的情况
        $array = array(
            array('developer' => array('id' => 1, 'name' => 'Taylor')),
            array('developer' => array('id' => 2, 'name' => 'Abigail')),
        );
        $expected = array('Taylor', 'Abigail');
        $actual = Arr::pluck($array, 'developer.name');

        $this->assertEquals($expected, $actual);

        //测试第三个参数key存在的情况
        $array = array(
            array('developer' => array('id' => 1, 'name' => 'Taylor')),
            array('developer' => array('id' => 2, 'name' => 'Abigail')),
        );
        $expected = array(1=>'Taylor', 2=>'Abigail');
        $actual = Arr::pluck($array, 'developer.name', 'developer.id');

        $this->assertEquals($expected, $actual);
    }

    public function testPrepend()
    {
        //测试key存在的情况
        $array = array('b' => 'banana');
        $expected = array('a' => 'apple', 'b' => 'banana');
        $actual = Arr::prepend($array, 'apple', 'a');

        $this->assertEquals($expected, $actual);


        //测试key不存在的情况
        $array = array('b' => 'banana');
        $expected = array('apple', 'b' => 'banana');
        $actual = Arr::prepend($array, 'apple');

        $this->assertEquals($expected, $actual);
    }

    public function testPull()
    {
        //测试key不存在的情况
        $array = array('a' => 'apple', 'b' => 'banana');
        $expected = 'apple';
        $actual = Arr::pull($array, 'a');

        $this->assertEquals($expected, $actual);

        //测试原数组是否改变
        $expected = array('b'=>'banana');
        $actual = $array;
        $this->assertEquals($expected, $actual);


        //测试key不存在的情况
        $array = array('a' => 'apple', 'b' => 'banana');
        $expected = null;
        $actual = Arr::pull($array, 'c');

        $this->assertEquals($expected, $actual);


        //测试第三个参数不存在的情况
        $array = array('a' => 'apple', 'b' => 'banana');
        $expected = null;
        $actual = Arr::pull($array, 'c');

        $this->assertEquals($expected, $actual);
    }

    public function testRandom()
    {
        //测试是否随机数
        $array = range(1, 99);
        $random = Arr::random($array);

        $this->assertTrue(in_array($random, $array));


        //测试第二个参数超出范围
        $array = range(1, 10);
        $expected = $array;
        $actual = Arr::random($array, 12);

        $this->assertEquals($expected, $actual);


        //测试第二个参数为0
        $array = range(1, 10);
        $expected = array();
        $actual = Arr::random($array, 0);;

        $this->assertEquals($expected, $actual);

    }


    public function testSort()
    {
        $array = array(5, 3, 1, 2, 4);
        $expected = array(2=>1, 3=>2, 1=>3, 4=>4, 0=>5);
        $actual = Arr::sort($array);
        $this->assertEquals($expected, $actual);
    }


    public function testSortBy()
    {
        //callback参数为字符串
        $array = array(
            array('name' => 'Desk', 'price' => 200),
            array('name' => 'Chair', 'price' => 100),
            array('name' => 'Bookcase', 'price' => 150),
        );
        $sort = Arr::sortBy($array, 'price');
        $expected = array(1, 2, 0);
        $actual = array_keys($sort);

        $this->assertEquals($expected, $actual);


        //callback参数为回调函数
        $array = array(
            array('name' => 'Desk', 'price' => 200),
            array('name' => 'Chair', 'price' => 100),
            array('name' => 'Bookcase', 'price' => 150),
        );
        $sort = Arr::sortBy($array, function($item){
            return $item['price'];
        });
        $expected = array(1, 2, 0);
        $actual = array_keys($sort);

        $this->assertEquals($expected, $actual);


        //option参数为SORT_STRING
        $array = array(
            array('name' => 'Desk', 'price' => 200),
            array('name' => 'Chair', 'price' => 100),
            array('name' => 'Bookcase', 'price' => 150),
        );
        $sort = Arr::sortBy($array, 'name', SORT_STRING);
        $expected = array(2, 1, 0);
        $actual = array_keys($sort);

        $this->assertEquals($expected, $actual);


        //callback参数为字符串
        $array = array(
            array('name' => 'Desk', 'price' => 200),
            array('name' => 'Chair', 'price' => 100),
            array('name' => 'Bookcase', 'price' => 150),
        );
        $sort = Arr::sortBy($array, 'price', SORT_REGULAR, true);
        $expected = array(0, 2, 1);
        $actual = array_keys($sort);

        $this->assertEquals($expected, $actual);
    }

    public function testSrotDesc()
    {
        //callback参数为字符串
        $array = array(
            array('name' => 'Desk', 'price' => 200),
            array('name' => 'Chair', 'price' => 100),
            array('name' => 'Bookcase', 'price' => 150),
        );
        $sort = Arr::sortByDesc($array, 'price');
        $expected = array(0, 2, 1);
        $actual = array_keys($sort);

        $this->assertEquals($expected, $actual);
    }

    public function testSrotKeys()
    {
        //callback参数为字符串
        $array = array(
            'id' => 22345,
            'first' => 'John',
            'last' => 'Doe',
        );
        $sort = Arr::sortKeys($array);
        $expected = array('first', 'id', 'last');
        $actual = array_keys($sort);

        $this->assertEquals($expected, $actual);
    }

    public function testSrotKeysDesc()
    {
        //callback参数为字符串
        $array = array(
            'id' => 22345,
            'first' => 'John',
            'last' => 'Doe',
        );
        $sort = Arr::sortKeysDesc($array);
        $expected = array('last', 'id', 'first');
        $actual = array_keys($sort);

        $this->assertEquals($expected, $actual);
    }

    public function testWhere()
    {
        //测试只有三个参数的情况
        $array = array(
            array('account_id' => 1, 'product' => 'Desk'),
            array('account_id' => 2, 'product' => 'Chair'),
        );
        $expected = array(array('account_id' => 1, 'product' => 'Desk'));
        $actual = Arr::where($array, 'account_id', 1);

        $this->assertEquals($expected, $actual);


        //测试四个参数的情况
        $array = array(
            array('account_id' => 1, 'product' => 'Desk'),
            array('account_id' => 2, 'product' => 'Chair'),
        );
        $expected = array(array('account_id' => 1, 'product' => 'Desk'));
        $actual = Arr::where($array, 'account_id', '<', 2);

        $this->assertEquals($expected, $actual);
    }

    public function testMap()
    {
        $array = array("a"=>1, "b"=>2, "c"=>3, "d"=>4, "e"=>5);
        $expected = array("a"=>false, "b"=>false, "c"=>true, "d"=>true, "e"=>true);
        $actual = Arr::map($array, function($item){
            return $item > 2;
        });

        $this->assertEquals($expected, $actual);
    }

    public function testSortRecursive()
    {
        //测试索引数组
        $array = array(
            array('Roman', 'Taylor', 'Li'),
            array('PHP', 'Ruby', 'JavaScript'),
        );
        $expected = array(
            array('JavaScript', 'PHP', 'Ruby'),
            array('Li', 'Roman', 'Taylor'),
        );
        $actual = Arr::sortRecursive($array);

        $this->assertEquals($expected, $actual);

        //测试关联数组
        $array = array(
            'b' => array('Roman', 'Taylor', 'Li'),
            'a' => array('PHP', 'Ruby', 'JavaScript'),
            'c' => array('MySQL', 'SQLServer', 'Oracle'),
        );
        $expected = array(
            'a' => array('JavaScript', 'PHP', 'Ruby'),
            'b' => array('Li', 'Roman', 'Taylor'),
            'c' => array('MySQL', 'Oracle', 'SQLServer'),
        );
        $actual = Arr::sortRecursive($array);

        $this->assertEquals($expected, $actual);
    }

    public function testIsAssoc()
    {
        //测试索引数组
        $array = array(
            array('Roman', 'Taylor', 'Li'),
            array('PHP', 'Ruby', 'JavaScript'),
        );
        $this->assertFalse(Arr::isAssoc($array));


        //测试关联数组
        $array = array(
            'b' => array('Roman', 'Taylor', 'Li'),
            'a' => array('PHP', 'Ruby', 'JavaScript'),
            'c' => array('MySQL', 'SQLServer', 'Oracle'),
        );
        $this->assertTrue(Arr::isAssoc($array));
    }


    public function testSum()
    {
        $expected = 55;
        $actual = Arr::sum(range(1, 10));

        $this->assertEquals($expected, $actual);


        //测试点语法的情况
        $array = array(
            array('price'=>1),
            array('price'=>2),
            array('price'=>3),
            array('price'=>4),
        );
        $expected = 10;
        $actual = Arr::sum($array, '*.price');

        $this->assertEquals($expected, $actual);


        //测试嵌套的 "*" 语法的情况
        $array = array(
            'a' => array(
                array('price'=>1),
                array('price'=>2),
                array('price'=>3),
                array('price'=>4),
            ),
        );
        $expected = 10;
        $actual = Arr::sum($array, 'a.*.price');

        $this->assertEquals($expected, $actual);


        //测试匿名函数的情况
        $array = array(
            array('price'=>1),
            array('price'=>2),
            array('price'=>3),
            array('price'=>4),
        );
        $expected = 10;
        $actual = Arr::sum($array, function($item){
            return $item['price'];
        });

        $this->assertEquals($expected, $actual);
    }

    public function testAvg()
    {
        $expected = 5.5;
        $actual = Arr::avg(range(1, 10));

        $this->assertEquals($expected, $actual);


        //测试点语法的情况
        $array = array(
            array('price'=>1),
            array('price'=>2),
            array('price'=>3),
            array('price'=>4),
            array('price'=>5),
        );
        $expected = 3;
        $actual = Arr::avg($array, 'price');
        $this->assertEquals($expected, $actual);



        //测试匿名函数的情况
        $array = array(
            array('price'=>1),
            array('price'=>2),
            array('price'=>3),
            array('price'=>4),
            array('price'=>5),
        );
        $expected = 3;
        $actual = Arr::avg($array, function($item){
            return $item['price'];
        });

        $this->assertEquals($expected, $actual);
    }


    public function testChunk()
    {
        $array = range(1, 6);
        $expected = array(array(1,2), array(2=>3, 3=>4), array(4=>5, 5=>6));
        $actual = Arr::chunk($array, 2);

        $this->assertEquals($expected, $actual);
    }

    public function testCombine()
    {
        $expected = array('a'=>'apple', 'b'=>'banana');
        $actual = Arr::combine(array('a', 'b'), array('apple', 'banana'));

        $this->assertEquals($expected, $actual);


        //超过3个参数的情况
        $expected = array('a'=>array('apple', 1), 'b'=>array('banana', 2));
        $actual = Arr::combine(array('a', 'b'), array('apple', 'banana'), array('1', '2'));

        $this->assertEquals($expected, $actual);
    }


    public function testConcat()
    {
        //第二个参数是字符串
        $expected = array('a');
        $actual = Arr::concat(array(), 'a');

        $this->assertEquals($expected, $actual);


        //第二个参数是数组
        $expected = array('a', 'b');
        $actual = Arr::concat(array('a'), array('b'));

        $this->assertEquals($expected, $actual);
    }

    public function testContains()
    {
        //判断一维数组值存在的情况, 相当于in_array
        $array = array('name' => 'Desk', 'price' => 100);
        $this->assertTrue(Arr::contains($array, 'Desk'));


        //判断一维数组值不存在的情况, 相当于in_array
        $array = array('name' => 'Desk', 'price' => 100);
        $this->assertFalse(Arr::contains($array, 'New York'));


        //判断二维数组的情况
        $array = $collection = array(
            array('product' => 'Desk', 'price' => 200),
            array('product' => 'Chair', 'price' => 100),
        );
        $this->assertFalse(Arr::contains($array, 'product', 'aa'));
        $this->assertTrue(Arr::contains($array, 'product', 'Desk'));
        $this->assertTrue(Arr::contains($array, 'price', '>', '100'));


        //使用回调函数的情况
        $array = array(1, 2, 3, 4, 5);
        $this->assertTrue(Arr::contains($array, function($value){
            return $value == 5;
        }));

    }


    public function testContainsStrict()
    {
        //判断一维数组值存在的情况
        $array = array('name' => 'Desk', 'price' => 100);
        $this->assertFalse(Arr::containsStrict($array, '100'));


        //使用回调函数的情况
        $array = array(1, 2, 3, 4, 5);
        $this->assertFalse(Arr::containsStrict($array, function($value){
            return $value === '5';
        }));


        //判断一维数组值不存在的情况
        $array = array('name' => 'Desk', 'price' => 100);
        $this->assertFalse(Arr::containsStrict($array, 'New York'));


        //判断二维数组的情况
        $array = $collection = array(
            array('product' => 'Desk', 'price' => 200),
            array('product' => 'Chair', 'price' => 100),
        );
        $this->assertFalse(Arr::containsStrict($array, 'product', 'aa'));
        $this->assertTrue(Arr::containsStrict($array, 'price', 100));
        $this->assertFalse(Arr::containsStrict($array, 'price', '100'));
    }


    public function testCrossJoin()
    {
        //只有两个参数
        $array = array(1, 2);
        $expected = array(
            array(1, 'a'),
            array(1, 'b'),
            array(2, 'a'),
            array(2, 'b'),
        );
        $actual = Arr::crossJoin($array, array('a', 'b'));
        $this->assertEquals($expected, $actual);


        //三个参数
        $array = array(1, 2);
        $expected = array(
            array(1, 'a', 'I'),
            array(1, 'a', 'II'),
            array(1, 'b', 'I'),
            array(1, 'b', 'II'),
            array(2, 'a', 'I'),
            array(2, 'a', 'II'),
            array(2, 'b', 'I'),
            array(2, 'b', 'II'),
        );
        $actual = Arr::crossJoin($array, array('a', 'b'), array('I', 'II'));
        $this->assertEquals($expected, $actual);
    }


    public function testEach()
    {
        $instance = $this;
        Arr::each(array('a'), function($item, $key)use($instance){
            $instance->assertEquals('0', $key);
            $instance->assertEquals('a', $item);
        });
    }

    public function testEachSpread()
    {
        $array = array(
            array('John Doe', 35),
        );
        $instance = $this;
        Arr::eachSpread($array, function($name, $age, $key)use($instance){
            $instance->assertEquals('John Doe', $name);
            $instance->assertEquals(35, $age);
            $instance->assertEquals(0, $key);
        });
    }


    public function testEvery()
    {
        //参数二是回调函数
        $array = array(1, 2, 3, 4);
        $this->assertFalse(Arr::every($array, function($item){
            return $item > 2;
        }));


        //参数二是一个字符串, 满足的情况
        $array = array(
            array('a' => true),
            array('a' => true),
        );
        $this->assertTrue(Arr::every($array, 'a'));


        //参数二是一个字符串, 不满足的情况
        $array = array(
            array('a' => true),
            array('a' => false),
        );
        $this->assertFalse(Arr::every($array, 'a'));


        //参数二是一个字符串, 不满足的情况
        $array = $collection = array(
            array('product' => 'Desk', 'price' => 100),
            array('product' => 'Chair', 'price' => 100),
        );
        $this->assertFalse(Arr::every($array, 'product', 'Desk'));
        $this->assertTrue(Arr::every($array, 'price', 100));
        $this->assertFalse(Arr::every($array, 'price', '>', 100));
    }

    public function testReject()
    {
        //测试不传递第二个参数的情况
        $array = array(1, '1', true, false, null, '', 0, '0');
        $expected = array(3=>false, 4=>null, 5=>'', 6=>0, 7=>'0');
        $actual = Arr::reject($array);
        $this->assertEquals($expected, $actual);


        //测试参数callback存在的情况
        $array = array("a"=>1, "b"=>2, "c"=>3, "d"=>4, "e"=>5);
        $expected = array("a"=>1, "b"=>2);
        $actual = Arr::reject($array, function($item){
            return $item > 2;
        });

        $this->assertEquals($expected, $actual);
    }


    public function testFirstWhere()
    {
        //参数二是一个字符串, 满足的情况
        $array = array(
            array('a' => 'apple', 'b'),
            array('a' => 'aa', 'c'),
        );
        $expected = array('a'=>'aa', 'c');
        $actual = Arr::firstWhere($array, 'a', 'aa');
        $this->assertEquals($expected, $actual);


        //参数二是一个字符串, 不满足的情况
        $array = $collection = array(
            array('product' => 'Desk', 'price' => 100),
            array('product' => 'Chair', 'price' => 200),
        );
        $expected = array('product' => 'Chair', 'price' => 200);
        $actual = Arr::firstWhere($array, 'price', '>', 100);
        $this->assertEquals($expected, $actual);
    }


    public function testFlatMap()
    {
        $array = array(
            array('name' => 'Sally'),
            array('school' => 'Arkansas'),
            array('age' => 2)
        );
        $excepted = array('name'=>'SALLY', 'school'=>'ARKANSAS', 'age'=>2);
        $actual = Arr::flatMap($array, function($item){
            return array_map('strtoupper', $item);
        });
        $this->assertEquals($excepted, $actual);
    }


    public function testForPage()
    {
        $array = range(1, 10);
        $expected = array(3=>4, 4=>5, 5=>6);
        $actual = Arr::forPage($array, 2, 3);

        $this->assertEquals($expected, $actual);
    }


    public function testGroupBy()
    {
        //第二个参数是一个值
        $array = array(
            array('account_id' => 'account-x10', 'product' => 'Chair'),
            array('account_id' => 'account-x10', 'product' => 'Bookcase'),
            array('account_id' => 'account-x11', 'product' => 'Desk'),
        );

        $expected = array(
            'account-x10' => array(
                array('account_id' => 'account-x10', 'product' => 'Chair'),
                array('account_id' => 'account-x10', 'product' => 'Bookcase')
            ),
            'account-x11' => array(
                array('account_id' => 'account-x11', 'product' => 'Desk'),
            )
        );
        $actual = Arr::groupBy($array, 'account_id');
        $this->assertEquals($expected, $actual);



        //第二个参数传递一个回调函数
        $array = array(
            array('account_id' => 'account-x10', 'product' => 'Chair'),
            array('account_id' => 'account-x10', 'product' => 'Bookcase'),
            array('account_id' => 'account-x11', 'product' => 'Desk'),
        );
        $expected = array(
            'x10' => array(
                array('account_id' => 'account-x10', 'product' => 'Chair'),
                array('account_id' => 'account-x10', 'product' => 'Bookcase')
            ),
            'x11' => array(
                array('account_id' => 'account-x11', 'product' => 'Desk')
            )
        );
        $actual = Arr::groupBy($array, function ($item) {
            return substr($item['account_id'], -3);
        });

        $this->assertEquals($expected, $actual);


        //多个分组条件
        $array = array(
            10 => array('user' => 1, 'skill' => 1, 'roles' => array('Role_1', 'Role_3')),
            20 => array('user' => 2, 'skill' => 1, 'roles' => array('Role_1', 'Role_2')),
            30 => array('user' => 3, 'skill' => 2, 'roles' => array('Role_1')),
            40 => array('user' => 4, 'skill' => 2, 'roles' => array('Role_2')),
        );
        $expected = array(
            1 => array(
                'Role_1' => array(
                    10 => array('user' => 1, 'skill' => 1, 'roles' => array('Role_1', 'Role_3')),
                    20 => array('user' => 2, 'skill' => 1, 'roles' => array('Role_1', 'Role_2')),
                ),
                'Role_2' => array(
                    20 => array('user' => 2, 'skill' => 1, 'roles' => array('Role_1', 'Role_2')),
                ),
                'Role_3' => array(
                    10 => array('user' => 1, 'skill' => 1, 'roles' => array('Role_1', 'Role_3')),
                ),
            ),
            2 => array(
                'Role_1' => array(
                    30 => array('user' => 3, 'skill' => 2, 'roles' => array('Role_1')),
                ),
                'Role_2' => array(
                    40 => array('user' => 4, 'skill' => 2, 'roles' => array('Role_2')),
                ),
            ),
        );
        $actual = Arr::groupBy($array, array(
            'skill',
            function ($item) {
                return $item['roles'];
            },
        ), $preserveKeys = true);

        $this->assertEquals($expected, $actual);

    }


    public function testImplode()
    {
        //有三个参数的情况
        $array = array(
            array('account_id' => 1, 'product' => 'Desk'),
            array('account_id' => 2, 'product' => 'Chair'),
        );

        $expected = 'Desk, Chair';
        $actual = Arr::implode($array, 'product', ', ');
        $this->assertEquals($expected, $actual);


        //一维数组的情况
        $array = array(1, 2, 3, 4, 5);
        $expected = '1-2-3-4-5';
        $actual = Arr::implode($array, '-');
        $this->assertEquals($expected, $actual);
    }


    public function tempKeyBy()
    {
        //第二个参数是一个键
        $array = array(
            array('product_id' => 'prod-100', 'name' => 'desk'),
            array('product_id' => 'prod-200', 'name' => 'chair'),
        );

        $expected = array(
            'prod-100' => array('product_id' => 'prod-100', 'name' => 'Desk'),
            'prod-200' => array('product_id' => 'prod-200', 'name' => 'Chair'),
        );
        $actual = Arr::keyBy($array, 'product_id');
        $this->assertEquals($expected, $actual);


        //第二个参数是回调函数
        $array = array(
            array('product_id' => 'prod-100', 'name' => 'desk'),
            array('product_id' => 'prod-200', 'name' => 'chair'),
        );
        $expected = array(
            'PROD-100' => array('product_id' => 'prod-100', 'name' => 'Desk'),
            'PROD-200' => array('product_id' => 'prod-200', 'name' => 'Chair'),
        );
        $actual = Arr::keyBy($array, function ($item) {
            return strtoupper($item['product_id']);
        });
        $this->assertEquals($expected, $actual);
    }


    public function testMapInto()
    {
        $array = array('USD', 'EUR', 'GBP');
        $expected = array(new Currency('USD'), new Currency('EUR'), new Currency('GBP'));
        $actual = Arr::mapInto($array, '\DavidNotBad\Support\Tests\Data\Currency');

        $this->assertEquals($expected, $actual);
    }


    public function testMapSpread()
    {
        $array = array(
            array(0, 1), array(2, 3), array(4, 5), array(6, 7)
        );
        $expected = array(1, 5, 9, 13);
        $actual = Arr::mapSpread($array, function($odd, $even){
            return $odd + $even;
        });
        $this->assertEquals($expected, $actual);
    }


    public function testMapToGroups()
    {
        $array = array(
            array(
                'name' => 'John Doe',
                'department' => 'Sales',
            ),
            array(
                'name' => 'Jane Doe',
                'department' => 'Sales',
            ),
            array(
                'name' => 'Johnny Doe',
                'department' => 'Marketing',
            )
        );
        $expected = array(
            'Sales' => array('John Doe', 'Jane Doe'),
            'Marketing' => array('Johnny Doe'),
        );
        $actual = Arr::mapToGroups($array, function ($item) {
            return array($item['department'] => $item['name']);
        });
        $this->assertEquals($expected, $actual);
    }


    public function testMapWithKeys()
    {
        $array = array(
            array(
                'name' => 'John',
                'department' => 'Sales',
                'email' => 'john@example.com'
            ),
            array(
                'name' => 'Jane',
                'department' => 'Marketing',
                'email' => 'jane@example.com'
            )
        );
        $expected = array(
                'john@example.com' => 'John',
                'jane@example.com' => 'Jane',
        );
        $actual = Arr::mapWithKeys($array, function ($item) {
            return array($item['email'] => $item['name']);
        });

        $this->assertEquals($expected, $actual);
    }


    public function testMax()
    {
        //测试只有一个参数的情况
        $array = array(1, 2, 3, 4, 5);
        $expected = 5;
        $actual = Arr::max($array);

        $this->assertEquals($expected, $actual);



        //测试含有参数二的情况
        $array = array(
            array('foo'=>10),
            array('foo'=>20)
        );
        $expected = 20;
        $actual = Arr::max($array, 'foo');

        $this->assertEquals($expected, $actual);


        //测试含有参数二的情况
        $array = array(
            array('foo'=>5),
            array('foo'=>10),
            array('foo'=>20),
        );
        $expected = 10;
        $actual = Arr::max($array, function($item){
            return $item['foo'] < 20;
        });

        $this->assertEquals($expected, $actual);
    }


    public function testMedian()
    {
        //测试含有参数二的情况
        $array = array(
            array('foo' => 10),
            array('foo' => 10),
            array('foo' => 20),
            array('foo' => 40)
        );
        $expected = 15;
        $actual = Arr::median($array, 'foo');
        $this->assertEquals($expected, $actual);


        $array = array(1, 1, 2, 4);
        $expected = 1.5;
        $actual = Arr::median($array);
        $this->assertEquals($expected, $actual);
    }


    public function testMin()
    {
        //测试含有参数二的情况
        $array = array(
            array('foo' => 10),
            array('foo' => 20)
            );
        $expected = 10;
        $actual = Arr::min($array, 'foo');
        $this->assertEquals($expected, $actual);


        $array = array(1, 2, 3, 4, 5);
        $expected = 1;
        $actual = Arr::min($array);
        $this->assertEquals($expected, $actual);
    }


    public function testMode()
    {
        //测试含有参数二的情况
        $array = array(
            array('foo' => 10),
            array('foo' => 10),
            array('foo' => 20),
            array('foo' => 40)
        );
        $expected = array(10);
        $actual = Arr::mode($array, 'foo');
        $this->assertEquals($expected, $actual);



        $array = array(1, 1, 2, 4);
        $expected = array(1);
        $actual = Arr::mode($array);
        $this->assertEquals($expected, $actual);
    }


    public function testNth()
    {
        $array = array('a', 'b', 'c', 'd', 'e', 'f');
        $expected = array(0=>'a', 2=>'c', 4=>'e');
        $actual = Arr::nth($array, 2);
        $this->assertEquals($expected, $actual);


        $array = array('a', 'b', 'c', 'd', 'e', 'f');
        $expected = array(1=>'b',3=>'d',5=>'f');
        $actual = Arr::nth($array, 2, 1);
        $this->assertEquals($expected, $actual);
    }


    public function testPartition()
    {
        $array = array(1, 2, 3, 4, 5, 6);

        list($underThree, $aboveThree) = Arr::partition($array, function ($i) {
            return $i < 3;
        });
        $expectedUnderThree = array(1, 2);
        $expedtedAboveThree = array(2=>3, 4, 5, 6);
        $this->assertEquals($expectedUnderThree, $underThree);
        $this->assertEquals($expedtedAboveThree, $aboveThree);
    }


    public function testSplit()
    {
        $array = array(1, 2, 3, 4, 5);
        $expected = array(array(1, 2), array(2=>3, 4), array(4=>5));
        $actual = Arr::split($array, 3);
        $this->assertEquals($expected, $actual);
    }


    public function testTimes()
    {
        $expected = array(2, 4, 6, 8, 10);
        $actual = Arr::times(5, function ($number) {
            return $number * 2;
        });
        $this->assertEquals($expected, $actual);
    }


    public function testUnion()
    {
        $array = array(1=>'a', 2=>'b');
        $expected = array(1=>'a', 2=>'b', 3=>'c');
        $actual = Arr::union($array, array(3=>'c', 1=>'b'));
        $this->assertEquals($expected, $actual);
    }


    public function testUnique()
    {
        //处理一维数组
        $array = array(1, 1, 2, 2, 3, 4, 2);
        $expected = array(0=>1,2=>2,4=>3,5=>4);
        $actual = Arr::unique($array);
        $this->assertEquals($expected, $actual);


        //处理多维数组
        $array = array(
            array('name' => 'iPhone 6', 'brand' => 'Apple', 'type' => 'phone'),
            array('name' => 'iPhone 5', 'brand' => 'Apple', 'type' => 'phone'),
            array('name' => 'Apple Watch', 'brand' => 'Apple', 'type' => 'watch'),
            array('name' => 'Galaxy S6', 'brand' => 'Samsung', 'type' => 'phone'),
            array('name' => 'Galaxy Gear', 'brand' => 'Samsung', 'type' => 'watch'),
        );
        $expected = array(
            0 => array('name' => 'iPhone 6', 'brand' => 'Apple', 'type' => 'phone'),
            3 => array('name' => 'Galaxy S6', 'brand' => 'Samsung', 'type' => 'phone'),
        );
        $actual = Arr::unique($array, 'brand');
        $this->assertEquals($expected, $actual);


        //使用回调函数
        $array = array(
            array('name' => 'iPhone 6', 'brand' => 'Apple', 'type' => 'phone'),
            array('name' => 'iPhone 5', 'brand' => 'Apple', 'type' => 'phone'),
            array('name' => 'Apple Watch', 'brand' => 'Apple', 'type' => 'watch'),
            array('name' => 'Galaxy S6', 'brand' => 'Samsung', 'type' => 'phone'),
            array('name' => 'Galaxy Gear', 'brand' => 'Samsung', 'type' => 'watch'),
        );
        $expected = array(
            array('name' => 'iPhone 6', 'brand' => 'Apple', 'type' => 'phone'),
            2=>array('name' => 'Apple Watch', 'brand' => 'Apple', 'type' => 'watch'),
            array('name' => 'Galaxy S6', 'brand' => 'Samsung', 'type' => 'phone'),
            array('name' => 'Galaxy Gear', 'brand' => 'Samsung', 'type' => 'watch'),
        );
        $actual = Arr::unique($array, function($item){
            return $item['brand'] . $item['type'];
        });
        $this->assertEquals($expected, $actual);
    }


    public function testUniqueStrict()
    {
        $array = array(1, '1');
        $expected = array(1, '1');
        $actual = Arr::uniqueStrict($array);
        $this->assertEquals($expected, $actual);
    }


    public function testFilter()
    {
        //测试使用回调函数的情况
        $array = array(1, 2, 3, 4);
        $expected = array(2=>3, 4);
        $actual = Arr::filter($array, function($item){
            return $item > 2;
        });
        $this->assertEquals($expected, $actual);


        //测试只有一个参数的情况
        $array = array(1, 2, 3, null, false, '', 0, array());
        $expected = array(1, 2, 3);
        $actual = Arr::filter($array);
        $this->assertEquals($expected, $actual);
    }


    public function testWhereScript()
    {
        //参数类型不一致的情况
        $array = array(
            array('account_id' => 1, 'product' => 'Desk'),
            array('account_id' => 2, 'product' => 'Chair'),
        );
        $expected = array();
        $actual = Arr::whereStrict($array, 'account_id', '1');

        $this->assertEquals($expected, $actual);


        //参数类型一致的情况
        $array = array(
            array('account_id' => 1, 'product' => 'Desk'),
            array('account_id' => 2, 'product' => 'Chair'),
        );
        $expected = array(array('account_id' => 1, 'product' => 'Desk'));
        $actual = Arr::whereStrict($array, 'account_id', 1);

        $this->assertEquals($expected, $actual);
    }


    public function testWhereIn()
    {
        $array = array(
            array('product' => 'Desk', 'price' => 200),
            array('product' => 'Chair', 'price' => 100),
            array('product' => 'Bookcase', 'price' => 150),
            array('product' => 'Door', 'price' => 100),
        );
        $expected = array(
            array('product' => 'Desk', 'price' => 200),
            2=>array('product' => 'Bookcase', 'price' => 150),
        );
        $actual = Arr::whereIn($array, 'price', array(150, 200));
        $this->assertEquals($expected, $actual);
    }


    public function testWhereInStrict()
    {
        $array = array(
            array('product' => 'Desk', 'price' => 200),
            array('product' => 'Chair', 'price' => 100),
            array('product' => 'Bookcase', 'price' => 150),
            array('product' => 'Door', 'price' => 100),
        );
        $expected = array(
            array('product' => 'Desk', 'price' => 200),
        );
        $actual = Arr::whereInStrict($array, 'price', array('150', 200));
        $this->assertEquals($expected, $actual);
    }


    public function testWhereInstanceOf()
    {
        $array = array(
            new static(),
            new static(),
            new CollectionTest(),
        );
        $expected = array(new static(), new static());
        $actual = Arr::whereInstanceOf($array, get_class());
        $this->assertEquals($expected, $actual);
    }

    public function testWhereNotIn()
    {
        $array = array(
            array('product' => 'Desk', 'price' => 200),
            array('product' => 'Chair', 'price' => 100),
            array('product' => 'Bookcase', 'price' => 150),
            array('product' => 'Door', 'price' => 100),
        );
        $expected = array(
            1=>array('product' => 'Chair', 'price' => 100),
            3=>array('product' => 'Door', 'price' => 100),
        );
        $actual = Arr::whereNotIn($array, 'price', array(150, 200));
        $this->assertEquals($expected, $actual);
    }


    public function testWhereNotInStrict()
    {
        $array = array(
            array('product' => 'Desk', 'price' => 200),
            array('product' => 'Chair', 'price' => 100),
            array('product' => 'Bookcase', 'price' => 150),
            array('product' => 'Door', 'price' => 100),
        );
        $expected = array(
            1=>array('product' => 'Chair', 'price' => 100),
            array('product' => 'Bookcase', 'price' => 150),
            array('product' => 'Door', 'price' => 100),
        );
        $actual = Arr::whereNotInStrict($array, 'price', array('150', 200));
        $this->assertEquals($expected, $actual);
    }




}

