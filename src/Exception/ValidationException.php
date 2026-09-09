<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Exception;

use Derafu\Translation\Contract\TranslatableInterface;
use Derafu\Translation\Trait\TranslatableExceptionTrait;

/**
 * Exception thrown when a value fails validation.
 *
 * The message can be a plain string (used as-is and as the translation key),
 * an array (`[messageId, ...icuParameters]`, e.g.
 * `['Must be between {min} and {max}.', 'min' => 1, 'max' => 10]`), or a
 * `TranslatableInterface` instance. Without a translator, `getMessage()`
 * already returns the fully formatted English message (ICU formatting is
 * applied eagerly), so this remains a drop-in replacement for the previous,
 * plain-string-only constructor.
 */
class ValidationException extends ProcessorException implements TranslatableInterface
{
    use TranslatableExceptionTrait;
}
