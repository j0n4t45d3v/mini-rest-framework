<?php

namespace Mini\Framework\Http\Validators;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
final class Size implements Validator
{

  public function __construct(private readonly Min $min, private readonly Max $max) { }

  public function isValid(mixed $value): bool
  {
    return $this->min->isValid($value) && $this->max->isValid($value);
  }

}
