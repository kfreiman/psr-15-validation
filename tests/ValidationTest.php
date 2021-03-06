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
                    ['ip', 'ip'],
                ],
                [
                    'ip' => '123123123', // wrong data
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
                        ['ip' => ['Value "123123123" was expected to be a valid IP address.']],
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
