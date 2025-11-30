<?php

use Mini\Framework\Http\Validators\Min;

describe("Min Validator", function () {

  it("returns true for values greater or equals than min size", function (int $size, mixed $case) {
    $validator = new Min($size);
    expect($validator->isValid($case))
      ->toBeTrue();
  })->with([
      [1, "22"],
      [3, "223"],
      [10, 50],
      [100, 1000],
      [100, 100.0001],
      [2, [1, 2, 3]],
      [2, 2],
    ]);

  it("returns false for invalid values", function (int $size, mixed $case) {
    $validator = new Min($size);
    expect($validator->isValid($case))
      ->toBeFalse();
  })->with([
      [1, ""],
      [3, "22"],
      [10, 3],
      [100, "1398219843729843293msakldjnkfashd"],
      [2, [1]],
    ]);


  it("throw TypeError for values non as a numeric, string or array", function (int $size, mixed $case) {
    $validator = new Min($size);
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
