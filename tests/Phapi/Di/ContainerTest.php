<?php

namespace Phapi\Tests\Di;

use Phapi\Di\Container;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @coversDefaultClass \Phapi\Di\Container
 */
class ContainerTest extends TestCase
{
    public function testWithString()
    {
        $container = new Container();
        $container->bind('param', 'value');

        $this->assertEquals('value', $container->make('param'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Identifier "Foo" is not defined.
     */
    public function testMakeInvalidKey()
    {
        $container = new Container();
        $container->make('Foo');
    }

    public function testWithStringAndObject()
    {
        $container = new Container();
        $container->bind('param', 'container value');

        $container->bind('stdClass', function ($container) {
            $obj = new \stdClass();
            $obj->value = $container->make('param');
            return $obj;
        });

        $one = $container->make('stdClass');
        $two = $container->make('stdClass');

        $this->assertSame($one, $two);
        $this->assertSame('container value', $one->value);
        $this->assertSame('container value', $two->value);
    }

    public function testMultiton()
    {
        $container = new Container();
        $container->bind('object', function ($container) {
            $obj = new \stdClass();
            $obj->value = 'multiton test';
            return $obj;
        }, Container::TYPE_MULTITON);

        $one = $container->make('object');
        $two = $container->make('object');

        $this->assertNotSame($one, $two);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cannot override locked content "object".
     */
    public function testLocked()
    {
        $container = new Container();
        $container->bind('param', 'container value');

        $container->bind('object', function ($container) {
            $obj = new \stdClass();
            $obj->value = $container->make('param');
            return $obj;
        });

        $object = $container->make('object');

        $container->bind('object', 'something');
    }

    public function testArrayAccess()
    {
        $container = new Container();
        $container['param'] = 'value';
        $this->assertEquals('value', $container['param']);
        $this->assertTrue(isset($container['param']));
        unset($container['param']);
        $this->assertFalse(isset($container['param']));
    }

    public function testValidatorSuccess()
    {
        $mockValidator = \Mockery::mock('Phapi\Contract\Di\Validator');
        $mockValidator->shouldReceive('validate')->andReturn('success');

        $container = new Container();
        $container->addValidator('param', $mockValidator);
        $container['param'] = "success";
        $this->assertEquals('success', $container['param']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid
     */
    public function testValidatorFail()
    {
        $mockValidator = \Mockery::mock('Phapi\Contract\Di\Validator');
        $mockValidator->shouldReceive('validate')->andThrow(new \InvalidArgumentException('Invalid'));

        $container = new Container();
        $container->addValidator('param', $mockValidator);
        $container['param'] = "success";
        $this->assertEquals('success', $container['param']);
    }
}