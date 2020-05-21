<?php
namespace App\Models\ModelTrait;

trait ThisUserTrait
{
    protected $user = null;

    public function __construct()
    {
        $user                = auth('users')->user();
        $user && $this->user = $user;
    }
}
