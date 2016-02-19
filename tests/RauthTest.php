<?php

namespace SitePoint\Rauth\Tests;

use SitePoint\Rauth;

/**
 * Class RauthTest
 * @package SitePoint\Rauth\Tests
 */
class RauthTest extends \PHPUnit_Framework_TestCase
{
    private $class;

    public function setUp()
    {
        // The class has a 'requirements' property with correct values for all `auth` flags
        $this->class = new ExampleClass();
    }

    public function testSetCache()
    {
        $c = new Rauth\ArrayCache();
        $r = new Rauth($c);
        $this->assertInstanceOf('SitePoint\Rauth', $r);
    }

    public function testDefaultMode()
    {
        $r = new Rauth();
        $r->setDefaultMode(Rauth::MODE_AND);
        $r->setDefaultMode(Rauth::MODE_OR);
        $r->setDefaultMode(Rauth::MODE_NONE);
        $this->assertInstanceOf('SitePoint\Rauth', $r);
    }

    public function testDefaultModeError()
    {
        $r = new Rauth();
        $this->expectException('InvalidArgumentException');
        $r->setDefaultMode('foo');
    }

    public function authorizeDataProviderDefault()
    {
        $c1 = new ExampleClass();

        $set1 = [
            // Class-only tests
            [$c1, null, [], false], // anon user, class auths, fails
            [$c1, null, ['groups' => [], 'permissions' => []], false], // weirdly and badly configured user, cannot pass
            [$c1, null, ['groups' => ['admin']], true], // user with group admin should pass
            [$c1, null, ['permissions' => ['cook', 'clean']], true], // user with these permissions should pass
            [$c1, null, ['permissions' => ['cook', 'kill']], true], // user with these permissions should pass even though one doesn't exist
            [$c1, null, ['permissions' => ['cook', 'kill'], 'banana' => ['yellow']], true], // made up categories have no effect
            [$c1, null, ['groups' => 'admin'], true], // if the value is a string, it's fine, gets cast to array
        ];

        $method = 'someTest';
        $set1[] = [$c1, $method, [], false];
        $set1[] = [$c1, $method, ['groups' => [], 'permissions' => []], false];
        $set1[] = [$c1, $method, ['groups' => ['admin']], false];
        $set1[] = [$c1, $method, ['groups' => ['reg-user']], true];
        $set1[] = [$c1, $method, ['groups' => ['paying customers', 'reg-user']], true];
        $set1[] = [$c1, $method, ['groups' => ['paying customers']], true];
        $set1[] = [$c1, $method, ['groups' => 'paying customers'], true];
        $set1[] = [$c1, $method, ['permissions' => ['cook', 'clean']], false];
        $set1[] = [$c1, $method, ['permissions' => ['cook', 'kill']], false]; // user with these permissions should pass even though one doesn't exist
        $set1[] = [$c1, $method, ['permissions' => ['cook', 'kill'], 'banana' => ['yellow']], false]; // made up categories have no effect
        $set1[] = [$c1, $method, ['groups' => 'admin'], false];

        $method = 'someOtherTest';
        $set1[] = [$c1, $method, [], true];
        $set1[] = [$c1, $method, ['groups' => [], 'permissions' => []], true];
        $set1[] = [$c1, $method, ['groups' => ['admin']], true];
        $set1[] = [$c1, $method, ['groups' => ['reg-user']], true];
        $set1[] = [$c1, $method, ['groups' => ['paying customers', 'reg-user']], true];
        $set1[] = [$c1, $method, ['groups' => ['paying customers']], true];
        $set1[] = [$c1, $method, ['groups' => ['foo']], false];
        $set1[] = [$c1, $method, ['groups' => ['foo', 'bar']], false];
        $set1[] = [$c1, $method, ['groups' => 'paying customers'], true];
        $set1[] = [$c1, $method, ['groups' => 'foo'], false];
        $set1[] = [$c1, $method, ['permissions' => ['cook', 'clean']], true];
        $set1[] = [$c1, $method, ['permissions' => ['cook', 'kill']], true]; // user with these permissions should pass even though one doesn't exist
        $set1[] = [$c1, $method, ['permissions' => ['cook', 'kill'], 'banana' => ['yellow'], 'groups' => 'foo'], false]; // made up categories have no effect

        $method = 'anotherTest';
        $set1[] = [$c1, $method, [], false];
        $set1[] = [$c1, $method, ['groups' => [], 'permissions' => []], false];
        $set1[] = [$c1, $method, ['groups' => ['admin']], false];
        $set1[] = [$c1, $method, ['groups' => ['reg-user']], false];
        $set1[] = [$c1, $method, ['groups' => ['paying customers', 'reg-user']], false];
        $set1[] = [$c1, $method, ['groups' => ['paying customers']], false];
        $set1[] = [$c1, $method, ['groups' => ['foo']], false];
        $set1[] = [$c1, $method, ['groups' => ['foo', 'bar']], false];
        $set1[] = [$c1, $method, ['groups' => 'paying customers'], false];
        $set1[] = [$c1, $method, ['groups' => 'foo'], false];
        $set1[] = [$c1, $method, ['permissions' => ['cook', 'clean']], false];
        $set1[] = [$c1, $method, ['permissions' => ['cook', 'kill']], false]; // user with these permissions should pass even though one doesn't exist
        $set1[] = [$c1, $method, ['permissions' => ['cook', 'kill'], 'banana' => ['yellow'], 'groups' => 'foo'], false]; // made up categories have no effect
        $set1[] = [$c1, $method, ['banana' => 'whatever', 'name' => 'Bruno'], true];
        $set1[] = [$c1, $method, ['banana' => 'whatever', 'name' => 'Bruno', 'groups' => []], true];
        $set1[] = [$c1, $method, ['banana' => 'whatever', 'name' => 'Bruno', 'groups' => ['blocked']], false];
        $set1[] = [$c1, $method, ['name' => 'Bruno', 'groups' => []], false];
        $set1[] = [$c1, $method, ['banana' => 'whatever', 'groups' => []], false];
        
        $method = 'noAuthTest';
        $set1[] = [$c1, $method, [], false];
        $set1[] = [$c1, $method, ['groups' => [], 'permissions' => []], false];
        $set1[] = [$c1, $method, ['groups' => ['admin']], true];
        $set1[] = [$c1, $method, ['permissions' => ['cook', 'clean']], true];
        $set1[] = [$c1, $method, ['permissions' => ['cook', 'kill']], true];
        $set1[] = [$c1, $method, ['permissions' => ['cook', 'kill'], 'banana' => ['yellow']], true];
        $set1[] = [$c1, $method, ['groups' => 'admin'], true];

        $method = 'noDocblockTest';
        $set1[] = [$c1, $method, [], false];
        $set1[] = [$c1, $method, ['groups' => [], 'permissions' => []], false];
        $set1[] = [$c1, $method, ['groups' => ['admin']], true];
        $set1[] = [$c1, $method, ['permissions' => ['cook', 'clean']], true];
        $set1[] = [$c1, $method, ['permissions' => ['cook', 'kill']], true];
        $set1[] = [$c1, $method, ['permissions' => ['cook', 'kill'], 'banana' => ['yellow']], true];
        $set1[] = [$c1, $method, ['groups' => 'admin'], true];

        // ---------------------------------

        $set2 = [];
        $c2 = new ExampleClass2();
        $set2[] = [$c2, null, [], true];
        $set2[] = [$c2, null, ['something' => 'else', 'whatever' => ['here', 'there']], true];

        return array_merge($set1, $set2);
    }

