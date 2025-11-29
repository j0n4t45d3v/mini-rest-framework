<?php

namespace Mini\Framework\Http\Validators;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final class DateTime extends Pattern
{

  const DATE_TIME_PATTERN = "^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$";
  const MAX_SECONDS_VALUE = 59;
  const MAX_MINUTES_VALUE = 59;
  const MAX_HOUR_VALUE = 23;
  const MIN_VALUE_ACCEPT = 0;

  public function __construct()
  {
    parent::__construct(regex: self::DATE_TIME_PATTERN);
  }

  public function isValid(mixed $value): bool
  {
    if (!parent::isValid($value)) {
      return false;
    }
    [$date, $time] = explode("T", $value);
    return $this->isValidDate($date) && $this->isValidTime($time);
  }

  private function isValidDate(string $date): bool
  {
    return (new Date)->isValid($date);
  }

  private function isValidTime(string $time): bool
  {
    $timeExploded = explode(":", $time);
    $sizeTime = count($timeExploded);
    if ($sizeTime < 3 || $sizeTime  > 3) {
      return false;
    }

    [$hour, $minute, $seconds] = $timeExploded;
    return $this->isValidHour($hour)
      && $this->isValidMinute($minute)
      && $this->isValidSeconds($seconds);
  }

  private function isValidHour(int $hour)
  {
    return $hour >= 0 && $hour <= self::MAX_HOUR_VALUE;
  }

  private function isValidMinute(int $minute)
  {
    return $minute >= 0 && $minute <= self::MAX_MINUTES_VALUE;
  }

  private function isValidSeconds(int $seconds)
  {
    return $seconds >= 0 && $seconds <= self::MAX_SECONDS_VALUE;
  }
}
