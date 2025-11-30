<?php

use Mini\Framework\Http\Validators\NotEmpty;

describe("NotEmpty Validator", function () {

  it("returns true for values not empty", function (mixed $case) {
    $validator = new NotEmpty;
    expect($validator->isValid($case))
      ->toBeTrue();
  })->with([
    1,
    2,
    "eresr",
    "adskjuwe",
    true,
    [1, 2, 3],
    -1
  ]);

  it("returns false for empty values", function (mixed $case) {
    $validator = new NotEmpty;
    expect($validator->isValid($case))
      ->toBeFalse();
  })->with([
    " ",
    "",
    [[]], //empty array
    0,
    false,
    null
  ]);
});
