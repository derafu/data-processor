<?php

declare(strict_types=1);

/**
 * Derafu: Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Transformer\Json;

use Derafu\DataProcessor\Contract\TransformerRuleInterface;
use Derafu\DataProcessor\Exception\TransformationException;
use JsonException;

final class JsonDecodeRule implements TransformerRuleInterface
{
    public function transform(mixed $value, array $parameters = []): mixed
    {
        if (!is_string($value)) {
            throw new TransformationException('Value must be string.');
        }

        $assoc = isset($parameters[0]) && $parameters[0] === 'array';
        try {
            return json_decode($value, $assoc, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new TransformationException(
                'Error decoding the value from JSON: ' . $e->getMessage()
            );
        }
    }
}
