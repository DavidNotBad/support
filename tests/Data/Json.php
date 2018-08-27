<?php
namespace DavidNotBad\Support\Tests\Data;


use DavidNotBad\Support\Contracts\Jsonable;

class Json implements Jsonable
{
    /**
     * 将对象转换成转换成json格式
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode(array('a'=>'apple'));
    }

}