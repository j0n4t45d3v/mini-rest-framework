<?php

namespace Mini\Framework\Core\Mappers;

use Mini\Framework\Core\Attributes\JsonProperty;
use Mini\Framework\Core\Exceptions\BodyNotProviderException;
use Mini\Framework\Core\Exceptions\InvalidJsonSerializerException;
use ReflectionClass;
use ReflectionNamedType;

final class JsonObjectMapper
{

  private const ATTRIBUTE = JsonProperty::class;
  private const PRIMITIVE_TYPES = [
    'int',
    'float',
    'bool',
    'string',
    'array'
  ];

  /**
   * @template T
   * @param string $jsonRaw
   * @param class-string<T> $className
   * @return T|null
   */
  public static function wrapper(string $jsonRaw, string $className): mixed
  {
    $jsonDecoded = json_decode($jsonRaw, true);

    if (json_last_error() != JSON_ERROR_NONE) {
      throw new InvalidJsonSerializerException(json_last_error_msg());
    }

    if (empty($jsonDecoded)) {
      throw new BodyNotProviderException("body not provided!");
    }

    $reflector = new ReflectionClass($className);
    $instance = $reflector->newInstanceWithoutConstructor();
    foreach ($reflector->getProperties() as $property) {
      $typePropertier = $property->getType();
      $jsonProperty = $property->getAttributes(self::ATTRIBUTE);
      $property->setAccessible(true);
      if (empty($jsonProperty)) {
        $property->setValue($instance, null);
        continue;
      }
      $jsonPropertyInfo = $jsonProperty[0]->newInstance();
      $jsonPropertyValue = $jsonDecoded[$jsonPropertyInfo->name] ?? null;
      self::validateValue($typePropertier, $jsonPropertyInfo->name, $jsonPropertyValue);
      $property->setValue($instance, $jsonPropertyValue);
    }
    return $instance;
  }

  private static function validateValue(ReflectionNamedType $type, string $property, mixed $value)
  {
    $isValidType = match ($type->getName()) {
      'int' => is_numeric($value) || is_int($value),
      'float' => is_numeric($value) || is_float($value),
      'bool' => is_bool($value),
      'string' => is_string($value),
      'array' => is_array($value),
      'mixed' => true,
      default => false
    };

    $allowNull = ($type->allowsNull() && is_null($value));
    $isValidValue = $isValidType || $allowNull;
    if (!$isValidValue) {
      throw new InvalidJsonSerializerException(
        "Invalid type for property '$property'."
      );
    }
    return;
  }

  public static function unwrapper(object $instance): string
  {
    $reflector = new ReflectionClass($instance);
    $jsonArray = [];
    foreach ($reflector->getProperties() as $property) {
      $typePropertier = $property->getType();
      $jsonPropertyAttribute = $property->getAttributes(self::ATTRIBUTE);
      $isNotAPrimitiveType = !in_array($typePropertier->getName(), self::PRIMITIVE_TYPES);
      if (empty($jsonPropertyAttribute) || $isNotAPrimitiveType) {
        continue;
      }
      $jsonPropertyInstance = $jsonPropertyAttribute[0]->newInstance();
      $jsonArray[$jsonPropertyInstance->name] = $property->getValue($instance);
    }
    return json_encode($jsonArray);
  }
}
