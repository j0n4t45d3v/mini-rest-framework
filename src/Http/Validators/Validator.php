<?php

namespace Mini\Framework\Http\Validators;

interface Validator 
{
  public function isValid(mixed $value): bool;
}
