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
use Derafu\DataProcessor\Rule\Transformer\Json\JsonDecodeRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonDecodeRule::class)]
final class JsonDecodeRuleTest extends TestCase
{
    private JsonDecodeRule $rule;

    protected function setUp(): void
    {
        $this->rule = new JsonDecodeRule();
    }

    #[DataProvider('ValidJsonDecodeDataProvider')]
    public function testValidJsonDecode(mixed $input, array $parameters, mixed $expected): void
    {
        $result = $this->rule->transform($input, $parameters);
        $this->assertSame(json_encode($expected), json_encode($result));
    }

    public static function ValidJsonDecodeDataProvider(): array
    {
        return [
            'decode_to_object' => [
                '{"name":"John","age":30}',
                [],
                (object)['name' => 'John', 'age' => 30],
            ],
            'decode_to_array' => [
                '{"name":"John","age":30}',
                ['array'],
                ['name' => 'John', 'age' => 30],
            ],
        ];
    }

    #[DataProvider('invalidJsonDecodeDataProvider')]
    public function testInvalidJsonDecode(mixed $input, array $parameters): void
    {
        $this->expectException(TransformationException::class);

        $result = $this->rule->transform($input, $parameters);
    }

    public static function invalidJsonDecodeDataProvider(): array
    {
        return [
            'invalid_json' => [
                'Invalid JSON',
                [],
            ],
            'non_string' => [
                123,
                [],
                123,
            ],
        ];
    }
}
