<?php

declare(strict_types=1);

/**
 * Derafu: Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Validator;

use Derafu\DataProcessor\Contract\ProcessorInterface;
use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\DataProcessor\Processor;
use Derafu\DataProcessor\ProcessorFactory;
use Derafu\DataProcessor\Registrar\DefaultRuleRegistrar;
use Derafu\DataProcessor\Rule\Validator\String\AlphaDashRule;
use Derafu\DataProcessor\Rule\Validator\String\AlphaNumRule;
use Derafu\DataProcessor\Rule\Validator\String\AlphaRule;
use Derafu\DataProcessor\Rule\Validator\String\EndsWithRule;
use Derafu\DataProcessor\Rule\Validator\String\NotRegexRule;
use Derafu\DataProcessor\Rule\Validator\String\RegexRule;
use Derafu\DataProcessor\Rule\Validator\String\StartsWithRule;
use Derafu\DataProcessor\RuleRegistry;
use Derafu\DataProcessor\RuleResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Processor::class)]
#[CoversClass(DefaultRuleRegistrar::class)]
#[CoversClass(ProcessorFactory::class)]
#[CoversClass(RuleRegistry::class)]
#[CoversClass(RuleResolver::class)]
#[CoversClass(AlphaRule::class)]
#[CoversClass(AlphaNumRule::class)]
#[CoversClass(AlphaDashRule::class)]
#[CoversClass(StartsWithRule::class)]
#[CoversClass(EndsWithRule::class)]
#[CoversClass(RegexRule::class)]
#[CoversClass(NotRegexRule::class)]
final class StringValidationTest extends TestCase
{
    private ProcessorInterface $processor;

    protected function setUp(): void
    {
        $this->processor = ProcessorFactory::create();
    }

    #[DataProvider('stringPatternValidationProvider')]
    public function testStringPatternValidation(string $input, array $rules, bool $shouldPass): void
    {
        if (!$shouldPass) {
            $this->expectException(ValidationException::class);
        }

        $result = $this->processor->process($input, [
            'validate' => $rules,
        ]);

        if ($shouldPass) {
            $this->assertSame($input, $result);
        }
    }

    public static function stringPatternValidationProvider(): array
    {
        return [
            'valid_alpha' => [
                'HelloWorld',
                ['alpha'],
                true,
            ],
            'invalid_alpha' => [
                'Hello123',
                ['alpha'],
                false,
            ],
            'valid_alpha_num' => [
                'Hello123',
                ['alpha_num'],
                true,
            ],
            'invalid_alpha_num' => [
                'Hello_123',
                ['alpha_num'],
                false,
            ],
            'valid_alpha_dash' => [
                'Hello-123_world',
                ['alpha_dash'],
                true,
            ],
            'invalid_alpha_dash' => [
                'Hello@123',
                ['alpha_dash'],
                false,
            ],
            'valid_starts_with' => [
                'HelloWorld',
                ['starts_with:Hello'],
                true,
            ],
            'invalid_starts_with' => [
                'GoodbyeWorld',
                ['starts_with:Hello'],
                false,
            ],
            'valid_ends_with' => [
                'HelloWorld',
                ['ends_with:World'],
                true,
            ],
            'invalid_ends_with' => [
                'HelloThere',
                ['ends_with:World'],
                false,
            ],
            'valid_regex' => [
                'ABC-123',
                ['regex:/^[A-Z]+-\d+$/'],
                true,
            ],
            'invalid_regex' => [
                'abc-123',
                ['regex:/^[A-Z]+-\d+$/'],
                false,
            ],
            'valid_not_regex' => [
                'abc-123',
                ['not_regex:/^[A-Z]+-\d+$/'],
                true,
            ],
            'invalid_not_regex' => [
                'ABC-123',
                ['not_regex:/^[A-Z]+-\d+$/'],
                false,
            ],
        ];
    }
}