    /**
     * @dataProvider authorizeDataProviderDefault
     * @param $class
     * @param $method
     * @param $attributes
     * @param $success
     * @throws \Exception
     */
    public function testAuthorizeDefault($class, $method, $attributes, $success)
    {
        $r = new Rauth();
        $this->assertTrue(
            $r->authorize($class, $method, $attributes) === $success
        );
    }

    public function testInvalidMode()
    {
        $r = new Rauth();
        $this->expectException('InvalidArgumentException');
        $r->authorize(new ExampleClass(), 'invalidModeTest', []);
    }

    public function testClassAuth()
    {
        $rauth = new Rauth();
        $this->assertEquals(
            $this->class->requirements['class'],
            $rauth->extractAuth($this->class)
        );
    }

    public function methodAuthProvider()
    {
        $rf = new \ReflectionClass(new ExampleClass());
        foreach ($rf->getMethods() as $method) {
            yield [$method->name];
        }
    }

    /**
     * @dataProvider methodAuthProvider
     * @param $method
     */
    public function testMethodAuths($method)
    {
        $rauth = new Rauth();
        $this->assertEquals(
            $this->class->requirements[$method],
            $rauth->extractAuth($this->class, $method)
        );
    }

    public function testInvalidClass()
    {
        $rauth = new Rauth();
        $this->expectException('InvalidArgumentException');
        $rauth->authorize([], 'whateverMethod', []);
    }
}

