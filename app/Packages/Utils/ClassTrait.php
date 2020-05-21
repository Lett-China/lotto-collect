<?php
namespace App\Packages\Utils;

trait ClassTrait
{
    public function __call($method, $parameters)
    {
        return $this->{$method}(...$parameters);
    }

    public static function __callStatic($method, $parameters)
    {
        return (new static )->{$method}(...$parameters);
    }
}
