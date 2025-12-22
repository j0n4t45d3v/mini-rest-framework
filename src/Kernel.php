<?php

namespace Mini\Framework;

use Mini\Framework\Core\_Internal\ControllerDiscovery;
use Mini\Framework\Core\_Internal\Handlers\DefaultExceptionHandler;
use Mini\Framework\Core\_Internal\RouterManager;
use Mini\Framework\Core\_Internal\Utils\NormalizeUtils;
use Mini\Framework\Core\Attributes\Body;
use Mini\Framework\Core\Attributes\Controller;
use Mini\Framework\Core\Attributes\Middleware;
use Mini\Framework\Core\Attributes\Path;
use Mini\Framework\Core\ExceptionHandler;
use Mini\Framework\Core\Models\HttpContext;
use Mini\Framework\Core\Models\Request;
use Mini\Framework\Core\Models\Response;
use Mini\Framework\Http\MiddlewareHandlerAfter;
use Mini\Framework\Http\MiddlewareHandlerBefore;
use ReflectionMethod;
use ReflectionParameter;
use Throwable;

final class Kernel
{

  use NormalizeUtils;

  private const CACHE_DIR = "cache/";
  private const CACHE_ROUTE = self::CACHE_DIR . "routes.php";
  private readonly ControllerDiscovery $controllerDiscovery;
  private array $cache;
  private bool $enableCache;

  public function __construct(
      string $root = __DIR__,
      private readonly ExceptionHandler $exceptionHandler = new DefaultExceptionHandler()
  ){
    $this->controllerDiscovery = new ControllerDiscovery();
    $this->cache = [];
    $this->enableCache = false;
    if (file_exists(self::CACHE_ROUTE)) {
      $this->cache = require self::CACHE_ROUTE;
    }
    $this->registerControllers($root);
  }

  public function useRouteCache(): void
  {
    $this->enableCache = true;
  }

  private function registerControllers(string $root): void
  {
    foreach ($this->controllerDiscovery->scan($root) as $controller) {
      $contextPath = "/";
      $controllerAttribute = $controller->getAttributes(Controller::class);
      if ($controllerAttribute) {
        $contextPath = $controllerAttribute[0]->newInstance()->contextPath;
      }
      $contextPath = $this->normalizeURI($contextPath);

      foreach ($controller->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        $route = $method->getAttributes(Path::class);
        if (empty($route)) {
          continue;
        }

        $routeInfo = $route[0]->newInstance();
        $uri = $this->normalizeURI($routeInfo->uri);
        $path = "$contextPath$uri";

        $middlewares = $method->getAttributes(Middleware::class);

        $this->cache[$path][$routeInfo->httpMethod->name] = [
            "class" => $controller->name,
            "method" => $method->getName(),
            "middlewares" => array_map(function ($middleware) {
              /**@var Middleware $teste */
              $teste = $middleware->newInstance();
              return $teste->handler;
            }, $middlewares),
            "args" => array_map(fn(ReflectionParameter $p) => [
                "name" => $p->getName(),
                "type" => $p->getType()->getName(),
                "is_body" => !empty($p->getAttributes(Body::class)),
            ], $method->getParameters())
        ];
      }
    }

    if ($this->enableCache) {
      if (!is_dir(self::CACHE_DIR)) {
        mkdir(self::CACHE_DIR);
      }

      $content = "<?php\n\nreturn " . var_export($cache ?? [], true) . ";";
      file_put_contents(self::CACHE_ROUTE, $content);
    }
  }

  public function dispatch(): void
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
    $uris = $this->cache;

    $pathVariables = [];
    $routerManager = new RouterManager($uris);
    $method = $routerManager->resolve($uri, $httpMethod, $pathVariables);
    try {
      $instance = new $method["class"]();
      $firstMiddleware = $method["middlewares"][0];
      $args = $method["args"] ?? [];
      $body = null;
      $argsValue = [];
      foreach ($args as $arg) {
        [
          "name" => $name,
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

      $middleware = null;
      if ($firstMiddleware) {
        $middleware = new $firstMiddleware();
      }
      $response = new Response();
      $request = new Request($body);
      $ctx = new HttpContext($request, $response);
      if ($middleware instanceof MiddlewareHandlerBefore) {
        $middleware->before($ctx);
      }

      if ($middleware instanceof MiddlewareHandlerBefore && !$ctx->isNext()) {
        http_response_code($response->getStatusCode());
        echo json_encode($response->getBody());
        return;
      }

      $response->setBody(call_user_func_array([$instance, $method["method"]], $argsValue));

      if ($middleware instanceof MiddlewareHandlerAfter) {
        $middleware->after($ctx);
      }

      echo json_encode($response->getBody());

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
