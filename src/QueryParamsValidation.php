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
        $this->validate($this->getRules(), $request->getQueryParams());

        $request = $request->withAttribute($this->errorsAttribute, $this->getErrors($request));
        $request = $request->withAttribute($this->hasErrorsAttribute, $this->hasErrors($request));
        return $delegator->process($request);
    }
}
