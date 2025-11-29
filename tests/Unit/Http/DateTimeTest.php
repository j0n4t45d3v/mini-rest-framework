<?php

use Mini\Framework\Http\Validators\DateTime;

describe("DateTime Validator", function () {

  it("returns true for valid datetime values", function (string $case) {
    $validator = new DateTime();
    expect($validator->isValid($case))
      ->toBeTrue();
  })->with(["1999-12-12T00:00:00", "2024-02-29T14:06:28", "2025-02-28T23:20:53"]);

  it("returns false for non-datetime strings", function (string $case) {
    $validator = new DateTime();
    expect($validator->isValid($case))
      ->toBeFalse();
  })->with([
    "ab",
    "johndoe",
    "",
    "xxxx-xx-xx",
    "2024-02-29",
  ]);

  it("returns false for valid datestime in wrong formats", function (string $case) {
    $validator = new DateTime();
    expect($validator->isValid($case))
      ->toBeFalse();
  })->with([
    "2002/02/19 00:00:00",
    "19-02-2002 01h 03min 53sec",
    "2024-2-9 1:2:24",
    "2024-02-9 12:30:1",
    "2024-2-09 23:2:59",
  ]);

  it("returns false for invalid times", function (string $case) {
    $validator = new DateTime();
    expect($validator->isValid($case))
      ->toBeFalse();
  })->with([
    "1999-12-12T60:00:00",
    "2024-02-29T14:72:00",
    "2025-02-28T-1:-1:-1",
    "1999-12-12T00:61:71",
    "2024-02-09T12:30",
    "2024-02-09T:59",
    "2024-02-09T::",
    "2024-02-09T3:59",
  ]);
});
