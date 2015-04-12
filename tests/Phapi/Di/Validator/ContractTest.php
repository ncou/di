<?php

namespace Phapi\Tests\Container\Validator;

use Phapi\Di\Container;
use Phapi\Di\Validator\Contract as ContractValidator;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @coversDefaultClass \Phapi\Di\Validator\Contract
 */
class ContractTest extends TestCase
{

    public $validator;
    public $container;

    public function setUp()
    {
        $this->container = new Container();
        $this->validator = new ContractValidator($this->container);
        $this->validator->setContract('Phapi\Contract\Di\Container');
    }

    public function testValidPipelineCallable()
    {
        $callable = function () {
            return new Container();
        };

        $return = $this->validator->validate($callable);
        $this->assertSame($callable, $return);
    }

    public function testValidPipelineNotCallable()
    {
        $c = new Container();

        $return = $this->validator->validate($c);
        $this->assertSame($c, $return);
    }

    public function testInvalidPipeline()
    {
        $this->setExpectedException('RuntimeException', 'The configured value does not implement');
        $return = $this->validator->validate(new \stdClass());
    }
}