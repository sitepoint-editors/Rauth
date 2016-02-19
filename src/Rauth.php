<?php

namespace SitePoint;

use SitePoint\Rauth\ArrayCache;
use SitePoint\Rauth\Cache;

class Rauth implements RauthInterface
{
    const REGEX = '/@auth-([\w-]+)\s?\s(.+)/';

    const MODE_OR = 'or';
    const MODE_AND = 'and';
    const MODE_NONE = 'none';

    const MODES = [
        self::MODE_AND,
        self::MODE_NONE,
        self::MODE_OR,
    ];

    /** @var string */
    private $defaultMode = self::MODE_OR;

    /** @var Cache */
    private $cache;

    public function __construct(Cache $c = null)
    {
        if ($c) {
            $this->cache = $c;
        }
    }

    /**
     * Set a default mode for auth blocks without one defined.
     *
     * Default is MODE_OR
     *
     * @param string $mode
     * @return RauthInterface
     */
    public function setDefaultMode(string $mode = null) : RauthInterface
    {
        if (!in_array($mode, self::MODES)) {
            throw new \InvalidArgumentException(
                'Mode ' . $mode . ' not accepted!'
            );
        }
        $this->defaultMode = $mode;

        return $this;
    }

    /**
     * Inject Cache instance
     *
     * @param Cache $c
     * @return RauthInterface
     */
    public function setCache(Cache $c) : RauthInterface
    {
        $this->cache = $c;

        return $this;
    }

    /**
     * Only used by the class.
     *
     * Could have user property directly, but having default cache is convenient
     *
     * @internal
     * @return null|Cache
     */
    private function getCache()
    {
        if ($this->cache === null) {
            $this->setCache(new ArrayCache());
        }

        return $this->cache;
    }

    /**
     * Used to extract the @auth blocks from a class or method
     *
     * The auth prefix is stripped, and the remaining values are saved as
     * key => value pairs.
     *
     * @param $class
     * @param string|null $method
     * @return array
     */
    public function extractAuth($class, string $method = null) : array
    {
        if (!is_string($class) && !is_object($class)) {
            throw new \InvalidArgumentException(
                'Class must be string or object!'
            );
        }

        $className = (is_string($class)) ? $class : get_class($class);
        $sig = ($method) ? $className . '::' . $method : $className;

        // Class auths haven't been cached yet
        if (!$this->getCache()->has($className)) {
            $r = new \ReflectionClass($className);
            preg_match_all(self::REGEX, $r->getDocComment(), $matchC);
            $this->getCache()->set($className, $this->normalize((array)$matchC));
        }

        // Method auths haven't been cached yet
        if (!$this->getCache()->has($sig)) {
            $r = new \ReflectionMethod($className, $method);
            preg_match_all(self::REGEX, $r->getDocComment(), $matchC);
            $this->getCache()->set($sig, $this->normalize((array)$matchC));
        }

        return ($this->getCache()->get($sig) == [])
            ? $this->getCache()->get($className)
            : $this->getCache()->get($sig);
    }

    /**
     * Turns a pregexed array of auth blocks into a decent array
     *
     * Internal use only - @see Rauth::extractAuth
     *
     * @internal
     * @param array $matches
     * @return array
     */
    private function normalize(array $matches) : array
    {
        $keys = $matches[1];
        $values = $matches[2];

        $return = [];

        foreach ($keys as $i => $key) {
            $key = strtolower(trim($key));

            if ($key == 'mode') {
                $value = strtolower($values[$i]);
            } else {
                $value = array_map(
                    function ($el) {
                        return trim($el, ', ');
                    },
                    explode(',', $values[$i])
                );
            }
            $return[$key] = $value;
        }

        return $return;
    }


    /**
     * Either passes or fails an authorization attempt.
     *
     * The first two arguments are the class/method pair to inspect for @auth
     * tags, and `$attr` are attributes to compare the @auths against.
     *
     * Depending on the currently selected mode (either default - for that
     * you should @see Rauth::setDefaultMode, or defined in the @auths), it will
     * evaluate the arrays against one another and come to a conclusion.
     *
     * @param $class
     * @param string|null $method
     * @param array $attr
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function authorize(
        $class,
        string $method = null,
        array $attr = []
    ) : bool {
    
        $auth = $this->extractAuth($class, $method);

        // Class / method has no rules - allow all
        if (empty($auth)) {
            return true;
        }

        // Store mode, remove from auth array
        $mode = $auth['mode'] ?? $this->defaultMode;
        unset($auth['mode']);

        // Handle bans, remove them from auth
        foreach ($auth as $set => $values) {
            if (strpos($set, 'ban-') === 0) {
                $key = str_replace('ban-', '', $set);
                if (isset($attr[$key]) && array_intersect(
                    (array)$attr[$key],
                    $values
                )
                ) {
                    return false;
                }
                unset($auth[$set]);
            }
        }

        switch ($mode) {
            case self::MODE_AND:
                $required = 0;
                $matches = 0;
                // All values in all arrays must match
                foreach ($auth as $set => $values) {
                    if (!isset($attr[$set])) {
                        return false;
                    }
                    $attr[$set] = (array)$attr[$set];
                    $required++;
                    sort($values);
                    sort($attr[$set]);
                    $matches += (int)($values == $attr[$set]);
                }

                return $required == $matches;
            case self::MODE_NONE:
                // There must be no overlap between any of the array values

                foreach ($auth as $set => $values) {
                    if (isset($attr[$set]) && count(
                        array_intersect(
                            (array)$attr[$set],
                            $values
                        )
                    )
                    ) {
                        return false;
                    }
                }

                return true;
            case self::MODE_OR:
                // At least one match must be present
                foreach ($auth as $set => $values) {
                    if (isset($attr[$set]) && count(
                        array_intersect(
                            (array)$attr[$set],
                            $values
                        )
                    )
                    ) {
                        return true;
                    }
                }

                return false;
            default:
                throw new \InvalidArgumentException('Durrrr');
        }

    }
}
