<?php

namespace Middlewares;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Validation implements MiddlewareInterface
{
    private $validators = [];

    private $errors = [];

    private $errorsAttribute = 'errors';

    private $hasErrorsAttribute = 'has-errors';

    public function __construct(array $validators)
    {
        $this->validators = $validators;
    }

    public function process(
        ServerRequestInterface $request,
        DelegateInterface $delegator
    ): ResponseInterface {
        $data = $request->getParsedBody();
        $this->validate($this->validators, $data);

        $request = $request->withAttribute($this->errorsAttribute, $this->getErrors());
        $request = $request->withAttribute($this->hasErrorsAttribute, $this->hasErrors());
        return $delegator->process($request);
    }

    private function validate(array $validators, $data)
    {
        foreach ($validators as $attr => $validator) {
            $value = $data[$attr] ?? null;
            try {
                $validator($value, $attr);
            } catch (\Exception $exception) {
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
