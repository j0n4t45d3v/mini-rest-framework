<?php

namespace Mini\Framework\Http;

use Mini\Framework\Core\Models\HttpContext;

interface MiddlewareHandlerBefore
{

  public function before(HttpContext $ctx): void;

}