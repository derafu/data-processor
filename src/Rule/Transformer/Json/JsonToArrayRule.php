<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Transformer\Json;

use Derafu\DataProcessor\Contract\TransformerRuleInterface;
use Derafu\DataProcessor\Exception\TransformationException;
use JsonException;

final class JsonToArrayRule implements TransformerRuleInterface
{
    public function transform(mixed $value, array $parameters = []): array
    {
        if (is_string($value)) {
            try {
                $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                if (!is_array($decoded)) {
                    throw new TransformationException('JSON decoded value is not an array.');
                }
                return $decoded;
            } catch (JsonException $e) {
                throw new TransformationException('Invalid JSON string: ' . $e->getMessage());
            }
        }

        if (is_array($value)) {
            return $value;
        }

        throw new TransformationException(sprintf(
            'Cannot transform value of type "%s" to array.',
            get_debug_type($value)
        ));
    }
}
