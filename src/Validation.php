<?php

namespace Middlewares;

use Assert\InvalidArgumentException;
use Assert\Assertion;
use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Validation implements MiddlewareInterface
{
    protected $rules = [];

    protected $errors = [];

    protected $errorsAttribute = 'errors';

    protected $hasErrorsAttribute = 'has-errors';

    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $data = $request->getParsedBody();
        $this->validate($this->rules, $data);

        $request = $request->withAttribute($this->errorsAttribute, $this->getErrors());
        $request = $request->withAttribute($this->hasErrorsAttribute, $this->hasErrors());
        return $handler->handle($request);
    }

    protected function validate(array $rules, $data)
    {
        // var_dump($rules);
        // exit;
        foreach ($rules as $rule) {
            $attr = $rule[0];
            $assertion = $rule[1];

            $arg1 = $rule[2] ?? null;
            $arg2 = $rule[3] ?? null;
            $arg3 = $rule[4] ?? null;

            $value = $data[$attr] ?? null;

            try {
                Assertion::$assertion($value, $arg1, $arg2, $arg3);
            } catch (InvalidArgumentException $exception) {
                $this->errors[$attr][] = $exception->getMessage();
            }
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }


    // public function FunctionName($value='')
    // {
    //     # code...
    // }
}
