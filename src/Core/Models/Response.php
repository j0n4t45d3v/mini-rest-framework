<?php

namespace Mini\Framework\Core\Models;

class Response
{
  private int $statusCode;
  private array $body;

  public function __construct()
  {
    $this->statusCode = 500;
    $this->body = [];
  }

  public function getStatusCode(): int
  {
    return $this->statusCode;
  }

  public function getBody(): array
  {
    return $this->body;
  }

  public function setBody(array $body): void
  {
    $this->body = $body;
  }

  public function setStatusCode(int $statusCode): void
  {
    $this->statusCode = $statusCode;
  }

  public function isSuccess(): bool
  {
    return 200 >= $this->statusCode && $this->statusCode < 300;
  }

}