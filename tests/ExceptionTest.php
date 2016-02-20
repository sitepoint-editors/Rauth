<?php

namespace SitePoint\Rauth\Tests;
use SitePoint\Rauth;
use SitePoint\Rauth\Exception\Reason;
use SitePoint\Rauth\Exception\AuthException;

class ExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testBanException() {
        $r = new Rauth;
        try {
            $r->authorize(
                new ExampleClass(), 'anotherTest', ['groups' => 'blocked']
            );
        } catch (AuthException $e) {
            $this->assertTrue($e->hasReasons());
            $reasons = $e->getReasons();
            $this->assertEquals(1, count($reasons));
            $this->assertEquals('ban', $e->getType());
            /** @var Reason $reason */
            $reason = $reasons[0];
            $this->assertEquals('groups', $reason->group);
        }
    }

}
