<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Sanitizer;

use Derafu\DataProcessor\Exception\SanitizationException;
use Derafu\DataProcessor\Rule\Sanitizer\RemovePrefixRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemovePrefixRule::class)]
final class RemovePrefixRuleTest extends TestCase
{
    private RemovePrefixRule $rule;

    protected function setUp(): void
    {
        $this->rule = new RemovePrefixRule();
    }

    #[DataProvider('removePrefixDataProvider')]
    public function testRemovePrefix(string $input, array $parameters, string $expected): void
    {
        $result = $this->rule->sanitize($input, $parameters);
        $this->assertSame($expected, $result);
    }

    public function testNonStringValue(): void
    {
        $this->expectException(SanitizationException::class);
        $value = 123;
        $result = $this->rule->sanitize($value);
    }

    public static function removePrefixDataProvider(): array
    {
        return [
            'single_prefix' => [
                'prefixHello',
                ['prefix'],
                'Hello',
            ],
            'multiple_prefixes' => [
                'httpExample',
                ['https', 'http'],
                'Example',
            ],
            'no_matching_prefix' => [
                'Hello World',
                ['prefix'],
                'Hello World',
            ],
            'empty_string' => [
                '',
                ['prefix'],
                '',
            ],
            'prefix_equals_string' => [
                'prefix',
                ['prefix'],
                '',
            ],
            'case_sensitive' => [
                'PREFIXHello',
                ['prefix'],
                'PREFIXHello',
            ],
        ];
    }
}
