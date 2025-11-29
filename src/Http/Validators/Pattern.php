<?php 

namespace Mini\Framework\Http\Validators;

use Attribute;
use TypeError;

#[Attribute(Attribute::TARGET_PARAMETER |Attribute::TARGET_PROPERTY)]
readonly class Pattern implements Validator
{

  public function __construct(private string $regex) {
  }

  public function isValid(mixed $value): bool
  {
    if (!is_string($value)) {
      throw new TypeError("value provided is not a string");
    }
    return preg_match("/$this->regex/", $value);
  }

}
