<?php

use Mini\Framework\Http\Validators\Pattern;

const EMAIL_PATTERN = "^(?=.{1,254}$)(?=.{1,64}@)[A-Za-z0-9!#$%&'*+\/=?^_`{|}~.-]+@[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?(?:\.[A-Za-z]{2,})+$";

describe("Pattern Validator", function () {

  it("returns true for valid values", function (string $pattern, string $case) {
    $validator = new Pattern($pattern);
    expect($validator->isValid($case))
      ->toBeTrue();
  })->with([
    ["^\d{2}$", "12"],
    [EMAIL_PATTERN, "john@doe.test"],
  ]);

  it("returns false for invalid values", function (string $pattern, string $case) {
    $validator = new Pattern($pattern);
    expect($validator->isValid($case))
      ->toBeFalse();
  })->with([
    ["^\d{2}$", "ab"],
    [EMAIL_PATTERN, "johndoe"],
    [EMAIL_PATTERN, ""],
  ]);

  it("throw TypeError for values non-strings", function (string $pattern, mixed $case) {
    $validator = new Pattern($pattern);
    try {
      $validator->isValid($case);
    } catch (TypeError $error) {
      expect($error->getMessage())
        ->toBe("value provided is not a string");
    }
  })->with([
    ["^\d{2}$", 12],
    [EMAIL_PATTERN, false],
    [EMAIL_PATTERN, null],
  ]);
});
