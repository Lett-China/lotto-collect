<?php
namespace App\Models\ModelTrait;

trait ModelTrait
{
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    public function setUpdate($params = [])
    {
        $request = request();
        foreach ($params as $value) {
            isset($request->$value) && $this->$value = $request->$value;
        }
    }
}
