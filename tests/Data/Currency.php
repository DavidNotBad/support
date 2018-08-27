<?php
/**
 * Created by PhpStorm.
 * User: David
 * Date: 2018/8/16
 * Time: 15:33
 */

namespace DavidNotBad\Support\Tests\Data;

class Currency
{
    /**
     * Create a new currency instance.
     *
     * @param  string  $code
     * @return void
     */
    function __construct($code)
    {
        $this->code = $code;
    }
}