<?php

declare(strict_types=1);

/**
 * Derafu: Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Validator\I18n;

use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;
use Locale;
use ResourceBundle;

final class CountryRule implements ValidatorRuleInterface
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

        if (!in_array(strtoupper($value), $this->countries)) {
            throw new ValidationException('Invalid country code.');
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
