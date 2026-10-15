<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Validator\I18n;

use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ProcessorException;
use Derafu\DataProcessor\Exception\ValidationException;
use ResourceBundle;

final class LocaleRule implements ValidatorRuleInterface
{
    /**
     * @var array<int, string>
     */
    private array $locales;

    public function __construct()
    {
        $this->locales = $this->getLocales();
    }

    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_string($value)) {
            throw new ValidationException('Value must be a string.');
        }

        if (!in_array($value, $this->locales)) {
            throw new ValidationException('Invalid locale code.');
        }
    }

    /**
     * @return array<int, string>
     */
    private function getLocales(): array
    {
        $locales = ResourceBundle::getLocales('');

        return $locales === false ? throw new ProcessorException('The list of locales can not be read.') : $locales;
    }
}
