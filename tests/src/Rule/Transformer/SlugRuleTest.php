<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Transformer;

use Derafu\DataProcessor\Exception\TransformationException;
use Derafu\DataProcessor\Rule\Transformer\String\SlugRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SlugRule::class)]
final class SlugRuleTest extends TestCase
{
    private SlugRule $rule;

    protected function setUp(): void
    {
        $this->rule = new SlugRule();
    }

    #[DataProvider('slugDataProvider')]
    public function testSlug(mixed $input, mixed $expected): void
    {
        $result = $this->rule->transform($input);
        $this->assertSame($expected, $result);
    }

    public static function slugDataProvider(): array
    {
        return [
            'simple_string' => ['Hello World', 'hello-world'],
            'special_chars' => ['Hello@#$World', 'hello-world'],
            'accented_chars' => ['José María', 'jose-maria'],
            'multiple_spaces' => ['Hello   World', 'hello-world'],
            'multiple_dashes' => ['hello---world', 'hello-world'],
            'trim_dashes' => ['-hello-world-', 'hello-world'],
            'numbers' => ['Hello 123 World', 'hello-123-world'],
            'empty_string' => ['', ''],
        ];
    }

    #[DataProvider('invalidSlugDataProvider')]
    public function testInvalidSlug(mixed $input): void
    {
        $this->expectException(TransformationException::class);
        $result = $this->rule->transform($input);
    }

    public static function invalidSlugDataProvider(): array
    {
        return [
            'non_string' => [123],
        ];
    }
}
