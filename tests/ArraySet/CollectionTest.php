<?php
namespace DavidNotBad\Support\Tests\ArraySet;
use DavidNotBad\Support\ArraySet\Collection;
use DavidNotBad\Support\Env\PHPVersion;
use DavidNotBad\Support\Tests\Data\Access;
use DavidNotBad\Support\Tests\Data\Json;
use PHPUnit\Framework\TestCase;


class CollectionTest extends TestCase
{

    public function testNewCollection()
    {
        //测试实例化类
        $collection = new Collection(array('a','b'));

        $expected = array('a','b');
        $actual = $collection->all();

        $this->assertEquals($expected,$actual);


        //测试辅助函数
        $collection = collection(array('a'));

        $expected = array('a');
        $actual = $collection->all();

        $this->assertEquals($expected,$actual);


        //测试传递的是自身的实例
        $collection = collection(collection(array('c')));

        $expected = array('c');
        $actual = $collection->all();

        $this->assertEquals($expected,$actual);


        //测试传递的是Arrayable的实例
        $arrayAble = new Access();
        $arrayAble[] = 'a';

        $collection = collection($arrayAble);

        $expected = array('a');
        $actual = $collection->all();

        $this->assertEquals($expected,$actual);


        //测试传递的是Jsonable的实例
        $json = new Json();

        $collection = collection($json);

        $expected = array('a'=>'apple');
        $actual = $collection->all();

        $this->assertEquals($expected,$actual);
    }

    public function macro($data)
    {
        return array_map('strtoupper', $data);
    }

    /**
     * @throws \Exception
     */
    public function testMacro()
    {
        //php5.4前的写法
        if (PHPVersion::lt('5.4')) {
            Collection::macro('test', array($this, 'macro'));

            $expected = array('a'=>'APPLE');

            $collection = collection(array('a'=>'apple'));
            $actual = call_user_func(array($collection, 'test'), $collection->all());

            $this->assertEquals($expected,$actual);
        }else{
            Collection::macro('test', function(){

                return array_map('strtoupper', $this->items);
            });

            $expected = array('a'=>'APPLE');

            $collection = collection(array('a'=>'apple'));
            $actual = call_user_func(array($collection, 'test'), $collection->all());

            $this->assertEquals($expected,$actual);
        }

    }

    public function testMake()
    {
        $instance = Collection::make();
        $this->assertTrue($instance instanceof Collection);
    }

    public function testWrap()
    {
        $instance = Collection::make(array('a'));
        $actual = Collection::wrap($instance)->all();
        $expected = array('a');

        $this->assertEquals($expected,$actual);
    }

    public function testUnwrap()
    {
        $expected = array('John Doe');
        $actual = Collection::unwrap(Collection::make('John Doe'));
        $this->assertEquals($expected, $actual);


        $expected = array('John Doe');
        $actual = Collection::unwrap($expected);
        $this->assertEquals($expected, $actual);


        $expected = 'John Doe';
        $actual = Collection::unwrap($expected);
        $this->assertEquals($expected, $actual);
    }

    public function testCount()
    {
        $expected = 2;
        $actual = Collection::make(array('a', 'b'))->count();

        $this->assertEquals($expected, $actual);
    }


    public function testSum()
    {
        $expected = 55;
        $actual = Collection::make(range(1, 10))->sum();

        $this->assertEquals($expected, $actual);


        //测试点语法的情况
        $array = array(
            array('price'=>1),
            array('price'=>2),
            array('price'=>3),
            array('price'=>4),
        );
        $expected = 10;
        $actual = Collection::make($array)->sum('*.price');

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
        $actual = Collection::make($array)->sum('a.*.price');

        $this->assertEquals($expected, $actual);
    }


    public function testReduce()
    {
        $expected = 3;
        $actual = collection(array(1, 2))->reduce(function($carry, $item){
            return $carry + $item;
        }, 0);

        $this->assertEquals($expected, $actual);
    }

    public function testChunk()
    {
        $expected = array(array(1, 2), array(2=>3, 3=>4));
        $actual = collection(range(1, 4))->chunk('2')->toArray();

        $this->assertEquals($expected, $actual);
    }

    public function testCollapse()
    {
        $array = array(array(1, 2), array(3, 4));
        $expected = range(1, 4);
        $actual = collection($array)->collapse()->all();

        $this->assertEquals($expected, $actual);
    }

    public function testCombine()
    {
        $expected = array('a'=>'apple', 'b'=>'banana');
        $actual = collection(array('a', 'b'))->combine(array('apple', 'banana'))->all();

        $this->assertEquals($expected, $actual);
    }

    public function testTimes()
    {
        $expected = \collection(array(2, 4, 6, 8, 10));
        $actual = Collection::times(5, function ($number) {
            return $number * 2;
        });
        $this->assertEquals($expected, $actual);
    }

    public function testAll()
    {
        $expected = array(2, 4);
        $actual = \collection(array(2, 4))->all();
        $this->assertEquals($expected, $actual);
    }


