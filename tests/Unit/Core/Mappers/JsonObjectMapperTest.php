<?php

use Mini\Framework\Core\Attributes\JsonProperty;
use Mini\Framework\Core\Exceptions\BodyNotProviderException;
use Mini\Framework\Core\Exceptions\InvalidJsonSerializerException;
use Mini\Framework\Core\Mappers\JsonObjectMapper;


describe("JsonObjectMapper::wrapper", function () {

  class ClassSerializeTestCase1
  {
    public function __construct(
      #[JsonProperty(name: "key1")]
      private int $key1,
      #[JsonProperty(name: "key2")]
      private string|null $key2,
      #[JsonProperty(name: "key3")]
      private bool $key3,
      private mixed $ignoredKey = null
    ) {}

    public function getKey1(): int
    {
      return $this->key1;
    }

    public function getKey2(): string|null
    {
      return $this->key2;
    }

    public function getKey3(): bool
    {
      return $this->key3;
    }

    public function getIgnoredKey(): mixed
    {
      return $this->ignoredKey;
    }
  }

  it("return deserialize json to objects instances", function (array $testCases) {
    ["json" => $jsonRaw, "expected" => $expected] = $testCases;
    /** @var ClassSerializeTestCase1|null $instance */
    $instance = JsonObjectMapper::wrapper($jsonRaw, ClassSerializeTestCase1::class);


    expect($instance)
      ->not->toBeNull()
      ->toBeInstanceOf(ClassSerializeTestCase1::class);
    expect($instance->getKey1())->toBe($expected["key1"]);
    expect($instance->getKey2())->toBe($expected["key2"]);
    expect($instance->getKey3())->toBe($expected["key3"]);
    expect($instance->getIgnoredKey())->toBeNull();
  })->with(
    [
      [[
        "json" => '{ "key1": 1, "key2": "value", "key3": true, "ignoredKey": "value" }',
        "expected" => ["key1" => 1, "key2" => "value", "key3" => true],
      ]],
      [[
        "json" => '{ "key1": 10, "key2": "abc", "key3": false }',
        "expected" => ["key1" => 10, "key2" => "abc", "key3" => false],
      ]],
      [[
        "json" =>  '{ "key1": 5, "key2": null, "key3": true }',
        "expected" => ["key1" => 5, "key2" => null, "key3" => true],
      ]],
      [[
        "json" => '{ "key3": false, "ignoredKey": null, "key1": 99, "key2": "zxy" }',
        "expected" => ["key1" => 99, "key2" => "zxy", "key3" => false],
      ]],
      [[
        "json" => '{ "key1": 2, "key2": "more", "key3": true, "extra1": 123, "extra2": "ignored" }',
        "expected" => ["key1" => 2, "key2" => "more", "key3" => true],
      ]],
      [[
        "json" => '{ "key1": 3, "key2": "123", "key3": true }',
        "expected" => ["key1" => 3, "key2" => "123", "key3" => true],
      ]],
      [[
        "json" => '{ "key1": 8, "key2": "test", "key3": true }',
        "expected" => ["key1" => 8, "key2" => "test", "key3" => true],
      ]],
      [[
        "json" => '{ "key1": 7, "key3": true }',
        "expected" => ["key1" => 7, "key2" => null, "key3" => true],
      ]],
      [[
        "json" =>  "{ \"key1\": \"42\", \"key2\": \"present\", \"key3\": true }",
        "expected" => ["key1" => 42, "key2" => "present", "key3" => true],
      ]],
      [[
        "json" => '{ "key1": 1, "key2": null, "key3": false, "ignoredKey": null }',
        "expected" => ["key1" => 1, "key2" => null, "key3" => false],
      ]],
    ]
  );

  it("return InvalidJsonSerializerException for broken json", function ($case) {
    expect(fn() => JsonObjectMapper::wrapper($case, ClassSerializeTestCase1::class))
      ->toThrow(InvalidJsonSerializerException::class);
  })->with([
    ['{"jeke: "vale"}'],
    ['{name: "abc"}'],
    ['{"a": 123,}'],
    ['{"a": "b", "c": ]'],
    ['{"a": "b" "c": "d"}'],
    ['["a", "b", }'],
    ['{"a": {"b": "c"'],
    ['{]'],
    ['{"num": 1e9999999999999}'],
    ['{null}'],
    ['{"a": tru}'],
  ]);

  it("return BodyNotProviderException for empty json", function ($case) {
    expect(fn() => JsonObjectMapper::wrapper($case, ClassSerializeTestCase1::class))
      ->toThrow(BodyNotProviderException::class);
  })->with(['{}']);
});

