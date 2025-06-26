<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor;

use Carbon\Carbon;
use Derafu\DataProcessor\Contract\ProcessorInterface;
use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\DataProcessor\Processor;
use Derafu\DataProcessor\ProcessorFactory;
use Derafu\DataProcessor\Registrar\DefaultRuleRegistrar;
use Derafu\DataProcessor\Rule\Caster\BooleanRule;
use Derafu\DataProcessor\Rule\Caster\DateRule;
use Derafu\DataProcessor\Rule\Caster\DateTimeRule;
use Derafu\DataProcessor\Rule\Caster\FloatRule;
use Derafu\DataProcessor\Rule\Caster\IntegerRule;
use Derafu\DataProcessor\Rule\Caster\StringRule;
use Derafu\DataProcessor\Rule\Caster\TimestampRule;
use Derafu\DataProcessor\Rule\Sanitizer\HtmlSpecialCharsRule;
use Derafu\DataProcessor\Rule\Sanitizer\RegexKeepRule;
use Derafu\DataProcessor\Rule\Sanitizer\RegexRemoveRule;
use Derafu\DataProcessor\Rule\Sanitizer\RemoveCharsRule;
use Derafu\DataProcessor\Rule\Sanitizer\RemoveNonPrintableRule;
use Derafu\DataProcessor\Rule\Sanitizer\RemovePrefixRule;
use Derafu\DataProcessor\Rule\Sanitizer\SpacesRule;
use Derafu\DataProcessor\Rule\Sanitizer\StripTagsRule;
use Derafu\DataProcessor\Rule\Sanitizer\SubStringRule;
use Derafu\DataProcessor\Rule\Sanitizer\TrimRule;
use Derafu\DataProcessor\Rule\Transformer\Json\JsonToArrayRule;
use Derafu\DataProcessor\Rule\Transformer\String\LowercaseRule;
use Derafu\DataProcessor\Rule\Validator\Array\InRule;
use Derafu\DataProcessor\Rule\Validator\Numeric\GreaterThanOrEqualRule;
use Derafu\DataProcessor\Rule\Validator\Numeric\LessThanOrEqualRule;
use Derafu\DataProcessor\Rule\Validator\String\MaxLengthRule;
use Derafu\DataProcessor\Rule\Validator\String\MinLengthRule;
use Derafu\DataProcessor\Rule\Validator\String\RequiredRule;
use Derafu\DataProcessor\RuleParser;
use Derafu\DataProcessor\RuleRegistry;
use Derafu\DataProcessor\RuleResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Processor::class)]
#[CoversClass(DefaultRuleRegistrar::class)]
#[CoversClass(ProcessorFactory::class)]
#[CoversClass(RuleRegistry::class)]
#[CoversClass(RuleResolver::class)]
#[CoversClass(RuleParser::class)]
#[CoversClass(InRule::class)]
#[CoversClass(IntegerRule::class)]
#[CoversClass(RemoveCharsRule::class)]
#[CoversClass(TrimRule::class)]
#[CoversClass(RequiredRule::class)]
#[CoversClass(GreaterThanOrEqualRule::class)]
#[CoversClass(LessThanOrEqualRule::class)]
#[CoversClass(RemoveNonPrintableRule::class)]
#[CoversClass(StripTagsRule::class)]
#[CoversClass(SpacesRule::class)]
#[CoversClass(HtmlSpecialCharsRule::class)]
#[CoversClass(LowercaseRule::class)]
#[CoversClass(SubStringRule::class)]
#[CoversClass(RemovePrefixRule::class)]
#[CoversClass(RegexRemoveRule::class)]
#[CoversClass(RegexKeepRule::class)]
#[CoversClass(StringRule::class)]
#[CoversClass(FloatRule::class)]
#[CoversClass(BooleanRule::class)]
#[CoversClass(JsonToArrayRule::class)]
#[CoversClass(DateRule::class)]
#[CoversClass(DateTimeRule::class)]
#[CoversClass(TimestampRule::class)]
#[CoversClass(MinLengthRule::class)]
#[CoversClass(MaxLengthRule::class)]
final class ProcessorTest extends TestCase
{
    private ProcessorInterface $processor;

    protected function setUp(): void
    {
        $this->processor = ProcessorFactory::create();
    }

    #[DataProvider('castingProvider')]
    public function testCasting(mixed $input, string $rule, mixed $expected): void
    {
        $result = $this->processor->process($input, [
            'cast' => $rule,
        ]);

        if ($expected instanceof Carbon) {
            $this->assertInstanceOf(Carbon::class, $result);
            $this->assertTrue($expected->eq($result));
        } else {
            $this->assertSame($expected, $result);
        }
    }

    public static function castingProvider(): array
    {
        return [
            'to_string' => [
                123,
                'string',
                '123',
            ],
            'to_int' => [
                '123.45',
                'integer',
                123,
            ],
            'to_float' => [
                '123.456',
                'float',
                123.456,
            ],
            'to_float_with_precision' => [
                '123.456',
                'float:2',
                123.46,
            ],
            'to_bool_from_string' => [
                'true',
                'boolean',
                true,
            ],
            'to_date' => [
                '2024-02-15',
                'date',
                Carbon::parse('2024-02-15')->startOfDay(),
            ],
            'to_datetime' => [
                '2024-02-15 14:30:00',
                'datetime',
                Carbon::parse('2024-02-15 14:30:00'),
            ],
            'to_timestamp' => [
                '2024-02-15 14:30:00',
                'timestamp',
                Carbon::parse('2024-02-15 14:30:00')->timestamp,
            ],
        ];
    }

