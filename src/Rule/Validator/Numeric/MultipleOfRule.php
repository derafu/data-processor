<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Validator\Numeric;

use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\DataProcessor\Trait\Rule\Validator\NumericParameterTrait;

/**
 * Validates that a number is a multiple of another.
 *
 * The check is exact: it does not use floating point numbers. Both numbers are
 * taken as decimal text, so `0.3` is a multiple of `0.1`.
 */
final class MultipleOfRule implements ValidatorRuleInterface
{
    use NumericParameterTrait;

    /**
     * The most digits that the multiple can have: the remainder of each step of
     * the division is less than the multiple, and it is multiplied by 10.
     */
    private const MAX_MULTIPLE_DIGITS = 17;

    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_numeric($value)) {
            throw new ValidationException('Value must be numeric.');
        }

        if (empty($parameters)) {
            throw new ValidationException('Multiple value must be specified.');
        }

        $this->numberParameter($parameters[0]);
        [$multiple, $multipleDecimals] = $this->decompose((string) $parameters[0]);

        if ($multiple === '') {
            throw new ValidationException('Multiple cannot be zero.');
        }

        if (strlen($multiple) > self::MAX_MULTIPLE_DIGITS) {
            throw new ValidationException([
                'Invalid parameter: {parameter}',
                'parameter' => (string) $parameters[0],
            ]);
        }

        if (!$this->isMultiple((string) $value, $multiple, $multipleDecimals)) {
            throw new ValidationException([
                'Must be a multiple of {value}.',
                'value' => $parameters[0],
            ]);
        }
    }

    /**
     * Tells if a number is a multiple of another.
     *
     * @param string $value The number.
     * @param string $multiple The digits of the multiple, without the point.
     * @param int $multipleDecimals The decimals of the multiple.
     * @return bool
     */
    private function isMultiple(string $value, string $multiple, int $multipleDecimals): bool
    {
        if (!is_finite((float) $value)) {
            return false;
        }

        [$digits, $decimals] = $this->decompose($value);

        // Zero is a multiple of any number.
        if ($digits === '') {
            return true;
        }

        // A multiple of a number has no more decimals than that number.
        if ($decimals > $multipleDecimals) {
            return false;
        }

        // The same decimals in both, and then the division of whole numbers.
        $digits .= str_repeat('0', $multipleDecimals - $decimals);
        $divisor = (int) $multiple;
        $remainder = 0;
        foreach (str_split($digits) as $digit) {
            $remainder = ($remainder * 10 + (int) $digit) % $divisor;
        }

        return $remainder === 0;
    }

    /**
     * Splits a number into its digits and its decimals.
     *
     * The sign is dropped, the digits have no zeros at the start, and the
     * decimals have no zeros at the end. Zero has no digits.
     *
     * @param string $number A number, as it was written (`1.50`, `-3`, `1e-2`).
     * @return array{string, int} The digits without the point, and the number
     * of decimals.
     */
    private function decompose(string $number): array
    {
        preg_match('/^[+-]?(\d*)(?:\.(\d*))?(?:[eE]([+-]?\d+))?$/', trim($number, " \t\n\r\v\f"), $matches);

        $fraction = $matches[2] ?? '';
        $digits = ($matches[1] ?? '') . $fraction;
        $decimals = strlen($fraction) - (int) ($matches[3] ?? 0);

        if ($decimals < 0) {
            $digits .= str_repeat('0', -$decimals);
            $decimals = 0;
        }

        $digits = ltrim($digits, '0');
        while ($decimals > 0 && str_ends_with($digits, '0')) {
            $digits = substr($digits, 0, -1);
            $decimals--;
        }

        return $digits === '' ? ['', 0] : [$digits, $decimals];
    }
}
