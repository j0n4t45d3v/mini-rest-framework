<?php

namespace Mini\Framework\Http\Validators;

use Attribute;
use TypeError;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
final class Max implements Validator
{

  public function __construct(private readonly int $size) {}

  public function isValid(mixed $value): bool
  {
    if (is_string($value)) {
      return strlen($value) <= $this->size;
    }

    if (is_array($value)) {
      return count($value) <= $this->size ;
    }

    if (is_numeric($value)) {
      return $value <= $this->size;
    }

    throw new TypeError("value provided is not a numeric, string or array");
    
  }

}
