<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Transformer\String;

use Derafu\DataProcessor\Contract\TransformerRuleInterface;
use Derafu\DataProcessor\Exception\TransformationException;

final class SlugRule implements TransformerRuleInterface
{
    public function transform(mixed $value, array $parameters = []): mixed
    {
        if (!is_string($value)) {
            throw new TransformationException('Value must be string.');
        }

        // Convert to ASCII.
        $value = transliterator_transliterate('Any-Latin; Latin-ASCII', $value);
        $value = $value === false ? throw new TransformationException('Error converting the value to ASCII.') : $value;

        // Convert to lowercase.
        $value = mb_strtolower($value);

        // Replace spaces and special characters with dashes.
        $value = preg_replace('/[^a-z0-9-]/', '-', $value) ?? throw new TransformationException('Error replacing the characters of the value.');

        // Remove multiple consecutive dashes.
        $value = preg_replace('/-+/', '-', $value) ?? throw new TransformationException('Error replacing the characters of the value.');

        // Trim leading and trailing dashes.
        return trim($value, '-');
    }
}
