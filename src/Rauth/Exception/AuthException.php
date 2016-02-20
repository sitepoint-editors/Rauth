<?php

namespace SitePoint\Rauth\Exception;

class AuthException extends \Exception
{
    private $reasons = [];

    /** @var string */
    private $type = '';

    public function __construct($type = '')
    {
        $this->setType($type);
    }

    /**
     * Returns the collection of reasons. Can be empty.
     *
     * @return array
     */
    public function getReasons() : array
    {
        return $this->reasons;
    }

    /**
     * Adds a reason to the reason bag in the exception
     *
     * @param Reason $reason
     * @return AuthException
     */
    public function addReason(Reason $reason) : AuthException
    {
        $this->reasons[] = $reason;
        return $this;
    }

    /**
     * Checks if any reasons have been defined in the exception
     *
     * @return bool
     */
    public function hasReasons() : bool
    {
        return (count($this->reasons) > 0);
    }

    /**
     * Sets the context in which the exception was thrown
     *
     * Example "ban" or "and"
     *
     * @param string $type
     * @return AuthException
     */
    public function setType(string $type) : AuthException
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Returns context in which exception occurred.
     *
     * Example "ban" or "and"
     *
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }
}
