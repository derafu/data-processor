<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Validator\Array;

use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\DataProcessor\Rule\Validator\Array\DependentRequiredRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(DependentRequiredRule::class)]
final class DependentRequiredRuleTest extends TestCase
{
    /**
     * @param list<string> $parameters
     */
    #[DataProvider('dataProvider')]
    public function testValidate(mixed $value, array $parameters, ?string $message): void
    {
        $rule = new DependentRequiredRule();

        if ($message !== null) {
            $this->expectException(ValidationException::class);
            $this->expectExceptionMessage($message);
        }

        $rule->validate($value, $parameters);

        if ($message === null) {
            $this->addToAssertionCount(1);
        }
    }

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function dataProvider(): array
    {
        return [
            'trigger_and_dependents' => [['card' => '4111', 'address' => 'Calle 1', 'zip' => '1'], ['card', 'address', 'zip'], null],
            'trigger_is_missing' => [['address' => ''], ['card', 'address'], null],
            'trigger_is_empty' => [['card' => '', 'address' => ''], ['card', 'address'], null],
            'trigger_is_null' => [['card' => null], ['card', 'address'], null],
            'dependent_is_false_it_has_a_value' => [['card' => '1', 'address' => false], ['card', 'address'], null],
            'dependent_is_empty' => [['card' => '4111', 'address' => ''], ['card', 'address'], 'Properties required when card is present: address.'],
            'dependent_is_missing' => [['card' => '4111'], ['card', 'address', 'zip'], 'Properties required when card is present: address, zip.'],
            'not_an_array' => ['x', ['card', 'address'], 'Value must be an array.'],
            'without_dependents' => [['card' => '1'], ['card'], 'Choices must be specified.'],
            'without_parameters' => [['card' => '1'], [], 'Choices must be specified.'],
        ];
    }
}