describe("JsonObjectMapper::unwrapper", function () {

    class ClassSerializeTestCase2
    {
        public function __construct(
            #[JsonProperty(name: "key1")]
            private int $key1,
            #[JsonProperty(name: "key2")]
            private string|null $key2,
            #[JsonProperty(name: "key3")]
            private bool $key3,
            private mixed $ignoredKey = null
        ) {}

        public function getKey1(): int { return $this->key1; }
        public function getKey2(): string|null { return $this->key2; }
        public function getKey3(): bool { return $this->key3; }
        public function getIgnoredKey(): mixed { return $this->ignoredKey; }
    }

    it("serializes simple primitive values", function () {
        $instance = new ClassSerializeTestCase2(10, "hello", true);

        $json = JsonObjectMapper::unwrapper($instance);

        expect($json)->toBeJson();
        expect(json_decode($json, true))->toMatchArray([
            "key1" => 10,
            "key2" => "hello",
            "key3" => true,
        ]);
    });

    it("serializes nullable string properly", function () {
        $instance = new ClassSerializeTestCase2(5, null, false);

        $json = JsonObjectMapper::unwrapper($instance);

        expect(json_decode($json, true))->toMatchArray([
            "key1" => 5,
            "key2" => null,
            "key3" => false,
        ]);
    });

    it("ignores fields without JsonProperty attribute", function () {
        $instance = new ClassSerializeTestCase2(1, "abc", true, "ignored");

        $jsonArray = json_decode(JsonObjectMapper::unwrapper($instance), true);

        expect($jsonArray)->not->toHaveKey("ignoredKey");
    });

    it("ignores non-primitive properties automatically", function () {

        // Criar uma classe com tipos não primitivos
        class NonPrimitiveTest {
            #[JsonProperty(name: "valid")]
            public int $valid = 100;

            #[JsonProperty(name: "ignoredObject")]
            public DateTime $ignoredObject;

            #[JsonProperty(name: "ignoredClass")]
            public stdClass $ignoredClass;

            public function __construct()
            {
                $this->ignoredObject = new DateTime();
                $this->ignoredClass = new stdClass();
            }
        }

        $instance = new NonPrimitiveTest();

        $json = JsonObjectMapper::unwrapper($instance);
        $decoded = json_decode($json, true);

        expect($decoded)->toMatchArray([
            "valid" => 100,
        ]);

        expect($decoded)->not->toHaveKey("ignoredObject");
        expect($decoded)->not->toHaveKey("ignoredClass");
    });

    it("serializes edge case primitive values", function () {
        $instance = new ClassSerializeTestCase2(0, "", false);

        $json = JsonObjectMapper::unwrapper($instance);
        $decoded = json_decode($json, true);

        expect($decoded)->toMatchArray([
            "key1" => 0,
            "key2" => "",
            "key3" => false,
        ]);
    });

    it("handles special characters in strings", function () {
        $instance = new ClassSerializeTestCase2(
            1,
            "line\nbreak \"quoted\" çáéíú",
            true
        );

        $json = JsonObjectMapper::unwrapper($instance);
        $decoded = json_decode($json, true);

        expect($decoded["key2"])->toBe("line\nbreak \"quoted\" çáéíú");
    });

    it("serializes with custom JsonProperty name", function () {

        class CustomNameTest {
            #[JsonProperty(name: "externalName")]
            private int $internalName = 77;
        }

        $json = JsonObjectMapper::unwrapper(new CustomNameTest());
        $decoded = json_decode($json, true);

        expect($decoded)->toMatchArray([
            "externalName" => 77,
        ]);
    });

});
