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

- **[Getting Started](cookbook/getting-started.md)** - Introduction to assertions and basic usage
- **[String Assertions](cookbook/string-assertions.md)** - String validation and checks
- **[Numeric Assertions](cookbook/numeric-assertions.md)** - Number validation and comparisons
- **[Array Assertions](cookbook/array-assertions.md)** - Array validation and operations
- **[Type Assertions](cookbook/type-assertions.md)** - Type checking and validation
- **[Comparison Assertions](cookbook/comparison-assertions.md)** - Value comparison utilities
- **[Object Assertions](cookbook/object-assertions.md)** - Object property and method checks
- **[Boolean Assertions](cookbook/boolean-assertions.md)** - Boolean value validation
- **[Null and Empty Assertions](cookbook/null-empty-assertions.md)** - Null and empty checks
- **[File System Assertions](cookbook/filesystem-assertions.md)** - File and directory validation
- **[Validation Assertions](cookbook/validation-assertions.md)** - Email, URL, and format validation
- **[Custom Assertions](cookbook/custom-assertions.md)** - Creating custom assertion rules
- **[Lazy Assertions](cookbook/lazy-assertions.md)** - Batch validation and error collection
- **[Assertion Chains](cookbook/assertion-chains.md)** - Fluent assertion interface

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
