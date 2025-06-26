<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Validator\Financial;

use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;
use Locale;
use ResourceBundle;

final class BicRule implements ValidatorRuleInterface
{
    private array $countries;

    public function __construct()
    {
        $this->countries = $this->getCountries();
    }

    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_string($value)) {
            throw new ValidationException('Value must be a string.');
        }

        $value = strtoupper($value);

        // Format: BANKCODE + COUNTRYCODE + LOCATION + BRANCH,
        // BANKCODE: 4 chars, COUNTRYCODE: 2 chars, LOCATION: 2 chars, BRANCH: 3 chars (optional),
        if (!preg_match('/^[A-Z]{4}[A-Z]{2}[A-Z0-9]{2}([A-Z0-9]{3})?$/', $value)) {
            throw new ValidationException('Invalid BIC format.');
        }

        // Verificar código de país,
        $countryCode = substr($value, 4, 2);
        if (!in_array($countryCode, $this->countries)) {
            throw new ValidationException('Invalid country code in BIC.');
        }
    }

    private function getCountries(): array
    {
        $locales = ResourceBundle::getLocales('');
        $countries = [];
        foreach ($locales as $locale) {
            $code = Locale::getRegion($locale);
            if (strlen($code) === 2) {
                $countries[] = strtoupper($code);
            }
        }
        return array_unique($countries);
    }
}
