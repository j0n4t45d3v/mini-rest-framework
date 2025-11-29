<?php

use Mini\Framework\Http\Validators\Date;

describe("Date Validator", function () {

  it("returns true for valid date values", function (string $case) {
    $validator = new Date();
    expect($validator->isValid($case))
      ->toBeTrue();
  })->with(["1999-12-12", "2024-02-29", "2025-02-28"]);

  it("returns false for non-date strings", function (string $case) {
    $validator = new Date();
    expect($validator->isValid($case))
      ->toBeFalse();
  })->with(["ab", "johndoe", "", "xxxx-xx-xx"]);

  it("returns false for valid dates in wrong formats", function (string $case) {
    $validator = new Date();
    expect($validator->isValid($case))
      ->toBeFalse();
  })->with([
    "2002/02/19",
    "19-02-2002",
    "2024-2-9",
    "2024-02-9",
    "2024-2-09",
  ]);

  it("returns false for invalid calendar dates", function (string $case) {
    $validator = new Date();
    expect($validator->isValid($case))
      ->toBeFalse();
  })->with([
    "1999-99-12",
    "2024-02-30",
    "2025-02-29",
    "2025-25-25",
    "2024-00-10",
    "2024-01-00",
  ]);
});
