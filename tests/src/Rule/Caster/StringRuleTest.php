<?php

declare(strict_types=1);

/**
 * Derafu: Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Caster;

use Derafu\DataProcessor\Exception\CastingException;
use Derafu\DataProcessor\Rule\Caster\StringRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(StringRule::class)]
final class StringRuleTest extends TestCase
{
    private StringRule $rule;

    protected function setUp(): void
    {
        $this->rule = new StringRule();
    }

    #[DataProvider('validStringCastDataProvider')]
    public function testValidStringCast(mixed $input, string $expected): void
    {
        $result = $this->rule->cast($input);
        $this->assertSame($expected, $result);
    }

    #[DataProvider('invalidStringCastDataProvider')]
    public function testInvalidStringCast(mixed $input): void
    {
        $this->expectException(CastingException::class);
        $this->rule->cast($input);
    }

    public static function validStringCastDataProvider(): array
    {
        return [
            'integer' => [123, '123'],
            'negative_integer' => [-123, '-123'],
            'zero' => [0, '0'],
            'float' => [123.45, '123.45'],
            'negative_float' => [-123.45, '-123.45'],
            'boolean_true' => [true, 'true'],
            'boolean_false' => [false, 'false'],
            'string' => ['hello', 'hello'],
            'empty_string' => ['', ''],
            'null' => [null, ''],
            // 'object_with_toString' => [
            //     new class {
            //         public function __toString() {
            //             return 'custom string';
            //         }
            //     },
            //     'custom string'
            // ],
        ];
    }

    public static function invalidStringCastDataProvider(): array
    {
        return [
            'array' => [[1,2,3]],
            //'object_without_toString' => [new stdClass()],
            //'resource' => [fopen('php://memory', 'r')],
        ];
    }

    // protected function tearDown(): void
    // {
    //     // Cerrar cualquier recurso abierto en las pruebas.
    //     foreach (self::invalidStringCastDataProvider() as $data) {
    //         if (isset($data[0]) && is_resource($data[0])) {
    //             fclose($data[0]);
    //         }
    //     }
    // }
}
