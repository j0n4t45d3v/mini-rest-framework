<?php

namespace Mini\Framework\Core\_Internal\Handlers;

use Mini\Framework\Core\ExceptionHandler;
use Throwable;

class DefaultExceptionHandler implements ExceptionHandler
{

  public function handler(
      Throwable $exception,
      int &$httpStatusCode,
      array &$headers
  ): mixed
  {
    return json_encode([
        "error" => $exception->getMessage(),
        "status" => "internal_server_error",
        "stacktrace" => $exception->getTraceAsString(),
        "timestamp" => date(DATE_ATOM)
    ]);
  }

}