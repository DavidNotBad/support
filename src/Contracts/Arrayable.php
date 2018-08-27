<?php
namespace DavidNotBad\Support\Contracts;


interface Arrayable
{
    /**
     * 获取数组的实例
     *
     * @return array
     */
    public function toArray();
}