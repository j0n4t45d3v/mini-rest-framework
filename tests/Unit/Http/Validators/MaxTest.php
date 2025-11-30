<?php

use Mini\Framework\Http\Validators\Max;

describe("Max Validator", function () {

  it("returns true for values less or equals than max size", function (int $size, mixed $case) {
    $validator = new Max($size);
    expect($validator->isValid($case))
      ->toBeTrue();
  })->with([
      [1, "2"],
      [3, "223"],
      [10, 3],
      [100, "1398219843729843293msakldjnkfashd"],
      [2, [1, 2]],
    ]);

  it("returns false for invalid values", function (int $size, mixed $case) {
    $validator = new Max($size);
    expect($validator->isValid($case))
      ->toBeFalse();
  })->with([
      [1, "22"],
      [3, "x223"],
      [10, 50],
      [100, 1000],
      [100, 100.0001],
      [2, [1, 2, 3]],
    ]);


  it("throw TypeError for values non as a numeric, string or array", function (int $size, mixed $case) {
    $validator = new Max($size);
    try {
      $validator->isValid($case);
    } catch (TypeError $error) {
      expect($error->getMessage())
        ->toBe("value provided is not a numeric, string or array");
    }
  })->with([
      [1, null],
      [3, new stdClass],
  ]);
});
