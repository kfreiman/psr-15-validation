<?php

namespace Middlewares\Tests;

use Middlewares\Utils\Dispatcher;
use Middlewares\Utils\Factory;
use Middlewares\Validation;
use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{
    public function validatorsProvider()
    {
        return [
            [
                [
                    'ip' => function ($value, $attr) {
                        throw new \Exception('Value "'. $value .'" for '.  $attr . ' is wrong!');
                    },
                ],
                [
                    'ip' => 'wrong data',
                ],
            ],
        ];
    }

    /**
     * @dataProvider validatorsProvider
     * @param mixed $validators
     * @param mixed $data
     */
    public function testValidation(array $validators, array $data)
    {
        $request = Factory::createServerRequest();
        $request = $request->withParsedBody($data);

        $mw = new Validation($validators);

        $response = Dispatcher::run([
                $mw,
                function ($request) {
                    $this->assertEquals(
                        ['ip' => ['Value "wrong data" for ip is wrong!']],
                        $request->getAttribute('errors')
                    );

                    $this->assertEquals(
                        true,
                        $request->getAttribute('has-errors')
                    );
                },
            ], $request);
    }
}
