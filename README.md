[![GitHub Workflow Status][ico-tests]][link-tests]
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

------

This library provides a comprehensive assertion library for PHP 8.4+, enabling robust validation and preconditions for your code.

## Requirements

> **Requires [PHP 8.4+](https://php.net/releases/)**

## Installation

```bash
composer require cline/assert
```

## Documentation

### Getting Started
- **[Getting Started](https://docs.cline.sh/assert/getting-started/)** - Introduction to assertions and basic usage
- **[Expect API](https://docs.cline.sh/assert/expect-api/)** - Jest/Pest-style fluent expectations

### Assertion Types
- **[String Assertions](https://docs.cline.sh/assert/string-assertions/)** - String validation and checks
- **[Numeric Assertions](https://docs.cline.sh/assert/numeric-assertions/)** - Number validation and comparisons
- **[Array Assertions](https://docs.cline.sh/assert/array-assertions/)** - Array validation and operations
- **[Type Assertions](https://docs.cline.sh/assert/type-assertions/)** - Type checking and validation
- **[Comparison Assertions](https://docs.cline.sh/assert/comparison-assertions/)** - Value comparison utilities
- **[Object Assertions](https://docs.cline.sh/assert/object-assertions/)** - Object property and method checks
- **[Boolean Assertions](https://docs.cline.sh/assert/boolean-assertions/)** - Boolean value validation
- **[Null and Empty Assertions](https://docs.cline.sh/assert/null-empty-assertions/)** - Null and empty checks
- **[File System Assertions](https://docs.cline.sh/assert/filesystem-assertions/)** - File and directory validation
- **[Validation Assertions](https://docs.cline.sh/assert/validation-assertions/)** - Email, URL, and format validation

### Advanced Usage
- **[Custom Assertions](https://docs.cline.sh/assert/custom-assertions/)** - Creating custom assertion rules
- **[Lazy Assertions](https://docs.cline.sh/assert/lazy-assertions/)** - Batch validation and error collection
- **[Assertion Chains](https://docs.cline.sh/assert/assertion-chains/)** - Fluent assertion interface

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please use the [GitHub security reporting form][link-security] rather than the issue queue.

## Credits

- [Brian Faust][link-maintainer]
- [Benjamin Eberlei][link-author]
- [All Contributors][link-contributors]

## License

The MIT License. Please see [License File](LICENSE.md) for more information.

[ico-tests]: https://github.com/faustbrian/assert/actions/workflows/quality-assurance.yaml/badge.svg
[ico-version]: https://img.shields.io/packagist/v/cline/assert.svg
[ico-license]: https://img.shields.io/badge/License-MIT-green.svg
[ico-downloads]: https://img.shields.io/packagist/dt/cline/assert.svg

[link-tests]: https://github.com/faustbrian/assert/actions
[link-packagist]: https://packagist.org/packages/cline/assert
[link-downloads]: https://packagist.org/packages/cline/assert
[link-security]: https://github.com/faustbrian/assert/security
[link-maintainer]: https://github.com/faustbrian
[link-author]: https://github.com/beberlei
[link-contributors]: ../../contributors
