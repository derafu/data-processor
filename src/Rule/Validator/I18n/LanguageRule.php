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
use Derafu\DataProcessor\Exception\ValidationException;
use Locale;
use ResourceBundle;

final class LanguageRule implements ValidatorRuleInterface
{
    private array $languages;

    public function __construct()
    {
        $this->languages = $this->getLanguages();
    }

    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_string($value)) {
            throw new ValidationException('Value must be a string.');
        }

        // Aceptar códigos de idioma con región (ej: es_CL).
        $languageCode = explode('_', $value)[0];

        if (!in_array(strtolower($languageCode), $this->languages)) {
            throw new ValidationException('Invalid language code.');
        }
    }

    private function getLanguages(): array
    {
        $locales = ResourceBundle::getLocales('');
        $languages = [];
        foreach ($locales as $locale) {
            $code = Locale::getPrimaryLanguage($locale);
            if (strlen($code) === 2) {
                $languages[] = strtolower($code);
            }
        }
        return array_unique($languages);
    }
}
