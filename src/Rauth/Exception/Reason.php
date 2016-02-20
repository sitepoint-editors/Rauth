<?php

namespace SitePoint\Rauth\Exception;

class Reason
{
    /** @var string */
    public $group;

    /** @var array */
    public $has;

    /** @var array */
    public $needs;

    /**
     * Reason constructor.
     *
     * Note that the word "group" below does not mean a group the user belongs
     * to, for example, but a group of attributes required / applied
     *
     * @param string $group "Auth-" group that caused the problem
     * @param array $has Existing attributes in that group
     * @param array $needs Needed / forbidden attributes in that group
     */
    public function __construct(string $group, array $has, array $needs)
    {
        $this->group = $group;
        $this->has = $has;
        $this->needs = $needs;
    }
}
