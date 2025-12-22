<?php

namespace Mini\Framework\Core\_Internal;

use FilesystemIterator;
use Generator;
use Mini\Framework\Core\Attributes\Controller;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionException;
use SplFileInfo;

final class ControllerDiscovery
{

  /**@return Generator<ReflectionClass>*/
  public function scan(string $root): Generator
  {
    $composerConfiguration = file_get_contents("$root/composer.json");
    $composerContentDecoded = json_decode($composerConfiguration, true);
    $namespaces = $composerContentDecoded["autoload"]["psr-4"];
    foreach ($namespaces as $namespace => $path) {
      $absolutePath = realpath($root . DIRECTORY_SEPARATOR . $path) . DIRECTORY_SEPARATOR;
      $recursiveDirectoryIterator =
          new RecursiveDirectoryIterator($absolutePath, FilesystemIterator::SKIP_DOTS);
      $phpFiles = new RecursiveIteratorIterator($recursiveDirectoryIterator);

      /** @var SplFileInfo $file */
      foreach ($phpFiles as $file) {
        if ($file->getExtension() !== 'php') {
          continue;
        }
        $classname = $this->normalizeClassname($file, $absolutePath, $namespace);
        try {
          $reflector = new ReflectionClass($classname);
          $isController = $reflector->getAttributes(Controller::class);
          if (!$isController) {
            continue;
          }
          yield $reflector;
        } catch (ReflectionException) {
          continue;
        }
      }
    }
  }


  private function normalizeClassname(
      SplFileInfo $file,
      string      $absolutePath,
      string      $namespace
  ): string
  {
    $searchToReplace = [".php", $absolutePath];
    $replacedBy = ["", $namespace];
    return str_replace(
        $searchToReplace,
        $replacedBy,
        $file->getPathname()
    );
  }
}
