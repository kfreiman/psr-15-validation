<?php

namespace Middlewares;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;

class QueryParamsValidation extends Validation
{
    public function process(
        ServerRequestInterface $request,
        DelegateInterface $delegator
    ): ResponseInterface {
        $data = $request->getQueryParams();
        $this->validate($this->rules, $data);

        $request = $request->withAttribute($this->errorsAttribute, $this->getErrors());
        $request = $request->withAttribute($this->hasErrorsAttribute, $this->hasErrors());
        return $delegator->process($request);
    }
}
