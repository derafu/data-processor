<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Validator\String;

use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\DataProcessor\Rule\Validator\String\AbstractRegexRule;
use Derafu\DataProcessor\Rule\Validator\String\RegexPatternRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RegexPatternRule::class)]
#[UsesClass(AbstractRegexRule::class)]
final class RegexPatternRuleTest extends TestCase
{
    #[DataProvider('validProvider')]
    public function testValid(string $pattern): void
    {
        (new RegexPatternRule())->validate($pattern);
        $this->addToAssertionCount(1);
    }

    #[DataProvider('invalidProvider')]
    public function testInvalid(string $pattern): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid regular expression pattern');

        (new RegexPatternRule())->validate($pattern);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function validProvider(): array
    {
        return [
            'empty' => [''],
            'literal' => ['abc'],
            'anchors and classes' => ['^[a-z0-9_]+$'],
            'groups' => ['(a|b)+(?:c)?'],
            'with slashes' => ['^a/b/c$'],
            'with an escaped slash' => ['^a\\/b$'],
            'with an escaped backslash and a slash' => ['^a\\\\/b$'],
            'named group' => ['(?<year>\\d{4})'],
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function invalidProvider(): array
    {
        return [
            'open group' => ['('],
            'closed group' => [')'],
            'open class' => ['[a-'],
            'repeat without an expression' => ['*a'],
            'bad range' => ['[z-a]'],
            'backslash at the end' => ['a\\'],
        ];
    }

    public function testAValueThatIsNotAStringFails(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Value must be a string.');

        (new RegexPatternRule())->validate(5);
    }
}
