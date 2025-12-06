<?php

namespace Mini\Framework\Core\Attributes;

use Attribute;
use Mini\Framework\Http\Method;

#[Attribute(Attribute::TARGET_METHOD)]
class Path
{
  public function __construct(
    public readonly string $uri = "",
    public readonly Method $httpMethod = Method::GET
  ) {}
}
