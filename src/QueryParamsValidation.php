<?php

namespace Middlewares;

use Psr\Http\Message\ServerRequestInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;

class QueryParamsValidation extends Validation
{
    public function process(
        ServerRequestInterface $request,
        DelegateInterface $handler
    ): ResponseInterface {
        $this->validate($this->getRules(), $request->getQueryParams());

        $request = $request->withAttribute($this->errorsAttribute, $this->getErrors());
        $request = $request->withAttribute($this->hasErrorsAttribute, $this->hasErrors());
        return $handler->process($request);
    }
}
