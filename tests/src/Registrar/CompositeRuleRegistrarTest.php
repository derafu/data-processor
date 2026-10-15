<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Registrar;

use ArrayIterator;
use Derafu\DataProcessor\Contract\RuleRegistrarInterface;
use Derafu\DataProcessor\Contract\RuleRegistryInterface;
use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\DataProcessor\Processor;
use Derafu\DataProcessor\ProcessorFactory;
use Derafu\DataProcessor\Registrar\CompositeRuleRegistrar;
use Derafu\DataProcessor\Registrar\DefaultRuleRegistrar;
use Derafu\DataProcessor\Rule\Validator\String\MinLengthRule;
use Derafu\DataProcessor\RuleParser;
use Derafu\DataProcessor\RuleRegistry;
use Derafu\DataProcessor\RuleResolver;
use Derafu\DataProcessor\RuleSet;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * The registrars of the packages are applied together, in order.
 */
#[CoversClass(CompositeRuleRegistrar::class)]
#[CoversClass(ProcessorFactory::class)]
#[CoversClass(DefaultRuleRegistrar::class)]
#[CoversClass(RuleRegistry::class)]
#[CoversClass(Processor::class)]
#[CoversClass(RuleParser::class)]
#[CoversClass(RuleResolver::class)]
#[CoversClass(MinLengthRule::class)]
#[UsesClass(RuleSet::class)]
final class CompositeRuleRegistrarTest extends TestCase
{
    public function testWithoutRegistrarsItRegistersNothing(): void
    {
        $registry = $this->createMock(RuleRegistryInterface::class);
        $registry->expects($this->never())->method($this->anything());

        (new CompositeRuleRegistrar())->register($registry);
    }

    public function testItAppliesEveryRegistrarInOrder(): void
    {
        $calls = [];
        $registrars = [];
        foreach (['first', 'second', 'third'] as $name) {
            $registrar = $this->createMock(RuleRegistrarInterface::class);
            $registrar->expects($this->once())->method('register')->willReturnCallback(
                function () use (&$calls, $name): void {
                    $calls[] = $name;
                }
            );
            $registrars[] = $registrar;
        }

        (new CompositeRuleRegistrar($registrars))->register(new RuleRegistry());

        $this->assertSame(['first', 'second', 'third'], $calls);
    }

    public function testItAcceptsAnyIterable(): void
    {
        $registrar = new class () implements RuleRegistrarInterface {
            public function register(RuleRegistryInterface $registry): void
            {
                $registry->addValidatorRule('always', AlwaysRule::class);
            }
        };

        // What a tagged_iterator of the container gives is not an array.
        $generator = (static function () use ($registrar): Generator {
            yield $registrar;
        })();

        foreach ([new ArrayIterator([$registrar]), $generator] as $registrars) {
            $registry = new RuleRegistry();
            (new CompositeRuleRegistrar($registrars))->register($registry);

            $this->assertInstanceOf(AlwaysRule::class, $registry->getValidatorRule('always'));
        }
    }

    public function testTheLastRegistrarReplacesARuleWithTheSameName(): void
    {
        $registry = new RuleRegistry();

        (new CompositeRuleRegistrar([
            $this->registrar('rule', AlwaysRule::class),
            $this->registrar('rule', MinLengthRule::class),
        ]))->register($registry);

        $this->assertInstanceOf(MinLengthRule::class, $registry->getValidatorRule('rule'));
    }

    public function testTheProcessorHasTheDefaultRulesAndTheOnesOfTheRegistrars(): void
    {
        $processor = ProcessorFactory::create(new CompositeRuleRegistrar([
            $this->registrar('always', AlwaysRule::class),
        ]));

        // A rule of the package and the one of the registrar.
        $this->assertSame('abc', $processor->process('abc', [
            'validate' => ['min_length:3', 'always'],
        ]));

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Always fails.');
        $processor->process('abc', ['validate' => ['min_length:3', 'always:fail']]);
    }

    /**
     * @param class-string<ValidatorRuleInterface> $rule
     */
    private function registrar(string $name, string $rule): RuleRegistrarInterface
    {
        return new class ($name, $rule) implements RuleRegistrarInterface {
            /**
             * @param class-string<ValidatorRuleInterface> $rule
             */
            public function __construct(
                private readonly string $name,
                private readonly string $rule
            ) {
            }

            public function register(RuleRegistryInterface $registry): void
            {
                $registry->addValidatorRule($this->name, $this->rule);
            }
        };
    }
}