/**
 * Class ExampleClass
 * @package SitePoint\tests
 *
 * @auth-groups admin, reg-user,fools
 * @auth-permissions cook,clean, harvest
 */
class ExampleClass
{
    public $requirements = [
        'class' => [
            'groups' => ['admin', 'reg-user', 'fools'],
            'permissions' => ['cook', 'clean', 'harvest']
        ],
        'someTest' => [
            'groups' => ['reg-user', 'paying customers'],
            'mode' => 'or'
        ],
        'someOtherTest' => [
            'groups' => ['foo', 'bar', 'baz'],
            'mode' => 'none'
        ],
        'anotherTest' => [
            'banana' => ['whatever'],
            'name' => ['Bruno'],
            'ban-groups' => ['blocked'],
            'mode' => 'and'
        ],
        'noAuthTest' => [
            'groups' => ['admin', 'reg-user', 'fools'],
            'permissions' => ['cook', 'clean', 'harvest']
        ],
        'noDocblockTest' => [
            'groups' => ['admin', 'reg-user', 'fools'],
            'permissions' => ['cook', 'clean', 'harvest']
        ],
        'invalidModeTest' => [
            'mode' => 'foobar'
        ]
    ];

    /**
     * @auth-groups reg-user, paying customers
     * @auth-mode OR
     */
    public function someTest()
    {

    }

    /**
     * @auth-groups foo, bar, baz
     * @auth-mode NONE
     */
    public function someOtherTest()
    {

    }

    /**
     * @auth-banana whatever
     * @auth-name Bruno
     * @auth-ban-groups blocked
     * @auth-mode AND
     */
    public function anotherTest()
    {

    }

    /**
     * foobar
     */
    public function noAuthTest()
    {

    }

    public function noDocblockTest()
    {

    }

    /**
     * @auth-mode foobar
     */
    public function invalidModeTest()
    {

    }
}

/**
 * Class ExampleClass2
 * @package SitePoint\tests
 */
class ExampleClass2
{
    public $requirements = [
        'class' => [],
        'someTest' => [
            'groups' => ['reg-user', 'paying customers'],
            'mode' => 'or'
        ],
        'someOtherTest' => [
            'groups' => ['foo', 'bar', 'baz'],
            'mode' => 'none'
        ],
        'anotherTest' => [
            'banana' => ['whatever'],
            'name' => ['Bruno'],
            'ban-groups' => ['blocked'],
            'mode' => 'and'
        ],
        'noAuthTest' => [],
        'noDocblockTest' => [],
        'invalidModeTest' => [
            'mode' => 'foobar'
        ]
    ];

    /**
     * @auth-groups reg-user, paying customers
     * @auth-mode OR
     */
    public function someTest()
    {

    }

    /**
     * @auth-groups foo, bar, baz
     * @auth-mode NONE
     */
    public function someOtherTest()
    {

    }

    /**
     * @auth-banana whatever
     * @auth-name Bruno
     * @auth-ban-groups blocked
     * @auth-mode AND
     */
    public function anotherTest()
    {

    }

    /**
     * foobar
     */
    public function noAuthTest()
    {

    }

    public function noDocblockTest()
    {

    }

    /**
     * @auth-mode foobar
     */
    public function invalidModeTest()
    {

    }
}