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

use Derafu\DataProcessor\Contract\RawParameterInterface;
use Derafu\DataProcessor\Rule\Validator\Numeric\RangeRule;
use Derafu\DataProcessor\Rule\Validator\String\RegexRule;
use Derafu\DataProcessor\RuleRegistry;
use Derafu\DataProcessor\RuleResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(RuleResolver::class)]
#[CoversClass(RuleRegistry::class)]
final class RuleResolverTest extends TestCase
{
    private RuleResolver $resolver;

    protected function setUp(): void
    {
        $registry = (new RuleRegistry())
            ->addValidatorRule('regex', RegexRule::class)
            ->addValidatorRule('range', RangeRule::class)
        ;

        $this->resolver = new RuleResolver($registry);
    }

    /**
     * @param list<string> $expected
     */
    #[DataProvider('rawParameterProvider')]
    public function testARuleWithARawParameterGetsAllOfIt(string $ruleString, array $expected): void
    {
        [$rule, $parameters] = $this->resolver->resolve('validate', $ruleString);

        $this->assertInstanceOf(RawParameterInterface::class, $rule);
        $this->assertSame($expected, $parameters);
    }

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function rawParameterProvider(): array
    {
        return [
            'with_comma' => ['regex:/^\d{1,3}$/', ['/^\d{1,3}$/']],
            'with_colon' => ['regex:/^a:b$/', ['/^a:b$/']],
            'with_several_commas' => ['regex:/a,b,c/', ['/a,b,c/']],
            'without_parameter' => ['regex', []],
        ];
    }

    /**
     * @param list<string> $expected
     */
    #[DataProvider('listParameterProvider')]
    public function testARuleWithoutARawParameterGetsAListSplitByComma(string $ruleString, array $expected): void
    {
        [$rule, $parameters] = $this->resolver->resolve('validate', $ruleString);

        $this->assertNotInstanceOf(RawParameterInterface::class, $rule);
        $this->assertSame($expected, $parameters);
    }

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function listParameterProvider(): array
    {
        return [
            'two_parameters' => ['range:1,5', ['1', '5']],
            'one_parameter' => ['range:1', ['1']],
            'without_parameter' => ['range', []],
        ];
    }
}
