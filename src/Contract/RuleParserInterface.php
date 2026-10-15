<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Contract;

interface RuleParserInterface
{
    /**
     * Parses rules from string or array format to internal array format.
     *
     * @param string|array<string, string|list<string>> $rules Rules in string or array format.
     *   - string format: 't(lowercase|uppercase) required|email'.
     *   - array format: ['transform' => 'lowercase|uppercase'].
     * @return array{transform?: list<string>, sanitize?: list<string>, cast?: string, validate?: list<string>} Rules in internal format.
     * Eg.: ['transform' => ['lowercase', 'uppercase']].
     */
    public function parse(string|array $rules): array;
}
