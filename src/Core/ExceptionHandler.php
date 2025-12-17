<?php

namespace Mini\Framework\Core;

use Throwable;

interface ExceptionHandler
{
  public function handler(
      Throwable $exception,
      int &$httpStatusCode,
      array &$headers
  ): mixed;
}