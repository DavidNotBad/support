<?php
namespace DavidNotBad\Support;

use \Closure;
use \Exception;
use \ReflectionClass;
use \ReflectionMethod;

/**
 * Class Build
 * @package DavidNotBad\Support
 */
class Build
{
    /**
     * The registered string macros.
     *
     * @var array
     */
    protected static $macros = array();

    /**
     * Register a custom macro.
     *
     * @param  string $name
     * @param  array|callable|ReflectionMethod $macro
     *
     * @param $className
     * @return void
     * @throws Exception
     */
    public static function bind($name, $macro, $className)
    {
        $className = static::classNameHandle($className);
        if($macro instanceof Closure && ! method_exists($macro, 'bind')) {
            throw new Exception(
                '该版本php不支持匿名函数的参数绑定, 请把参数$macro设置成数组的形式, e.g. array($className, "methodName")'
            );
        }
        static::$macros[$className][$name] = $macro;
    }

    /**
     * 处理类名称
     * @param $className
     * @return null|string
     * @throws Exception
     */
    protected static function classNameHandle($className)
    {
        return is_object($className) ? get_class($className) : $className;
    }


    /**
     * @param $fromClass
     * @param null $toClass
     * @throws Exception
     * @throws \ReflectionException
     */
    public static function bindClass($fromClass, $toClass)
    {
        $fromClass = static::classNameHandle($fromClass);
        $toClass = static::classNameHandle($toClass);

        $reflectionClass = new ReflectionClass($fromClass);
        $methods = $reflectionClass->getMethods(
            ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED
        );

        foreach ($methods as $method) {
            $method->setAccessible(true);

            static::bind($method->name, array($method, $fromClass), $toClass);
        }
    }

    /**
     * Checks if macro is registered.
     *
     * @param  string $name
     * @param $className
     * @return bool
     * @throws Exception
     */
    public static function hasBind($name, $className)
    {
        $className = static::classNameHandle($className);
        return isset(static::$macros[$className][$name]);
    }

    /**
     * Dynamically handle calls to the class.
     *
     * @param  $method
     * @param  $parameters
     * @param $className
     * @return mixed
     *
     * @throws Exception
     */
    public static function callStatic($method, $parameters, $className)
    {
        return static::execute($method, $parameters, $className, function($macro, $className){
            return Closure::bind($macro, null, $className);
        });
    }

    /**
     * Dynamically handle calls to the class.
     *
     * @param  $method
     * @param  $parameters
     * @param $className
     * @return mixed
     *
     * @throws Exception
     */
    public static function call($method, $parameters, $className)
    {
        return static::execute($method, $parameters, $className, function($macro, $className){
            /**
             * @var $macro Closure
             */
            return $macro->bindTo($className, $className);
        });
    }

    /**
     * @param $method
     * @param $parameters
     * @param $className
     * @param $closureCall
     * @return mixed
     * @throws Exception
     */
    protected static function execute($method, $parameters, $className, $closureCall)
    {
        $method = (string) $method;
        $parameters = (array) $parameters;
        $class = $className;
        $className = static::classNameHandle($className);

        if (! static::hasBind($method, $className)) {
            throw new Exception(sprintf(
                '方法 %s::%s 不存在.', $className, $method
            ));
        }

        $macro = static::$macros[$className][$method];

        if ($macro instanceof Closure) {
            if(! method_exists($macro, 'bindTo')) {
                throw new Exception('该版本php不支持匿名函数的参数绑定');
            }
            return call_user_func_array($closureCall($macro, $class), $parameters);
        }

        if(isset($macro[0]) && $macro[0] instanceof ReflectionMethod) {
            /**
             * @var $reflectionMethod ReflectionMethod
             */
            $reflectionMethod = $macro[0];
            $object = isset($macro[1]) ? (is_string($macro[1]) ? new $macro[1]() : $macro[1]) : null;
            return $reflectionMethod->invokeArgs($object, $parameters);
        }

        return call_user_func_array($macro, $parameters);
    }


}