    #[DataProvider('sanitizationProvider')]
    public function testSanitization(string $input, array $rules, string $expected): void
    {
        $result = $this->processor->process($input, [
            'sanitize' => $rules,
        ]);
        $this->assertSame($expected, $result);
    }

    public static function sanitizationProvider(): array
    {
        return [
            'remove_non_printable' => [
                "Hello\n\x00World",
                ['remove_non_printable'],
                "Hello\nWorld",
            ],
            'strip_tags_basic' => [
                '<p>Hello <script>alert("xss")</script></p>',
                ['strip_tags'],
                'Hello alert("xss")',
            ],
            'strip_tags_with_allowed' => [
                '<p>Hello <script>alert("xss")</script></p>',
                ['strip_tags:<p>'],
                '<p>Hello alert("xss")</p>',
            ],
            'remove_chars' => [
                'Hello@#$World',
                ['remove_chars:@#$'],
                'HelloWorld',
            ],
            'normalize_spaces' => [
                'Hello    World',
                ['spaces'],
                'Hello World',
            ],
            'trim_basic' => [
                '  Hello World  ',
                ['trim'],
                'Hello World',
            ],
            'trim_specific_chars' => [
                '...Hello World...',
                ['trim:.'],
                'Hello World',
            ],
            'html_encoding' => [
                '<hello>',
                ['htmlspecialchars'],
                '&lt;hello&gt;',
            ],
            'substring' => [
                'Hello World',
                ['substring:5'],
                'Hello',
            ],
            'remove_prefix' => [
                'prefixHello',
                ['remove_prefix:prefix'],
                'Hello',
            ],
            'regex_remove' => [
                'Hello123World',
                ['regex_remove:/[0-9]/'],
                'HelloWorld',
            ],
            'regex_keep' => [
                'Hello123World',
                ['regex_keep:/[0-9]+/'],
                '123',
            ],
        ];
    }

    #[DataProvider('validationProvider')]
    public function testValidation(mixed $input, array $rules, bool $shouldPass): void
    {
        if (!$shouldPass) {
            $this->expectException(ValidationException::class);
        }

        $result = $this->processor->process($input, [
            'validate' => $rules,
        ]);

        if ($shouldPass) {
            $this->assertSame($input, $result);
        }
    }

    public static function validationProvider(): array
    {
        return [
            'required_valid' => [
                'value',
                ['required'],
                true,
            ],
            'required_invalid' => [
                '',
                ['required'],
                false,
            ],
            'min_length_valid' => [
                'hello',
                ['min_length:3'],
                true,
            ],
            'min_length_invalid' => [
                'hi',
                ['min_length:3'],
                false,
            ],
            'max_length_valid' => [
                'hello',
                ['max_length:10'],
                true,
            ],
            'max_length_invalid' => [
                'hello world too long',
                ['max_length:10'],
                false,
            ],
            'min_value_valid' => [
                5,
                ['gte:0'],
                true,
            ],
            'min_value_invalid' => [
                -1,
                ['gte:0'],
                false,
            ],
            'max_value_valid' => [
                50,
                ['lte:100'],
                true,
            ],
            'max_value_invalid' => [
                150,
                ['lte:100'],
                false,
            ],
            'choices_valid' => [
                'approved',
                ['in:pending,approved,rejected'],
                true,
            ],
            'choices_invalid' => [
                'invalid_status',
                ['in:pending,approved,rejected'],
                false,
            ],
        ];
    }

    public function testCompleteProcessing(): void
    {

        $result = $this->processor->process(' 25@ ', [
            'sanitize' => ['trim', 'remove_chars:@#$'],
        ]);
        $result = $this->processor->process($result, [
            'cast' => 'integer',
            'validate' => ['required', 'gte:18', 'lte:99'],
        ]);

        $this->assertSame(25, $result);

        $this->expectException(ValidationException::class);
        $result = $this->processor->process(' 15@ ', [
            'sanitize' => ['trim', 'remove_chars:@#$'],
        ]);
        $result = $this->processor->process($result, [
            'cast' => 'integer',
            'validate' => ['required', 'gte:18', 'lte:99'],
        ]);
    }

    public function testCompleteProcessingWithStringRules(): void
    {
        // Test caso exitoso.
        $result = $this->processor->process(
            ' Hello@#$ WORLD! ',
            't(lowercase) c(string) s(trim|remove_chars:@#$|spaces) v(required|min_length:8|max_length:20)'
        );
        $this->assertSame('hello world!', $result);

        // Test caso que debe fallar.
        $this->expectException(ValidationException::class);
        $result = $this->processor->process(
            ' Hi@#$! ',
            't(lowercase) c(string) s(trim|remove_chars:@#$|spaces) v(required|min_length:8|max_length:20)'
        );
    }

    public function testCompleteProcessingWithArrayStringRules(): void
    {
        // Test caso exitoso.
        $result = $this->processor->process(' Hello@#$ WORLD! ', [
            'transform' => 'lowercase',
            'cast' => 'string',
            'sanitize' => 'trim|remove_chars:@#$|spaces',
            'validate' => 'required|min_length:8|max_length:20',
        ]);
        $this->assertSame('hello world!', $result);

        // Test caso que debe fallar.
        $this->expectException(ValidationException::class);
        $result = $this->processor->process(' Hi@#$! ', [
            'transform' => 'lowercase',
            'cast' => 'string',
            'sanitize' => 'trim|remove_chars:@#$|spaces',
            'validate' => 'required|min_length:8|max_length:20',
        ]);
    }
}
