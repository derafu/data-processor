<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Transformer;

use Derafu\DataProcessor\Exception\TransformationException;
use Derafu\DataProcessor\Rule\Transformer\Json\JsonToObjectRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonToObjectRule::class)]
final class JsonToObjectRuleTest extends TestCase
{
    private JsonToObjectRule $rule;

    protected function setUp(): void
    {
        $this->rule = new JsonToObjectRule();
    }

    #[DataProvider('objectCastDataProvider')]
    public function testObjectCast(mixed $input, array $expectedProperties): void
    {
        $result = $this->rule->transform($input);
        $this->assertIsObject($result);
        foreach ($expectedProperties as $property => $value) {
            $this->assertObjectHasProperty($property, $result);
            $this->assertSame(
                json_encode($value),
                json_encode($result->$property)
            );
        }
    }

    #[DataProvider('invalidObjectCastDataProvider')]
    public function testInvalidObjectCast(mixed $input): void
    {
        $this->expectException(TransformationException::class);
        $this->rule->transform($input);
    }

    public static function objectCastDataProvider(): array
    {
        return [
            'json_object' => [
                '{"name":"John","age":30}',
                ['name' => 'John', 'age' => 30],
            ],
            'json_nested' => [
                '{"user":{"name":"John","age":30}}',
                ['user' => (object)['name' => 'John', 'age' => 30]],
            ],
            'existing_object' => [
                (object)['name' => 'John', 'age' => 30],
                ['name' => 'John', 'age' => 30],
            ],
            'empty_object' => [
                '{}',
                [],
            ],
        ];
    }

    public static function invalidObjectCastDataProvider(): array
    {
        return [
            'json_array' => ['[1,2,3]'],
            'invalid_json' => ['not json'],
            'null' => [null],
            'number' => [123],
            'boolean' => [true],
            'array' => [['name' => 'John']],
        ];
    }
}
