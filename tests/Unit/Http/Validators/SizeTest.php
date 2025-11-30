<?php

use Mini\Framework\Http\Validators\Size;
use Mini\Framework\Http\Validators\Max;
use Mini\Framework\Http\Validators\Min;

describe("Size Validator", function () {

  it("returns true for values inside range ", function (int $min, int $max, mixed $case) {
    $validator = new Size(new Min($min), new Max($max));
    expect($validator->isValid($case))
      ->toBeTrue();
  })->with([
    [1, 5, "a"],
    [1, 5, "abcd"],
    [3, 3, "abc"],
    [1, 3, [1]],
    [1, 3, [1, 2, 3]],
    [0, 10, 5],
    [10, 20, 10],
    [10, 20, 20],
    [1, 1, "x"],
    [2, 4, [10, 20, 30]],
  ]);

  it("returns false for values outside range", function (int $min, int $max, mixed $case) {
    $validator = new Size(new Min($min), new Max($max));
    expect($validator->isValid($case))
      ->toBeFalse();
  })->with([
    [3, 5, "a"],
    [3, 5, "abcdef"],
    [2, 4, []],
    [2, 4, [1, 2, 3, 4, 5]],
    [10, 20, 5],
    [10, 20, 30],
    [1, 1, ""],
    [2, 2, [1]],
  ]);
});
