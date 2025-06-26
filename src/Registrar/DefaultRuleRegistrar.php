<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Registrar;

use Derafu\DataProcessor\Contract\RuleRegistrarInterface;
use Derafu\DataProcessor\Contract\RuleRegistryInterface;
use Derafu\DataProcessor\Rule\Caster;
use Derafu\DataProcessor\Rule\Sanitizer;
use Derafu\DataProcessor\Rule\Transformer;
use Derafu\DataProcessor\Rule\Validator;

/**
 * Registers all rules into the registry.
 */
final class DefaultRuleRegistrar implements RuleRegistrarInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(RuleRegistryInterface $registry): void
    {
        $this->registerCasterRules($registry);
        $this->registerTransformerRules($registry);
        $this->registerSanitizerRules($registry);
        $this->registerValidatorRules($registry);
    }

    /**
     * Registers caster rules.
     *
     * @param RuleRegistryInterface $registry
     * @return void
     */
    private function registerCasterRules(RuleRegistryInterface $registry): void
    {
        $registry
            ->addCasterRule('boolean', Caster\BooleanRule::class)
            ->addCasterRule('bool', Caster\BooleanRule::class)
            ->addCasterRule('date', Caster\DateRule::class)
            ->addCasterRule('datetime', Caster\DateTimeRule::class)
            ->addCasterRule('float', Caster\FloatRule::class)
            ->addCasterRule('decimal', Caster\FloatRule::class)
            ->addCasterRule('integer', Caster\IntegerRule::class)
            ->addCasterRule('int', Caster\IntegerRule::class)
            ->addCasterRule('string', Caster\StringRule::class)
            ->addCasterRule('str', Caster\StringRule::class)
            ->addCasterRule('timestamp', Caster\TimestampRule::class)
        ;
    }

    /**
     * Registers transformer rules.
     *
     * @param RuleRegistryInterface $registry
     * @return void
     */
    private function registerTransformerRules(RuleRegistryInterface $registry): void
    {
        $registry

            // Base 64 transformation.
            ->addTransformerRule('base64_decode', Transformer\Base64\Base64DecodeRule::class)
            ->addTransformerRule('base64_encode', Transformer\Base64\Base64EncodeRule::class)

            // JSON transformation.
            ->addTransformerRule('json_decode', Transformer\Json\JsonDecodeRule::class)
            ->addTransformerRule('json_encode', Transformer\Json\JsonEncodeRule::class)
            ->addTransformerRule('json_to_array', Transformer\Json\JsonToArrayRule::class)
            ->addTransformerRule('json_to_object', Transformer\Json\JsonToObjectRule::class)

            // Numeric transformation.
            ->addTransformerRule('round', Transformer\Numeric\RoundRule::class)

            // String transformation.
            ->addTransformerRule('lowercase', Transformer\String\LowercaseRule::class)
            ->addTransformerRule('lower', Transformer\String\LowercaseRule::class)
            ->addTransformerRule('slug', Transformer\String\SlugRule::class)
            ->addTransformerRule('transliterate', Transformer\String\TransliterateRule::class)
            ->addTransformerRule('uppercase', Transformer\String\UppercaseRule::class)
            ->addTransformerRule('upper', Transformer\String\UppercaseRule::class)
        ;
    }

    /**
     * Registers sanitizer rules.
     *
     * @param RuleRegistryInterface $registry
     * @return void
     */
    private function registerSanitizerRules(RuleRegistryInterface $registry): void
    {
        // String sanitization.
        $registry
            ->addSanitizerRule('addslashes', Sanitizer\AddSlashesRule::class)
            ->addSanitizerRule('htmlentities', Sanitizer\HtmlEntitiesRule::class)
            ->addSanitizerRule('htmlspecialchars', Sanitizer\HtmlSpecialCharsRule::class)
            ->addSanitizerRule('regex_keep', Sanitizer\RegexKeepRule::class)
            ->addSanitizerRule('regex_remove', Sanitizer\RegexRemoveRule::class)
            ->addSanitizerRule('remove_chars', Sanitizer\RemoveCharsRule::class)
            ->addSanitizerRule('remove_non_printable', Sanitizer\RemoveNonPrintableRule::class)
            ->addSanitizerRule('remove_prefix', Sanitizer\RemovePrefixRule::class)
            ->addSanitizerRule('remove_suffix', Sanitizer\RemoveSuffixRule::class)
            ->addSanitizerRule('spaces', Sanitizer\SpacesRule::class)
            ->addSanitizerRule('strip_tags', Sanitizer\StripTagsRule::class)
            ->addSanitizerRule('substring', Sanitizer\SubStringRule::class)
            ->addSanitizerRule('trim', Sanitizer\TrimRule::class)
        ;
    }

    /**
     * Registers validator rules.
     *
     * @param RuleRegistryInterface $registry
     * @return void
     */
    private function registerValidatorRules(RuleRegistryInterface $registry): void
    {
        $registry
            // Array validation.
            ->addValidatorRule('in', Validator\Array\InRule::class)
            ->addValidatorRule('choices', Validator\Array\InRule::class)
            ->addValidatorRule('max_items', Validator\Array\MaxItemsRule::class)
            ->addValidatorRule('min_items', Validator\Array\MinItemsRule::class)
            ->addValidatorRule('notempty', Validator\Array\NotEmptyRule::class)
            ->addValidatorRule('unique', Validator\Array\UniqueRule::class)

            // Date validation.
            ->addValidatorRule('after_or_equal', Validator\Date\AfterOrEqualRule::class)
            ->addValidatorRule('after', Validator\Date\AfterRule::class)
            ->addValidatorRule('before_or_equal', Validator\Date\BeforeOrEqualRule::class)
            ->addValidatorRule('before', Validator\Date\BeforeRule::class)
            ->addValidatorRule('between', Validator\Date\BetweenRule::class)
            ->addValidatorRule('date_equals', Validator\Date\DateEqualsRule::class)
            ->addValidatorRule('date_format', Validator\Date\DateFormatRule::class)
            ->addValidatorRule('weekday', Validator\Date\WeekdayRule::class)
            ->addValidatorRule('weekend', Validator\Date\WeekendRule::class)

            // File validation.
            ->addValidatorRule('file', Validator\File\FileRule::class)
            ->addValidatorRule('image', Validator\File\ImageRule::class)
            ->addValidatorRule('mimetype', Validator\File\MimeTypeRule::class)

            // Financial validation.
            ->addValidatorRule('bic', Validator\Financial\BicRule::class)
            ->addValidatorRule('card_number', Validator\Financial\CardNumberRule::class)
            ->addValidatorRule('iban', Validator\Financial\IbanRule::class)

            // I18n validation.
            ->addValidatorRule('country', Validator\I18n\CountryRule::class)
            ->addValidatorRule('currency', Validator\I18n\CurrencyRule::class)
            ->addValidatorRule('language', Validator\I18n\LanguageRule::class)
            ->addValidatorRule('locale', Validator\I18n\LocaleRule::class)
            ->addValidatorRule('timezone', Validator\I18n\TimezoneRule::class)

            // ID validation.
            ->addValidatorRule('uuid', Validator\Id\UuidRule::class)

            // Internet validation.
            ->addValidatorRule('email', Validator\Internet\EmailRule::class)
            ->addValidatorRule('hostname', Validator\Internet\HostnameRule::class)
            ->addValidatorRule('ip', Validator\Internet\IpRule::class)
            ->addValidatorRule('url', Validator\Internet\UrlRule::class)

            // Numeric validation.
            ->addValidatorRule('decimal', Validator\Numeric\DecimalRule::class)
            ->addValidatorRule('float', Validator\Numeric\DecimalRule::class)
            ->addValidatorRule('real', Validator\Numeric\DecimalRule::class)
            ->addValidatorRule('digits_range', Validator\Numeric\DigitsRangeRule::class)
            ->addValidatorRule('digits', Validator\Numeric\DigitsRule::class)
            ->addValidatorRule('gte', Validator\Numeric\GreaterThanOrEqualRule::class)
            ->addValidatorRule('min', Validator\Numeric\GreaterThanOrEqualRule::class)
            ->addValidatorRule('gt', Validator\Numeric\GreaterThanRule::class)
            ->addValidatorRule('integer', Validator\Numeric\IntegerRule::class)
            ->addValidatorRule('int', Validator\Numeric\IntegerRule::class)
            ->addValidatorRule('lte', Validator\Numeric\LessThanOrEqualRule::class)
            ->addValidatorRule('max', Validator\Numeric\LessThanOrEqualRule::class)
            ->addValidatorRule('lt', Validator\Numeric\LessThanRule::class)
            ->addValidatorRule('multiple_of', Validator\Numeric\MultipleOfRule::class)
            ->addValidatorRule('numeric', Validator\Numeric\NumericRule::class)
            ->addValidatorRule('range', Validator\Numeric\RangeRule::class)

            // String validation.
            ->addValidatorRule('alpha_dash', Validator\String\AlphaDashRule::class)
            ->addValidatorRule('alpha_num', Validator\String\AlphaNumRule::class)
            ->addValidatorRule('alpha', Validator\String\AlphaRule::class)
            ->addValidatorRule('base64', Validator\String\Base64Rule::class)
            ->addValidatorRule('ends_with', Validator\String\EndsWithRule::class)
            ->addValidatorRule('json', Validator\String\JsonRule::class)
            ->addValidatorRule('length', Validator\String\LengthRule::class)
            ->addValidatorRule('max_length', Validator\String\MaxLengthRule::class)
            ->addValidatorRule('min_length', Validator\String\MinLengthRule::class)
            ->addValidatorRule('not_regex', Validator\String\NotRegexRule::class)
            ->addValidatorRule('no_whitespace', Validator\String\NoWhitespaceRule::class)
            ->addValidatorRule('regex', Validator\String\RegexRule::class)
            ->addValidatorRule('required', Validator\String\RequiredRule::class)
            ->addValidatorRule('slug', Validator\String\SlugRule::class)
            ->addValidatorRule('starts_with', Validator\String\StartsWithRule::class)
        ;
    }
}
