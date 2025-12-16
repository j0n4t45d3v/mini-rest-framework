<?php

namespace Mini\Framework\Http\Validators;

use Attribute;
use TypeError;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
final readonly class Size implements Validator
{

  public function __construct(private Min $min, private Max $max) { }

  public function isValid(mixed $value): bool
  {
    return $this->min->isValid($value) && $this->max->isValid($value);
  }

}
