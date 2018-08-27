<?php
namespace DavidNotBad\Support\Env;
use DavidNotBad\Support\Exception\InvalidArgumentException;


/**
 * php版本相关
 * 
 * Class Version
 * @package DavidNotBad\Support\Env
 */
class PHPVersion
{
    /**
     * 获取当前的php版本, 使用点语法
     *
     * @return string
     */
    public static function get()
    {
        return PHP_VERSION;
    }

    /**
     * 获取数字类型的PHP版本
     *
     * @return float|int
     */
    public static function id()
    {
        static::setConstant();
        return PHP_VERSION_ID;
    }

    /**
     * 低版本的php设置版本相关常量
     */
    public static function setConstant()
    {
        //当PHP版本小于5.2.7
        if (!defined('PHP_VERSION_ID')) {
            $version = explode('.', PHP_VERSION);

            define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
            define('PHP_MAJOR_VERSION',   $version[0]);
            define('PHP_MINOR_VERSION',   $version[1]);
            define('PHP_RELEASE_VERSION', $version[2]);
            define('PHP_EXTRA_VERSION', isset($version[3]) ? $version[3] : '');
        }
    }

    /**
     * php版本匹配
     *
     * @param $version
     * @param string $operator
     * @return mixed
     * @throws InvalidArgumentException
     */
    public static function compare($version, $operator='>=')
    {
        $allow = array('<', '<=', '>', '>=', '==', '=', '!=', '<>');
        if(in_array($operator, $allow)) {
            return version_compare(static::get(), $version, $operator);
        }

        $operator = strtolower(trim($operator));
        $allow = array('le', 'gt', 'ge', 'eq', 'ne');
        if(in_array($operator, $allow)) {
            return version_compare(static::get(), $version, $operator);
        }

        throw new InvalidArgumentException('参数operator的值错误');
    }

    /**
     * 判断当前PHP版本是否等于指定版本
     *
     * @param $version
     * @return mixed
     * @throws InvalidArgumentException
     */
    public static function eq($version)
    {
        return static::compare($version, '==');
    }

    /**
     * 判断当前PHP版本是否不等于指定版本
     *
     * @param $version
     * @return mixed
     * @throws InvalidArgumentException
     */
    public static function ne($version)
    {
        return static::compare($version, '!=');
    }

    /**
     * 判断当前PHP版本是否大于指定版本
     *
     * @param $version
     * @return mixed
     * @throws InvalidArgumentException
     */
    public static function gt($version)
    {
        return static::compare($version, '>');
    }

    /**
     * 判断当前php版本是否小于指定版本
     *
     * @param $version
     * @return mixed
     * @throws InvalidArgumentException
     */
    public static function lt($version)
    {
        return static::compare($version, '<');
    }

    /**
     * 判断当前PHP版本是否大于或等于指定版本
     *
     * @param $version
     * @return mixed
     * @throws InvalidArgumentException
     */
    public static function ge($version)
    {
        return static::compare($version);
    }

    /**
     * 判断当前PHP版本是否小于或等于指定版本
     *
     * @param $version
     * @return mixed
     * @throws InvalidArgumentException
     */
    public static function le($version)
    {
        return static::compare($version, '<=');
    }


    /**
     * 判断当前php版本是否在指定的范围内
     *
     * @param $lower
     * @param $upper
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function between($lower, $upper)
    {
        return static::ge($lower) && static::lt($upper);
    }

}

