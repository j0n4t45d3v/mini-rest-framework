<?php

namespace Mini\Framework\Core\Models;

class Request
{
  private mixed $body;

  public function __construct(mixed $body)
  {
    $this->body = $body;
  }

  public function getBody(): mixed
  {
    return $this->body;
  }

  public function setBody(mixed $body): void
  {
    $this->body = $body;
  }
}