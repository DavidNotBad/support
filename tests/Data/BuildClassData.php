<?php
/**
 * Created by PhpStorm.
 * User: David
 * Date: 2018/8/6
 * Time: 9:37
 */

namespace DavidNotBad\Support\Tests\Data;


class BuildClassData
{
    public function toUpperInBuildTest($item)
    {
        return strtoupper($item);
    }

    public function toLowerInBuildTest($item)
    {
        return strtolower($item);
    }

    protected static function title($item)
    {
        return ucfirst($item);
    }
}