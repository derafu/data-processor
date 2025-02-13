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

final class JsonEncodeRule implements TransformerRuleInterface
{
    public function transform(mixed $value, array $parameters = []): mixed
    {
        $options =
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_THROW_ON_ERROR
        ;

        if (isset($parameters[0]) && $parameters[0] === 'pretty') {
            $options |= JSON_PRETTY_PRINT;
        }

        try {
            return json_encode($value, $options);
        } catch (JsonException $e) {
            throw new TransformationException(
                'Error encoding the value to JSON: ' . $e->getMessage()
            );
        }
    }
}
