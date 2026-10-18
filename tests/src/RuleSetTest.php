<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor;

use Derafu\DataProcessor\Exception\ProcessorException;
use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\DataProcessor\Processor;
use Derafu\DataProcessor\ProcessorFactory;
use Derafu\DataProcessor\Registrar\DefaultRuleRegistrar;
use Derafu\DataProcessor\Rule\Caster\StringRule;
use Derafu\DataProcessor\Rule\Sanitizer\TrimRule;
use Derafu\DataProcessor\Rule\Transformer\String\LowercaseRule;
use Derafu\DataProcessor\Rule\Validator\String\MinLengthRule;
use Derafu\DataProcessor\RuleParser;
use Derafu\DataProcessor\RuleRegistry;
use Derafu\DataProcessor\RuleResolver;
use Derafu\DataProcessor\RuleSet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * The rules in array format are validated when they are read: a category that
 * does not exist or a value that is not what the category has is an error, and
 * not a rule that is ignored.
 */
#[CoversClass(RuleSet::class)]
#[CoversClass(RuleParser::class)]
#[CoversClass(Processor::class)]
#[UsesClass(ProcessorFactory::class)]
#[UsesClass(DefaultRuleRegistrar::class)]
#[UsesClass(RuleRegistry::class)]
#[UsesClass(RuleResolver::class)]
#[UsesClass(StringRule::class)]
#[UsesClass(TrimRule::class)]
#[UsesClass(LowercaseRule::class)]
#[UsesClass(MinLengthRule::class)]
final class RuleSetTest extends TestCase
{
    #[Test]
    public function shouldReadTheCategoriesAsListsOrAsStringsSeparatedByPipes(): void
    {
        $set = RuleSet::fromArray([
            'transform' => 'lowercase|trim',
            'sanitize' => ['strip_tags'],
            'cast' => 'string',
            'validate' => 'required|email',
        ]);

        $this->assertSame('string', $set->cast);
        $this->assertSame(['lowercase', 'trim'], $set->transform);
        $this->assertSame(['strip_tags'], $set->sanitize);
        $this->assertSame(['required', 'email'], $set->validate);
    }

    #[Test]
    public function shouldWriteTheRulesInTheOrderOfTheCategories(): void
    {
        $set = RuleSet::fromArray([
            'validate' => ['required'],
            'cast' => 'integer',
            'sanitize' => ['spaces'],
            'transform' => ['lowercase'],
        ]);

        $this->assertSame(
            ['transform' => ['lowercase'], 'sanitize' => ['spaces'], 'cast' => 'integer', 'validate' => ['required']],
            $set->toArray()
        );
    }

    #[Test]
    public function shouldTakeNullAndEmptyCategoriesAsAbsent(): void
    {
        $set = RuleSet::fromArray(['cast' => null, 'transform' => null, 'sanitize' => [], 'validate' => []]);

        $this->assertNull($set->cast);
        $this->assertSame([], $set->transform);
        $this->assertSame([], $set->toArray());
        $this->assertSame([], RuleSet::fromArray([])->toArray());
    }

    #[Test]
    public function shouldKeepTheRulesThatAreAnEmptyString(): void
    {
        $this->assertSame([''], RuleSet::fromArray(['validate' => ''])->validate);
        $this->assertSame('', RuleSet::fromArray(['cast' => ''])->cast);
    }

    /**
     * @return array<string, array{array<array-key, mixed>, string}>
     */
    public static function wrongRulesProvider(): array
    {
        return [
            'a category that does not exist' => [
                ['validat' => ['required']],
                'The rules have a category that is not known: validat. The categories are: cast, transform, sanitize, validate.',
            ],
            'a list of rules, without categories' => [
                ['required', 'email'],
                'The rules have a category that is not known: 0. The categories are: cast, transform, sanitize, validate.',
            ],
            'a cast that is not a string' => [
                ['cast' => 5],
                'The rule of the category "cast" must be a string, int given.',
            ],
            'a cast that is a list' => [
                ['cast' => ['integer']],
                'The rule of the category "cast" must be a string, array given.',
            ],
            'rules that are an integer' => [
                ['validate' => 5],
                'The rules of the category "validate" must be a string or a list of strings, int given.',
            ],
            'a list with a rule that is not a string' => [
                ['transform' => ['trim', 5]],
                'The rules of the category "transform" must be a string or a list of strings, array given.',
            ],
            'rules that are not a list' => [
                ['sanitize' => ['a' => 'trim']],
                'The rules of the category "sanitize" must be a string or a list of strings, array given.',
            ],
            'rules that are an object' => [
                ['validate' => new stdClass()],
                'The rules of the category "validate" must be a string or a list of strings, stdClass given.',
            ],
        ];
    }

    /**
     * @param array<array-key, mixed> $rules
     */
    #[Test]
    #[DataProvider('wrongRulesProvider')]
    public function shouldRejectRulesThatAreNotValid(array $rules, string $message): void
    {
        $this->expectException(ProcessorException::class);
        $this->expectExceptionMessage($message);

        RuleSet::fromArray($rules);
    }

    #[Test]
    public function shouldRejectInvalidRulesWhenParsingAndProcessing(): void
    {
        try {
            (new RuleParser())->parse(['validat' => 'required']);
            $this->fail('The parser must reject a category that does not exist.');
        } catch (ProcessorException $e) {
            $this->assertStringStartsWith('The rules have a category that is not known: validat.', $e->getMessage());
        }

        $this->expectException(ProcessorException::class);

        ProcessorFactory::create()->process('x', ['validat' => 'required']);
    }

    #[Test]
    public function shouldProcessWithARuleSetThatWasAlreadyBuilt(): void
    {
        $processor = ProcessorFactory::create();
        $rules = new RuleSet(cast: 'string', transform: ['lowercase'], sanitize: ['trim'], validate: ['min_length:3']);

        $this->assertSame('abc', $processor->process('  ABC ', $rules));

        $this->expectException(ValidationException::class);

        $processor->process(' AB ', $rules);
    }
}
