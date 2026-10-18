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

/**
 * A rule whose parameter is one literal, not a list.
 *
 * The parameters of a rule are what follows the first `:` of its string, and
 * are split by `,`. That is right for the rules that take a list or several
 * values (`in:a,b`, `between:1,5`), and wrong for the ones that take one text
 * that can have a comma in it (`regex:/^\d{1,3}$/`, `date_format:d, M Y`).
 *
 * The resolver does not split the parameter of a rule that implements this: the
 * rule gets all of it as its only parameter, in the index `0`. The rule gets no
 * parameters if its string has no `:`.
 */
interface RawParameterInterface
{
}
