<?php

namespace App\Concrete;

class PrototypeConcrete
{
    private ?int $key = null;

    public function setKey(int $key)
    {
        $this->key = $key;
    }

    public function showKey()
    {
        _debug([
            'key' => $this->key,
        ]);
    }
}
