<?php

namespace Middlewares;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Assert\InvalidArgumentException;
use Assert\Assertion;
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
        DelegateInterface $delegator
    ): ResponseInterface {
        $data = $request->getParsedBody();
        $this->validate($this->rules, $data);

        $request = $request->withAttribute($this->errorsAttribute, $this->getErrors());
        $request = $request->withAttribute($this->hasErrorsAttribute, $this->hasErrors());
        return $delegator->process($request);
    }

    protected function validate(array $rules, $data)
    {
        foreach ($rules as $rule) {
            $attr = $rule[0];
            $assertion = $rule[1];

            $arg1 = $rule[2] ?? null;
            $arg2 = $rule[3] ?? null;
            $arg3 = $rule[4] ?? null;
            $arg4 = $rule[5] ?? null;

            $value = $data[$attr] ?? null;

            try {
                Assertion::$assertion($value, $arg1, $arg2, $arg3, $arg4);
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
}
