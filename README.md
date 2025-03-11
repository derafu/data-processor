# Derafu: Data Processor - Four-Phase Data Processing Library

![GitHub last commit](https://img.shields.io/github/last-commit/derafu/data-processor/main)
![CI Workflow](https://github.com/derafu/data-processor/actions/workflows/ci.yml/badge.svg?branch=main&event=push)
![GitHub code size in bytes](https://img.shields.io/github/languages/code-size/derafu/data-processor)
![GitHub Issues](https://img.shields.io/github/issues-raw/derafu/data-processor)
![Total Downloads](https://poser.pugx.org/derafu/data-processor/downloads)
![Monthly Downloads](https://poser.pugx.org/derafu/data-processor/d/monthly)

A PHP library designed to process data through four distinct phases: casting, transformation, sanitization and validation.

## What Makes it Different?

Unlike traditional validation libraries that focus solely on validation, Derafu Data Processor offers:

- **Clear Phase Separation**: Each processing phase (cast → transform → sanitize → validate) has its own purpose and runs in a specific order.
- **Independent Transformations**: A dedicated transformation layer for complex data conversions, separate from basic type casting.
- **Type-Safe Operations**: Strong typing and clear error handling in each phase.
- **Extensible Rule System**: Each type of rule (caster, transformer, sanitizer, validator) follows its own interface.
- **Almost Zero Dependencies**: just `intl` extension for specific format rules and `Carbon` for dates.

## Features

- 🎯 **Type-Safe Casting**: Convert between data types safely.
  - Basic types (string, int, float, bool).
  - Date and time handling.

- 🔄 **Data Transformations**: Complex data conversions.
  - Base64 encoding/decoding.
  - JSON processing.
  - Slug generation.
  - Character transliteration.

- 🧹 **Rich Sanitization Rules**: Clean and normalize input data.
  - String sanitization (trim, strip_tags, substring, etc.).
  - Regex-based cleaning (regex_remove, regex_keep).
  - HTML entity handling (htmlspecialchars, htmlentities, etc.).
  - Character set manipulation (remove_non_printable, remove_chars, etc.).

- ✅ **Comprehensive Validation**: Various validation rules.
  - String validations (required, min_length, max_length, etc.).
  - Number validations (range, gte, lte, etc.).
  - Date validations (after, before, between, etc.).
  - Format validations (email, URL, UUID, etc.).
  - File validations (file, image, mime_types).
  - Array validations (min_items, max_items, not_empty, etc.).

## Installation

```bash
composer require derafu/data-processor
```

## Basic Usage

```php
use Derafu\DataProcessor\ProcessorFactory;

$processor = ProcessorFactory::create();

// Simple validation.
$result = $processor->process('test@example.com', [
    'validate' => ['email'],
]);

// Multi-phase processing.
$result = $processor->process(' TEST@EXAMPLE.COM ', [
    'transform' => ['lowercase'],
    'sanitize' => ['trim'],
    'validate' => ['email', 'max_length:255'],
]);

// Array validation.
$result = $processor->process([1, 2, 2, 3], [
    'validate' => ['unique', 'max_items:5'],
]);

// File validation.
$result = $processor->process($_FILES['upload'], [
    'validate' => ['file:2M', 'mime_types:application/pdf,image/jpeg']
]);
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request. For major changes, please open an issue first to discuss what you would like to change.

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
