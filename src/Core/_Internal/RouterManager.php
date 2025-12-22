<?php

namespace Mini\Framework\Core\_Internal;

class RouterManager
{

  private array $routes;

  /**
   * @param array $routes
   */
  public function __construct(array $routes = [])
  {
    $this->routes = $routes;
  }


  public function resolve(
      string $uri,
      string $httpMethod,
      array &$pathVariables = []
  ): array|null
  {
    $route = $this->routes[$uri] ?? null;

    if (!$route) {
      foreach ($this->routes as $path => $methods) {
        $path = str_replace("/", "\/", $path);
        preg_match_all("/{([^\/]+)}/", $path, $variables);
        $path = preg_replace("/{[^\/]+}/", "([^\/]+)", $path);
        $pattern = "/^$path$/";
        if (preg_match_all($pattern, $uri, $values)) {
          $route = $methods;
          foreach ($variables[1] as $key => $variable) {
            $pathVariables[$variable] = $values[$key + 1][0];
          }
        }
      }
    }

    if (!$route) {
      http_response_code(404);
      echo json_encode([
          "error" => "path not found",
          "status" => "not_found",
          "timestamp" => date(DATE_ATOM)
      ]);
      exit(0);
    }

    $method = $route[$httpMethod] ?? null;

    if (!$method) {
      http_response_code(405);
      echo json_encode([
          "error" => "'$httpMethod' method not allowed to path '$uri'",
          "status" => "method_not_allowed",
          "timestamp" => date(DATE_ATOM)
      ]);
      exit(0);
    }
    return $route[$httpMethod] ?? null;
  }
}