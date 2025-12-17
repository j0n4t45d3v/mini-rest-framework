<?php

namespace Mini\Framework;

use Mini\Framework\Core\Attributes\Body;
use Mini\Framework\Core\Attributes\Controller;
use Mini\Framework\Core\Attributes\Path;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Throwable;

class Kernel
{

  private const CACHE_DIR = "cache/";
  private const CACHE_ROUTE = self::CACHE_DIR . "routes.php";

  public function __construct(
      private readonly ExceptionHandler $exceptionHandler = new DefaultExceptionHandler()
  ){}

  public function registerControllers(string $directory = "src/Controller"): void
  {
    $controllerFiles = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    $cache = [];
    if (file_exists(self::CACHE_ROUTE)) {
      $cache = require self::CACHE_ROUTE;
    }

    foreach ($controllerFiles as $controllerFile) {
      if ($controllerFile->isFile() && $controllerFile->getExtension() === "php") {
        $namespace = null;
        $class = null;

        if (filemtime(self::CACHE_ROUTE) >= filemtime($controllerFile->getPathname()) && !empty($cache)) {
          continue;
        }

        $fileStream = @fopen($controllerFile->getPathname(), 'r');
        if (!$fileStream) {
          continue;
        }
        while (($line = fgets($fileStream)) !== false) {
          if (preg_match('/^namespace\s+(.+?);/m', $line, $m)) {
            $namespace = trim($m[1]);
          }

          if (preg_match('/^class\s+([A-Za-z0-9_]+)/', $line, $m)) {
            $class = trim($m[1]);
            break;
          }
        }
        @fclose($fileStream);

        if (!$class) {
          continue;
        }
        $classFullname = "$namespace\\$class";

        $reflector = new ReflectionClass($classFullname);

        $contextPath = "/";
        $controllerAttribute = $reflector->getAttributes(Controller::class);
        if ($controllerAttribute) {
          $contextPath = $controllerAttribute[0]->newInstance()->contextPath;
        }

        if (!str_starts_with($contextPath, "/")) {
          $contextPath = "/$contextPath";
        }

        if (str_ends_with($contextPath, "/")) {
          $contextPath = substr($contextPath, 0, -1);
        }

        foreach ($reflector->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
          $route = $method->getAttributes(Path::class);
          if (empty($route)) {
            continue;
          }

          $routeInfo = $route[0]->newInstance();
          $uri = $routeInfo->uri;
          if (!str_starts_with($uri, "/")) {
            $uri = "/$uri";
          }

          if (str_ends_with($uri, "/")) {
            $uri = substr($uri, 0, -1);
          }
          $path = "$contextPath$uri";

          $cache[$path][$routeInfo->httpMethod->name] = [
            "class" => $classFullname,
            "method" => $method->getName(),
            "args" =>  array_map(fn(ReflectionParameter $p) =>  [
              "name" => $p->getName(),
              "type" => $p->getType()->getName(),
              "is_body" => !empty($p->getAttributes(Body::class)),
            ], $method->getParameters())
          ];
        }
      }
    }

    if (!is_dir(self::CACHE_DIR)) {
      mkdir(self::CACHE_DIR);
    }

    $content = "<?php\n\nreturn " . var_export($cache ?? [], true) . ";";
    file_put_contents(self::CACHE_ROUTE, $content);
  }

  public function dispatch()
  {
    $uri = $_SERVER["REQUEST_URI"] ?? "/";
    $queryParams = [];

    if (str_contains($uri, "?")) {
      [$uri, $queries] = array_pad(explode("?", $uri, 2), 2, "");

      $queries = explode("&", $queries);
      foreach ($queries as $query) {
        [$name, $value] = explode("=", $query);
        $queryParams[$name] = $value;
      }
    }

    if (str_ends_with($uri, "/")) {
      $uri = substr($uri, 0, -1);
    }
    $httpMethod = $_SERVER["REQUEST_METHOD"];
    $uris = require self::CACHE_ROUTE;

    $route = $uris[$uri] ?? null;

    $pathVariables = [];
    if (!$route) {
      foreach ($uris as $path => $methods) {
        $path = str_replace("/", "\/", $path);
        preg_match_all("/{([^\/]+)}/", $path, $variables);
        $path = preg_replace("/{[^\/]+}/", "([^\/]+)", $path);
        $pattern = "/^$path$/";
        if (preg_match_all($pattern, $uri, $values)) {
          $route = $methods;
          foreach ($variables[1] as $key => $variable) {
            $pathVariables[$variable] = $values[$key + 1][0];
          }
          break;
        }
      }
    }

    header("Content-Type: application/json");
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
    ini_set("display_errors", 0);
    ini_set('log_errors', '1');
    try {
      $instance = new $method["class"]();
      $args = $method["args"] ?? [];
      $argsValue = [];
      foreach ($args as $arg) {
        [
          "name" => $name,
          "type" => $type,
          "is_body" => $isBody
        ] = $arg;

        $value = null;
        if ($isBody) {
          $body = file_get_contents("php://input");
          $bodyArray = json_decode($body, true);
          $value = $bodyArray;
        }

        if ($name == "queries") {
          $value = $queryParams ?? [];
        }

        if (key_exists($name, $pathVariables)) {
          $value = $pathVariables[$name];
        }

        if (key_exists($name, $queryParams)) {
          $value = $queryParams[$name];
        }

        if (!is_null($value)) {
          $argsValue[$name] = $value;
        }
        
      }

      echo json_encode(call_user_func_array([$instance, $method["method"]], $argsValue));
    } catch (Throwable $e) {
      $statusCode = 500;
      $headers = [];
      echo $this->exceptionHandler->handler($e, $statusCode, $headers);
      foreach ($headers as $name => $value) {
        header("$name: $value");
      }
      http_response_code($statusCode);
    }
  }
}
