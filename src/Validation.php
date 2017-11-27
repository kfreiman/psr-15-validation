<?php

namespace Middlewares;

use Assert\InvalidArgumentException;
use Assert\Assertion;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Validation implements MiddlewareInterface
{
    protected $rules = [];

    protected $errors = [];

    protected $request;

    protected $errorsAttribute = 'errors';

    protected $hasErrorsAttribute = 'has-errors';

    public function __construct(array $rules)
    {
        $this->setRules($rules);
    }

    public function process(
        ServerRequestInterface $request,
        DelegateInterface $handler
    ): ResponseInterface {
        $this->setRequest($request);
        if (!is_null($before = $this->beforeValidation($request, $handler))) {
            return $before;
        }

        $this->validate($this->getRules(), $this->getData());
        $request = $request->withAttribute($this->errorsAttribute, $this->getErrors());
        $request = $request->withAttribute($this->hasErrorsAttribute, $this->hasErrors());

        if (!is_null($after = $this->afterValidation($request, $handler))) {
            return $after;
        }
        return $handler->process($request);
    }

    protected function getData(): array
    {
        return $this->getRequest()->getParsedBody();
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function setRequest(ServerRequestInterface $request)
    {
        $this->request = $request;
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
                $this->addError($attr, $exception->getMessage());
            }
        }
    }

    protected function addError(string $field, string $message)
    {
        $this->errors[$field][] = $message;
    }

    public function beforeValidation(
        ServerRequestInterface $request,
        DelegateInterface $handler
    ) {
        return null;
    }

    public function afterValidation(
        ServerRequestInterface $request,
        DelegateInterface $handler
    ) {
        return null;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function setRules(array $rules): void
    {
        $this->rules = $rules;
    }

    public function getRules(): array
    {
        return $this->rules;
    }
}
