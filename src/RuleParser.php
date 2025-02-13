<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor;

use Derafu\DataProcessor\Contract\RuleParserInterface;

/**
 * Resolves rule strings into rule instances with parameters.
 */
final class RuleParser implements RuleParserInterface
{
    /**
     * Map of short type names to full names.
     */
    private const TYPE_MAP = [
        't' => 'transform',
        's' => 'sanitize',
        'c' => 'cast',
        'v' => 'validate',
    ];

    /**
     * {@inheritDoc}
     */
    public function parse(string|array $rules): array
    {
        if (is_array($rules)) {
            return $this->parseArray($rules);
        }
        return $this->parseString($rules);
    }

    /**
     * Parses rules from array format.
     *
     * @param array $rules
     * @return array
     */
    private function parseArray(array $rules): array
    {
        $parsed = [];

        foreach ($rules as $type => $typeRules) {
            if (!is_string($typeRules)) {
                $parsed[$type] = $typeRules;
                continue;
            }
            if ($type === 'cast') {
                $parsed[$type] = $typeRules;
                continue;
            }
            $parsed[$type] = explode('|', $typeRules);
        }

        return $parsed;
    }

    /**
     * Parses rules from string format.
     *
     * @param string $rules
     * @return array
     */
    private function parseString(string $rules): array
    {
        $parsed = [
            'transform' => [],
            'sanitize' => [],
            'cast' => null,
            'validate' => [],
        ];

        $parts = preg_split('/\s+/', trim($rules));

        foreach ($parts as $part) {
            if (preg_match('/^([tsv])\\(([^)]*)\\)$/', $part, $matches)) {
                // Handle transform, sanitize and validate groups:
                // t(rule1|rule2), s(rule1|rule2), v(rule1|rule2)
                $type = self::TYPE_MAP[$matches[1]];
                // Solo agregar si hay reglas.
                if (!empty($matches[2])) {
                    $parsed[$type] = explode('|', $matches[2]);
                }
            } elseif (preg_match('/^c\\(([^)]*)\\)$/', $part, $matches)) {
                // Handle cast: c(string)
                // Solo asignar si hay un tipo.
                if (!empty($matches[1])) {
                    $parsed['cast'] = $matches[1];
                }
            } else {
                // Default to validate: rule1|rule2
                $parsed['validate'] = array_merge(
                    $parsed['validate'],
                    explode('|', $part)
                );
            }
        }

        return array_filter($parsed);
    }
}
