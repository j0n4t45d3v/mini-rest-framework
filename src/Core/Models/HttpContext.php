<?php

namespace Mini\Framework\Core\Models;

final  class HttpContext
{

  private bool $next;
  private readonly Request $request;
  private readonly Response $response;

  public function __construct(Request $request, Response $response) {
    $this->request = $request;
    $this->response = $response;
    $this->next = false;
  }

  public function writeResponse(array $body): void
  {
    $this->response->setBody($body);
  }

  public function getRequestBody(): mixed
  {
    return $this->request->getBody();
  }

  public function getResponseBody(): array
  {
    return $this->response->getBody();
  }

  public function next(): void
  {
    $this->next = true;
  }

  public function isNext(): bool
  {
    return $this->next;
  }

}