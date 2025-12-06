<?php

namespace Mini\Framework\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_CLASS_CONSTANT)]
final class Controller
{
  public function __construct(public readonly string $contextPath = "/") {}
}
