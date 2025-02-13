<?php

declare(strict_types=1);

/**
 * Derafu: Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
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

        // Convertir a ASCII.
        $value = transliterator_transliterate('Any-Latin; Latin-ASCII', $value);
        if ($value === false) {
            throw new TransformationException('Error converting the value to ASCII.');
        }

        // Convertir a minúsculas.
        $value = mb_strtolower($value);

        // Reemplazar espacios y caracteres especiales por guiones.
        $value = preg_replace('/[^a-z0-9-]/', '-', $value);

        // Eliminar guiones múltiples.
        $value = preg_replace('/-+/', '-', $value);

        // Eliminar guiones al inicio y final.
        return trim($value, '-');
    }
}
