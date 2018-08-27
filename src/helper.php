<?php

use DavidNotBad\Support\ArraySet\Collection;

if(! function_exists('collection')) {
    function collection($array=array())
    {
        return new Collection($array);
    }
}





