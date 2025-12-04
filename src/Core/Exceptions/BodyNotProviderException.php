<?php

namespace Mini\Framework\Core\Exceptions;

use RuntimeException;

class BodyNotProviderException extends RuntimeException {

  public function __construct(string $message) {
    parent::__construct($message);
  }

}
