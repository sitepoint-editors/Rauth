<?php

namespace SitePoint\Rauth;

interface Cache
{
    public function get(string $key);

    public function set(string $key, $value) : Cache;

    public function has(string $key) : bool;
}
