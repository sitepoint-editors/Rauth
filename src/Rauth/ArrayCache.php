<?php

namespace SitePoint\Rauth;

final class ArrayCache implements Cache
{
    private $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function get(string $key)
    {
        return $this->data[$key] ?? null;
    }

    public function set(string $key, $value) : Cache
    {
        $this->data[$key] = $value;
        return $this;
    }

    public function has(string $key) : bool
    {
        return isset($this->data[$key]);
    }
}
