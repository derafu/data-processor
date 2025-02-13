<?php

declare(strict_types=1);

/**
 * Derafu: Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Transformer;

use Derafu\DataProcessor\Exception\TransformationException;
use Derafu\DataProcessor\Rule\Transformer\Json\JsonToArrayRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonToArrayRule::class)]
final class JsonToArrayRuleTest extends TestCase
{
    private JsonToArrayRule $rule;

    protected function setUp(): void
    {
        $this->rule = new JsonToArrayRule();
    }

    #[DataProvider('arrayCastDataProvider')]
    public function testArrayCast(mixed $input, array $expected): void
    {
        $result = $this->rule->transform($input);
        $this->assertSame($expected, $result);
    }

    #[DataProvider('invalidArrayCastDataProvider')]
    public function testInvalidArrayCast(mixed $input): void
    {
        $this->expectException(TransformationException::class);
        $this->rule->transform($input);
    }

    public static function arrayCastDataProvider(): array
    {
        return [
            'json_array' => ['[1,2,3]', [1,2,3]],
            'json_object' => ['{"a":1,"b":2}', ['a' => 1,'b' => 2]],
            'empty_json_array' => ['[]', []],
            'array' => [[1,2,3], [1,2,3]],
        ];
    }

    public static function invalidArrayCastDataProvider(): array
    {
        return [
            'invalid_json' => ['not json'],
            'number' => [123],
            'boolean' => [true],
            'null' => [null],
        ];
    }
}
