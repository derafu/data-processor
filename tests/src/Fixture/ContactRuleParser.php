<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Fixture;

use Derafu\DataProcessor\Contract\RuleParserInterface;
use Derafu\DataProcessor\RuleParser;

/**
 * Adds an application shorthand for processing contact email addresses.
 */
final class ContactRuleParser implements RuleParserInterface
{
    public function parse(string|array $rules): array
    {
        return (new RuleParser())->parse(
            $rules === 'contact_email'
                ? 't(lowercase) s(trim) required|email'
                : $rules
        );
    }
}
