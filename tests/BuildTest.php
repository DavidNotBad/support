<?php
namespace DavidNotBad\Support\Tests;
use DavidNotBad\Support\Build;
use DavidNotBad\Support\Env\PHPVersion;
use PHPUnit\Framework\TestCase;

/**
 * Class BuildTest
 * @package DavidNotBad\Support\Tests\ArraySet
 */
class BuildTest extends TestCase
{
    /**
     * @param $item
     * @return string
     */
    public function toUpperInBuildTest($item)
    {
        return strtoupper($item);
    }

    /**
     * @throws \Exception
     */
    public function testBind()
    {
        if(PHPVersion::lt('5.4')) {
            //php5.5以下版本不支持ClassName::class的语法, 使用字符串的命名空间代替
            //或者使用哪个get_class函数得到命名空间
            $class = 'DavidNotBad\Support\Tests\Data\BuildData';
            //对于第二个参数, php5.4中的匿名函数没有bind和bindTo方法, 需要使用数组的形式进行传递参数
            Build::bind('toUpper', array($this, 'toUpperInBuildTest'), $class);

            $array = array('a'=>'apple', 'b'=>'banana');
            foreach ($array as &$item) {
                $item = call_user_func(array($class, 'toUpper'), $item);
            }

            $expected = array('a'=>'APPLE', 'b'=>'BANANA');
            $actual = $array;

            $this->assertEquals($expected, $actual);
        }

        if (PHPVersion::between('5.4', '5.5')) {
            //php5.5以下版本不支持ClassName::class的语法, 使用字符串的命名空间代替
            //或者使用哪个get_class函数得到命名空间
            $class = 'DavidNotBad\Support\Tests\Data\BuildData';
            Build::bind('toUpper', function($item){
                return strtoupper($item);
            }, $class);

            $array = array('a'=>'apple', 'b'=>'banana');
            foreach ($array as &$item) {
                $item = call_user_func(array($class, 'toUpper'), $item);
            }

            $expected = array('a'=>'APPLE', 'b'=>'BANANA');
            $actual = $array;

            $this->assertEquals($expected, $actual);
        }

        if (PHPVersion::ge('5.5')) {
            //php5.6+支持ClassName::class的形式获取类的命名空间
            //使用ClassName::class的形式在低版本中是会报语法错误, 所以还是使用了字符串的方式
            $class = 'DavidNotBad\Support\Tests\Data\BuildData';
            Build::bind('toUpper', function($item){
                return strtoupper($item);
            }, $class);

            $array = array('a'=>'apple', 'b'=>'banana');
            foreach ($array as &$item) {
                $item = call_user_func(array($class, 'toUpper'), $item);
            }

            $expected = array('a'=>'APPLE', 'b'=>'BANANA');
            $actual = $array;

            $this->assertEquals($expected, $actual);
        }

    }

    /**
     * @throws \Exception
     */
    public function testHasBind()
    {
        $class = 'DavidNotBad\Support\Tests\Data\BuildData';
        Build::bind('toUpper', array($this, 'toUpperInBuildTest'), $class);

        $this->assertTrue(Build::hasBind('toUpper', $class));
    }

    /**
     * @throws \Exception
     */
    public function testCallStatic()
    {
        $class = 'DavidNotBad\Support\Tests\Data\BuildData';
        Build::bind('toUpper', array($this, 'toUpperInBuildTest'), $class);

        $expected = 'APPLE';
        $actual = Build::callStatic('toUpper', 'apple', $class);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @throws \Exception
     */
    public function testCall()
    {
        $class = 'DavidNotBad\Support\Tests\Data\BuildData';
        Build::bind('toUpper', array($this, 'toUpperInBuildTest'), $class);

        $expected = 'APPLE';
        $actual = Build::call('toUpper', 'apple', $class);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function testBindClass()
    {
        $fromClass = 'DavidNotBad\Support\Tests\Data\BuildClassData';
        $toClass = 'DavidNotBad\Support\Tests\Data\BuildData';

        Build::bindClass($fromClass, $toClass);

        //测试动态方法toUpperInBuildTest
        $expected = 'APPLE';
        $actual = call_user_func(array(new $toClass(), 'toUpperInBuildTest'), 'apple');

        $this->assertEquals($expected, $actual);

        //测试动态方法toLowerInBuildTest
        $expected = 'apple';
        $actual = call_user_func(array(new $toClass(), 'toLowerInBuildTest'), 'APPLE');

        $this->assertEquals($expected, $actual);

        //测试静态方法
        $expected = 'David yang';
        $actual = call_user_func(array($toClass, 'title'), 'david yang');

        $this->assertEquals($expected, $actual);
    }


}