<?php

namespace Middlewares;

use Psr\Http\Message\ServerRequestInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class QueryParamsValidation extends Validation
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $data = $request->getQueryParams();
        $this->validate($this->rules, $data);

        $request = $request->withAttribute($this->errorsAttribute, $this->getErrors());
        $request = $request->withAttribute($this->hasErrorsAttribute, $this->hasErrors());
        return $handler->handle($request);
    }
}
