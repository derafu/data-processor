<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Validator\Internet;

use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;

/**
 * Validates a URL.
 *
 * Without parameters any scheme is valid. With parameters, only those schemes
 * are valid (`url:http,https`). A host with international characters is
 * converted to ASCII (IDNA) before the validation, so `http://münchen.de` is a
 * valid URL.
 */
final class UrlRule implements ValidatorRuleInterface
{
    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_string($value)) {
            throw new ValidationException('Value must be a string.');
        }

        $schemes = $this->schemes($parameters);

        $url = $this->toAscii($value);
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new ValidationException('Invalid URL format.');
        }

        if ($schemes !== [] && !in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), $schemes, true)) {
            throw new ValidationException('Invalid URL format.');
        }
    }

    /**
     * Reads the schemes that are allowed.
     *
     * @param list<string> $parameters The parameters of the rule.
     * @return list<string> The schemes, in lowercase.
     * @throws ValidationException If a parameter is not a scheme (RFC 3986).
     */
    private function schemes(array $parameters): array
    {
        $schemes = [];
        foreach ($parameters as $parameter) {
            if (!preg_match('/^[a-z][a-z0-9+.-]*$/i', $parameter)) {
                throw new ValidationException([
                    'Invalid parameter: {parameter}',
                    'parameter' => $parameter,
                ]);
            }
            $schemes[] = strtolower($parameter);
        }

        return $schemes;
    }

    /**
     * Converts the host of the URL to ASCII, if it has international
     * characters.
     *
     * @param string $url The URL.
     * @return string The URL, with the host in ASCII.
     */
    private function toAscii(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!is_string($host) || !preg_match('/[^\x00-\x7F]/', $host)) {
            return $url;
        }

        $ascii = idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
        if ($ascii === false) {
            return $url;
        }

        return substr_replace($url, $ascii, (int) strpos($url, $host), strlen($host));
    }
}
