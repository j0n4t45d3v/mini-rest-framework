<?php

namespace Mini\Framework\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
final class JsonProperty
{

  public function __construct(public readonly string $name) {}

}
