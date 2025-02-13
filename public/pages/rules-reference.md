# Rules Reference

## Cast Rules

| Rule | Description | Example |
|------|-------------|---------|
| `boolean`, `bool` | Convert value to boolean | `'cast' => 'boolean'` |
| `date` | Convert value to Carbon date | `'cast' => 'date'` |
| `datetime` | Convert value to Carbon datetime | `'cast' => 'datetime'` |
| `float`, `decimal` | Convert value to float | `'cast' => 'float'` |
| `integer`, `int` | Convert value to integer | `'cast' => 'integer'` |
| `string`, `str` | Convert value to string | `'cast' => 'string'` |
| `timestamp` | Convert value to Unix timestamp | `'cast' => 'timestamp'` |

## Transform Rules

| Rule | Description | Example |
|------|-------------|---------|
| `base64_decode` | Decode base64 string | `'transform' => ['base64_decode']` |
| `base64_encode` | Encode value to base64 | `'transform' => ['base64_encode']` |
| `json_decode` | Decode JSON string | `'transform' => ['json_decode']`, `'transform' => ['json_decode:array']` |
| `json_encode` | Encode value to JSON | `'transform' => ['json_encode']`, `'transform' => ['json_encode:pretty']` |
| `json_to_array` | Convert JSON string to array | `'transform' => ['json_to_array']` |
| `json_to_object` | Convert JSON string to object | `'transform' => ['json_to_object']` |
| `round` | Round numeric value | `'transform' => ['round:2']` |
| `lowercase`, `lower` | Convert string to lowercase | `'transform' => ['lowercase']` |
| `slug` | Convert string to URL friendly format | `'transform' => ['slug']` |
| `transliterate` | Convert characters to ASCII | `'transform' => ['transliterate']` |
| `uppercase`, `upper` | Convert string to uppercase | `'transform' => ['uppercase']` |

## Sanitize Rules

| Rule | Description | Example |
|------|-------------|---------|
| `addslashes` | Add slashes before quotes | `'sanitize' => ['addslashes']` |
| `htmlentities` | Convert characters to HTML entities | `'sanitize' => ['htmlentities']` |
| `htmlspecialchars` | Convert special characters to HTML entities | `'sanitize' => ['htmlspecialchars']` |
| `regex_keep` | Keep only characters matching pattern | `'sanitize' => ['regex_keep:/[0-9]/']` |
| `regex_remove` | Remove characters matching pattern | `'sanitize' => ['regex_remove:/[^a-z]/']` |
| `remove_chars` | Remove specific characters | `'sanitize' => ['remove_chars:@#$']` |
| `remove_non_printable` | Remove non-printable characters | `'sanitize' => ['remove_non_printable']` |
| `remove_prefix` | Remove string prefix | `'sanitize' => ['remove_prefix:http']` |
| `remove_suffix` | Remove string suffix | `'sanitize' => ['remove_suffix:.txt']` |
| `spaces` | Normalize multiple spaces to single | `'sanitize' => ['spaces']` |
| `strip_tags` | Remove HTML and PHP tags | `'sanitize' => ['strip_tags']`, `'sanitize' => ['strip_tags:<p><br>']` |
| `substring` | Extract part of string | `'sanitize' => ['substring:5']`, `'sanitize' => ['substring:0,10']` |
| `trim` | Remove whitespace from ends | `'sanitize' => ['trim']` |

## Validate Rules

### Array Validation

| Rule | Description | Example |
|------|-------------|---------|
| `in`, `choices` | Check if value is in list | `'validate' => ['in:a,b,c']` |
| `max_items` | Check array maximum length | `'validate' => ['max_items:5']` |
| `min_items` | Check array minimum length | `'validate' => ['min_items:1']` |
| `notempty` | Check if array is not empty | `'validate' => ['notempty']` |
| `unique` | Check if array values are unique | `'validate' => ['unique']` |

### Date Validation

| Rule | Description | Example |
|------|-------------|---------|
| `after` | Check if date is after another | `'validate' => ['after:2024-01-01']` |
| `after_or_equal` | Check if date is after or equal | `'validate' => ['after_or_equal:2024-01-01']` |
| `before` | Check if date is before another | `'validate' => ['before:2024-12-31']` |
| `before_or_equal` | Check if date is before or equal | `'validate' => ['before_or_equal:2024-12-31']` |
| `between` | Check if date is between two dates | `'validate' => ['between:2024-01-01,2024-12-31']` |
| `date_equals` | Check if date equals another | `'validate' => ['date_equals:2024-01-01']` |
| `date_format` | Check if date matches format | `'validate' => ['date_format:Y-m-d']` |
| `weekday` | Check if date is weekday | `'validate' => ['weekday']` |
| `weekend` | Check if date is weekend | `'validate' => ['weekend']` |

