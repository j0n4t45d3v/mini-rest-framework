<?php

namespace Mini\Framework\Http;

use Mini\Framework\Core\Models\HttpContext;

interface MiddlewareHandlerAfter
{
  public function after(HttpContext $ctx): void;
}