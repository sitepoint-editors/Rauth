<?php

namespace SitePoint;

use SitePoint\Rauth\Cache;

interface RauthInterface
{
    public function authorize($class, string $method = null, array $attributed = []) : bool;

    public function extractAuth($class, string $method = null) : array;

    public function setDefaultMode(string $mode = null) : RauthInterface;

    public function setCache(Cache $cache) : RauthInterface;
}