    public function testAvg()
    {
        $array = array(array('foo' => 10), array('foo' => 10), array('foo' => 20), array('foo' => 40));
        $expected = 20;
        $actual = collection($array)->avg('foo');
        var_dump($actual);
        $this->assertEquals($expected, $actual);


        $array = array(1, 1, 2, 4);
        $expected = 2;
        $actual = collection($array)->avg();
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
        $actual = \collection($array)->median('foo');
        $this->assertEquals($expected, $actual);


        $array = array(1, 1, 2, 4);
        $expected = 1.5;
        $actual = \collection($array)->median();
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
        $actual = collection($array)->mode('foo');
        $this->assertEquals($expected, $actual);



        $array = array(1, 1, 2, 4);
        $expected = array(1);
        $actual = collection($array)->mode();
        $this->assertEquals($expected, $actual);
    }


    public function testCrossJoin()
    {
        //只有两个参数
        $array = array(1, 2);
        $expected = \collection(array(
            array(1, 'a'),
            array(1, 'b'),
            array(2, 'a'),
            array(2, 'b'),
        ));
        $actual = \collection($array)->crossJoin(array('a', 'b'));
        $this->assertEquals($expected, $actual);


        //三个参数
        $array = array(1, 2);
        $expected = \collection(array(
            array(1, 'a', 'I'),
            array(1, 'a', 'II'),
            array(1, 'b', 'I'),
            array(1, 'b', 'II'),
            array(2, 'a', 'I'),
            array(2, 'a', 'II'),
            array(2, 'b', 'I'),
            array(2, 'b', 'II'),
        ));
        $actual = \collection($array)->crossJoin(array('a', 'b'), array('I', 'II'));
        $this->assertEquals($expected, $actual);
    }

    public function testEach()
    {
        $instance = $this;
        \collection(array('a'))->each(function($item, $key)use($instance){
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
        \collection($array)->eachSpread(function($name, $age, $key)use($instance){
            $instance->assertEquals('John Doe', $name);
            $instance->assertEquals(35, $age);
            $instance->assertEquals(0, $key);
        });
    }

    public function testEvery()
    {
        //参数二是回调函数
        $array = array(1, 2, 3, 4);
        $this->assertFalse(collection($array)->every(function($item){
            return $item > 2;
        }));


        //参数二是一个字符串, 满足的情况
        $array = array(
            array('a' => true),
            array('a' => true),
        );
        $this->assertTrue(collection($array)->every('a'));


        //参数二是一个字符串, 不满足的情况
        $array = array(
            array('a' => true),
            array('a' => false),
        );
        $this->assertFalse(collection($array)->every('a'));


        //参数二是一个字符串, 不满足的情况
        $array = $collection = array(
            array('product' => 'Desk', 'price' => 100),
            array('product' => 'Chair', 'price' => 100),
        );
        $this->assertFalse(collection($array)->every('product', 'Desk'));
        $this->assertTrue(collection($array)->every('price', 100));
        $this->assertFalse(collection($array)->every('price', '>', 100));
    }


    public function testExcept()
    {
        //删除数组中的某个成员, 第二个参数为数组
        $array = array('name' => 'Desk', 'price' => 100);
        $expected = collection(array('name'=>'Desk'));
        $actual = collection($array)->except(array('price'));

        $this->assertEquals($expected, $actual);


        //删除数组中的某个成员, 第二个参数为字符串
        $array = $temp = array('name' => 'Desk', 'price' => 100);
        $expected = \collection(array('name'=>'Desk'));
        $actual = collection($array)->except('price');

        $this->assertEquals($expected, $actual);

        //测试是否改变原数组, 不改变为正确的
        $this->assertEquals($temp, $array);
    }


    public function testFilter()
    {
        //测试使用回调函数的情况
        $array = array(1, 2, 3, 4);
        $expected = collection(array(2=>3, 4));
        $actual = collection($array)->filter(function($item){
            return $item > 2;
        });
        $this->assertEquals($expected, $actual);


        //测试只有一个参数的情况
        $array = array(1, 2, 3, null, false, '', 0, array());
        $expected = collection(array(1, 2, 3));
        $actual = collection($array)->filter();
        $this->assertEquals($expected, $actual);
    }

    public function testWhere()
    {
        //测试只有三个参数的情况
        $array = array(
            array('account_id' => 1, 'product' => 'Desk'),
            array('account_id' => 2, 'product' => 'Chair'),
        );
        $expected = \collection(array(array('account_id' => 1, 'product' => 'Desk')));
        $actual = collection($array)->where('account_id', 1);

        $this->assertEquals($expected, $actual);


        //测试四个参数的情况
        $array = array(
            array('account_id' => 1, 'product' => 'Desk'),
            array('account_id' => 2, 'product' => 'Chair'),
        );
        $expected = \collection(array(array('account_id' => 1, 'product' => 'Desk')));
        $actual = collection($array)->where('account_id', '<', 2);

        $this->assertEquals($expected, $actual);
    }



}