### File Validation

| Rule | Description | Example |
|------|-------------|---------|
| `file` | Validate file with size limit | `'validate' => ['file:2M']` |
| `image` | Validate image file with size limit | `'validate' => ['image:2M']` |
| `mimetype` | Validate file mime type | `'validate' => ['mimetype:application/pdf,image/jpeg']` |

### Financial Validation

| Rule | Description | Example |
|------|-------------|---------|
| `bic` | Validate BIC/SWIFT code | `'validate' => ['bic']` |
| `card_number` | Validate credit card number | `'validate' => ['card_number']` |
| `iban` | Validate IBAN | `'validate' => ['iban']` |

### I18n Validation

| Rule | Description | Example |
|------|-------------|---------|
| `country` | Validate country code | `'validate' => ['country']` |
| `currency` | Validate currency code | `'validate' => ['currency']` |
| `language` | Validate language code | `'validate' => ['language']` |
| `locale` | Validate locale code | `'validate' => ['locale']` |
| `timezone` | Validate timezone identifier | `'validate' => ['timezone']` |

### ID Validation

| Rule | Description | Example |
|------|-------------|---------|
| `uuid` | Validate UUID string | `'validate' => ['uuid']` |

### Internet Validation

| Rule | Description | Example |
|------|-------------|---------|
| `email` | Validate email address | `'validate' => ['email']` |
| `hostname` | Validate hostname | `'validate' => ['hostname']` |
| `ip` | Validate IP address | `'validate' => ['ip']`, `'validate' => ['ip:v4']`, `'validate' => ['ip:v6']` |
| `url` | Validate URL | `'validate' => ['url']` |

### Numeric Validation

| Rule | Description | Example |
|------|-------------|---------|
| `decimal`, `float`, `real` | Validate decimal number | `'validate' => ['decimal:2']` |
| `digits_range` | Validate number of digits range | `'validate' => ['digits_range:3,5']` |
| `digits` | Validate exact number of digits | `'validate' => ['digits:4']` |
| `gt` | Greater than | `'validate' => ['gt:10']` |
| `gte`, `min` | Greater than or equal | `'validate' => ['gte:10']` |
| `integer`, `int` | Validate integer | `'validate' => ['integer']` |
| `lt` | Less than | `'validate' => ['lt:10']` |
| `lte`, `max` | Less than or equal | `'validate' => ['lte:10']` |
| `multiple_of` | Check if number is multiple | `'validate' => ['multiple_of:5']` |
| `numeric` | Validate numeric value | `'validate' => ['numeric']` |
| `range` | Validate number in range | `'validate' => ['range:1,100']` |

### String Validation

| Rule | Description | Example |
|------|-------------|---------|
| `alpha` | Only alphabetic characters | `'validate' => ['alpha']` |
| `alpha_dash` | Alphanumeric with dashes/underscores | `'validate' => ['alpha_dash']` |
| `alpha_num` | Only alphanumeric characters | `'validate' => ['alpha_num']` |
| `base64` | Validate base64 string | `'validate' => ['base64']` |
| `ends_with` | String ends with value | `'validate' => ['ends_with:Test']` |
| `json` | Validate JSON string | `'validate' => ['json']` |
| `length` | Exact string length | `'validate' => ['length:10']` |
| `max_length` | Maximum string length | `'validate' => ['max_length:255']` |
| `min_length` | Minimum string length | `'validate' => ['min_length:3']` |
| `not_regex` | Not match regular expression | `'validate' => ['not_regex:/[0-9]/']` |
| `no_whitespace` | No whitespace in string | `'validate' => ['no_whitespace']` |
| `regex` | Match regular expression | `'validate' => ['regex:/^[A-Z]+$/']` |
| `required` | Value is required | `'validate' => ['required']` |
| `slug` | Validate URL friendly string | `'validate' => ['slug']` |
| `starts_with` | String starts with value | `'validate' => ['starts_with:Test']` |
