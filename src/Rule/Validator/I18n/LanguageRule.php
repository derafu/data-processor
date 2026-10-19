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
use Locale;
use ResourceBundle;

/**
 * Validates a language code, with an optional region: `es`, `fil`, `es_CL` or
 * `es-CL`.
 *
 * The language has 2 or 3 letters and the region 2 letters (a country), with
 * `_` or `-` between them. The value is not normalized, it is only validated.
 *
 * The codes are the ones of the locales of ICU, as the intl extension reads
 * them (`ResourceBundle::getLocales('')`, with the data of CLDR), so the list
 * depends on the version of ICU that PHP uses. The language codes are ISO 639
 * and the regions ISO 3166-1 alpha-2, as in BCP 47.
 *
 * @see https://unicode-org.github.io/icu/
 * @see https://cldr.unicode.org/
 */
final class LanguageRule implements ValidatorRuleInterface
{
    /**
     * @var list<string>
     */
    private array $languages = [];

    /**
     * @var list<string>
     */
    private array $regions = [];

    public function __construct()
    {
        $locales = ResourceBundle::getLocales('');
        $locales = $locales === false ? throw new ProcessorException('The list of locales can not be read.') : $locales;

        foreach ($locales as $locale) {
            $language = Locale::getPrimaryLanguage($locale) ?? '';
            if (strlen($language) === 2 || strlen($language) === 3) {
                $this->languages[] = strtolower($language);
            }

            $region = Locale::getRegion($locale) ?? '';
            if (strlen($region) === 2) {
                $this->regions[] = strtoupper($region);
            }
        }

        $this->languages = array_values(array_unique($this->languages));
        $this->regions = array_values(array_unique($this->regions));
    }

    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_string($value)) {
            throw new ValidationException('Value must be a string.');
        }

        if (
            !preg_match('/^([a-z]{2,3})(?:[_-]([a-z]{2}))?$/i', $value, $parts)
            || !in_array(strtolower($parts[1]), $this->languages, true)
            || (isset($parts[2]) && !in_array(strtoupper($parts[2]), $this->regions, true))
        ) {
            throw new ValidationException('Invalid language code.');
        }
    }
}
