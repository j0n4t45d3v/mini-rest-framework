<?php

namespace Mini\Framework\Core\_Internal\Utils;

trait NormalizeUtils
{

  public function normalizeURI(string $uri): string
  {
    if (!str_starts_with($uri, "/")) {
      $uri = "/$uri";
    }

    if (str_ends_with($uri, "/")) {
      $uri = substr($uri, 0, -1);
    }
    return $uri;
  }

}