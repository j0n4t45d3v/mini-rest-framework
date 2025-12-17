<?php

namespace Mini\Framework\Core\Attributes;

use Attribute;
use Mini\Framework\Http\MiddlewareHandlerAfter;
use Mini\Framework\Http\MiddlewareHandlerBefore;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Middleware
{
  /**@var class-string<MiddlewareHandlerBefore|MiddlewareHandlerAfter> $handler */
  public function __construct(public readonly string $handler)
  {
  }
}