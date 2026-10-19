<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Validator;

use DateTimeImmutable;
use Derafu\DataProcessor\Abstract\Rule\Validator\Date\AbstractDateRule;
use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\DataProcessor\Rule\Validator\Date\AfterOrEqualRule;
use Derafu\DataProcessor\Rule\Validator\Date\AfterRule;
use Derafu\DataProcessor\Rule\Validator\Date\BeforeOrEqualRule;
use Derafu\DataProcessor\Rule\Validator\Date\BeforeRule;
use Derafu\DataProcessor\Rule\Validator\Date\BetweenRule;
use Derafu\DataProcessor\Rule\Validator\Date\DateEqualsRule;
use Derafu\DataProcessor\Rule\Validator\Date\WeekdayRule;
use Derafu\DataProcessor\Rule\Validator\Date\WeekendRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * The value that a date validator receives is a string or a date: any other
 * value is not a date, and it is not given to the parser of dates.
 */
#[CoversClass(AbstractDateRule::class)]
#[CoversClass(AfterOrEqualRule::class)]
#[CoversClass(AfterRule::class)]
#[CoversClass(BeforeOrEqualRule::class)]
#[CoversClass(BeforeRule::class)]
#[CoversClass(BetweenRule::class)]
#[CoversClass(DateEqualsRule::class)]
#[CoversClass(WeekdayRule::class)]
#[CoversClass(WeekendRule::class)]
final class DateRulesInputTest extends TestCase
{
    /**
     * @return array<string, array{ValidatorRuleInterface, list<string>}>
     */
    public static function rulesProvider(): array
    {
        return [
            'after or equal' => [new AfterOrEqualRule(), ['2024-01-01']],
            'after' => [new AfterRule(), ['2024-01-01']],
            'before or equal' => [new BeforeOrEqualRule(), ['2024-01-01']],
            'before' => [new BeforeRule(), ['2024-01-01']],
            'between' => [new BetweenRule(), ['2024-01-01', '2024-12-31']],
            'date equals' => [new DateEqualsRule(), ['2024-06-15']],
            'weekday' => [new WeekdayRule(), []],
            'weekend' => [new WeekendRule(), []],
        ];
    }

    /**
     * @param list<string> $parameters
     */
    #[Test]
    #[DataProvider('rulesProvider')]
    public function shouldRejectAValueThatIsNotADate(ValidatorRuleInterface $rule, array $parameters): void
    {
        foreach ([5, 1.5, true, [], new stdClass()] as $value) {
            try {
                $rule->validate($value, $parameters);
                $this->fail('A value that is not a date must be rejected.');
            } catch (ValidationException $e) {
                $this->assertSame('Invalid date format.', $e->getMessage());
            }
        }
    }

    /**
     * @param list<string> $parameters
     */
    #[Test]
    #[DataProvider('rulesProvider')]
    public function shouldRejectAStringThatIsNotADate(ValidatorRuleInterface $rule, array $parameters): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid date format.');

        $rule->validate('not a date at all', $parameters);
    }

    #[Test]
    public function shouldAcceptAnObjectThatIsADate(): void
    {
        (new BetweenRule())->validate(new DateTimeImmutable('2024-06-15'), ['2024-01-01', '2024-12-31']);
        (new WeekdayRule())->validate(new DateTimeImmutable('2024-06-14'));
        (new WeekendRule())->validate(new DateTimeImmutable('2024-06-15'));

        $this->addToAssertionCount(3);
    }
}
