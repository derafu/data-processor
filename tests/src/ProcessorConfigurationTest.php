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

use Derafu\DataProcessor\Contract\RuleParserInterface;
use Derafu\DataProcessor\Contract\RuleRegistrarInterface;
use Derafu\DataProcessor\Contract\RuleRegistryInterface;
use Derafu\DataProcessor\Exception\RuleNotFoundException;
use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\DataProcessor\Processor;
use Derafu\DataProcessor\Registrar\CompositeRuleRegistrar;
use Derafu\DataProcessor\Registrar\DefaultRuleRegistrar;
use Derafu\DataProcessor\Rule\Transformer\String\UppercaseRule;
use Derafu\DataProcessor\Rule\Validator\String\MinLengthRule;
use Derafu\DataProcessor\Rule\Validator\String\RequiredRule;
use Derafu\DataProcessor\RuleParser;
use Derafu\DataProcessor\RuleRegistry;
use Derafu\DataProcessor\RuleResolver;
use Derafu\DataProcessor\RuleSet;
use Derafu\TestsDataProcessor\Registrar\AlwaysRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Processors use the rules registered by the application and its chosen parser.
 */
#[CoversClass(Processor::class)]
#[CoversClass(RuleParser::class)]
#[CoversClass(RuleResolver::class)]
#[CoversClass(RuleRegistry::class)]
#[CoversClass(DefaultRuleRegistrar::class)]
#[CoversClass(CompositeRuleRegistrar::class)]
#[CoversClass(MinLengthRule::class)]
#[CoversClass(UppercaseRule::class)]
#[UsesClass(RequiredRule::class)]
#[UsesClass(RuleSet::class)]
final class ProcessorConfigurationTest extends TestCase
{
    public function testDefaultRegistrarProvidesTheDefaultRules(): void
    {
        $registry = new RuleRegistry();
        (new DefaultRuleRegistrar())->register($registry);
        $processor = new Processor(new RuleResolver($registry), new RuleParser());

        $this->assertSame('ABC', $processor->process('abc', [
            'transform' => ['uppercase'],
            'validate' => ['min_length:3'],
        ]));
    }

    public function testOnlyTheRuleRequiredCountsAsRequired(): void
    {
        $registry = new RuleRegistry();
        (new DefaultRuleRegistrar())->register($registry);
        $registry->addValidatorRule('required_always', AlwaysRule::class);
        $processor = new Processor(new RuleResolver($registry), new RuleParser());

        // A rule whose name starts with `required` is not the rule `required`:
        // the blank value skips it.
        $this->assertNull($processor->process('', ['validate' => ['required_always:fail']]));

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Field is required.');

        $processor->process('', ['validate' => ['required', 'required_always:fail']]);
    }

    public function testAdditionalRulesExtendTheDefaultOnes(): void
    {
        $registry = new RuleRegistry();
        (new DefaultRuleRegistrar())->register($registry);
        $registry->addValidatorRule('always', AlwaysRule::class);
        $processor = new Processor(new RuleResolver($registry), new RuleParser());

        $this->assertSame('abc', $processor->process('abc', [
            'validate' => ['min_length:3', 'always'],
        ]));
    }

    public function testCompositeRegistrarAddsRulesToTheDefaultOnes(): void
    {
        $registry = new RuleRegistry();
        (new CompositeRuleRegistrar([
            new DefaultRuleRegistrar(),
            new class () implements RuleRegistrarInterface {
                public function register(RuleRegistryInterface $registry): void
                {
                    $registry->addValidatorRule('always', AlwaysRule::class);
                }
            },
        ]))->register($registry);
        $processor = new Processor(new RuleResolver($registry), new RuleParser());

        $this->assertSame('abc', $processor->process('abc', [
            'validate' => ['min_length:3', 'always'],
        ]));
    }

    public function testCustomRegistryHasOnlyTheRulesThatItIsGiven(): void
    {
        $registry = new RuleRegistry();
        $registry->addValidatorRule('always', AlwaysRule::class);
        $processor = new Processor(new RuleResolver($registry), new RuleParser());

        $this->assertSame('abc', $processor->process('abc', ['validate' => ['always']]));

        $this->expectException(RuleNotFoundException::class);
        $processor->process('abc', ['validate' => ['min_length:3']]);
    }

    public function testEmptyRegistryDoesNotProvideDefaultRules(): void
    {
        $processor = new Processor(new RuleResolver(new RuleRegistry()), new RuleParser());

        $this->expectException(RuleNotFoundException::class);
        $processor->process('abc', ['validate' => ['min_length:3']]);
    }

    public function testProcessorUsesTheParserThatItIsGiven(): void
    {
        $parser = $this->createMock(RuleParserInterface::class);
        $parser->expects($this->once())
            ->method('parse')
            ->with('ignored')
            ->willReturn(['validate' => ['min_length:3']])
        ;

        $registry = new RuleRegistry();
        (new DefaultRuleRegistrar())->register($registry);
        $processor = new Processor(new RuleResolver($registry), $parser);

        $this->assertSame('abc', $processor->process('abc', 'ignored'));
    }
}
