<?php

namespace Mini\Framework\Core\Attributes;

use Attribute;
use Mini\Framework\Http\Method;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Body
{
  public function __construct() {}
}
