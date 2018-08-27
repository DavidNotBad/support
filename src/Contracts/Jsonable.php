<?php
namespace DavidNotBad\Support\Contracts;


interface Jsonable
{
    /**
     * 将对象转换成转换成json格式
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0);
}