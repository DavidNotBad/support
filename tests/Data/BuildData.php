<?php
/**
 * Created by PhpStorm.
 * User: David
 * Date: 2018/8/4
 * Time: 11:38
 */

namespace DavidNotBad\Support\Tests\Data;

use DavidNotBad\Support\Build;

class BuildData
{
    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        $arguments[] = $this;
        return Build::call($name, $arguments, new static());
    }


    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws \Exception
     */
    public static function __callStatic($name, $arguments)
    {
        $instance = new static();
        $arguments[] = $instance;
        return Build::callStatic($name, $arguments, $instance);
    }
}