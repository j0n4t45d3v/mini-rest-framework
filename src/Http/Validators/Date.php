<?php

namespace Mini\Framework\Http\Validators;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final class Date extends Pattern
{

  const DATE_PATTERN = "^\d{4}-\d{2}-\d{2}$";

  public function __construct()
  {
    parent::__construct(regex: self::DATE_PATTERN);
  }

  public function isValid(mixed $value): bool
  {
    if (!parent::isValid($value)) {
      return false;
    }
    [$year, $month, $day] = explode("-", $value);

    return checkdate($month, $day, $year);
  }

}
