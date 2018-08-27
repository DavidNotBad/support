<?php
namespace DavidNotBad\Support\Tests\Data;
use \ArrayAccess;
use DavidNotBad\Support\Contracts\Arrayable;

class Access implements ArrayAccess, Arrayable
{
    protected $array = array();

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->array);
    }

    public function offsetGet($offset)
    {
        return $this->array[$offset];
    }

    public function offsetSet($offset, $value)
    {
        is_null($offset) ? $this->array[] = $value : $this->array[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->array[$offset]);
    }

    public function toArray()
    {
        return (array) $this->array;
    }


}