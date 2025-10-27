<?php declare(strict_types=1);


























































































































use Illuminate\Support\Facades\Date;

use Carbon\CarbonImmutable;
use Cline\Assert\Matchers\AnyMatcher;
use Cline\Assert\Matchers\AnythingMatcher;
use Cline\Assert\Matchers\ArrayContainingMatcher;
use Cline\Assert\Matchers\MatcherFactory;
use Cline\Assert\Matchers\StringContainingMatcher;

describe('MatcherFactory', function (): void {
    describe('any()', function (): void {
        describe('Happy Paths', function (): void {
            test('creates AnyMatcher instance', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::any('string');

                // Assert
                expect($matcher)->toBeInstanceOf(AnyMatcher::class);
            });

            test('creates matcher that matches string type', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::any('string');

                // Assert
                expect($matcher->matches('hello'))->toBeTrue();
                expect($matcher->matches(''))->toBeTrue();
            });

            test('creates matcher that matches integer type', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::any('integer');

                // Assert
                expect($matcher->matches(42))->toBeTrue();
                expect($matcher->matches(0))->toBeTrue();
                expect($matcher->matches(-10))->toBeTrue();
            });

            test('creates matcher that matches int type alias', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::any('int');

                // Assert
                expect($matcher->matches(42))->toBeTrue();
                expect($matcher->matches(0))->toBeTrue();
            });

            test('creates matcher that matches float type', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::any('float');

                // Assert
                expect($matcher->matches(3.14))->toBeTrue();
                expect($matcher->matches(0.0))->toBeTrue();
                expect($matcher->matches(-2.5))->toBeTrue();
            });

            test('creates matcher that matches double type alias', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::any('double');

                // Assert
                expect($matcher->matches(3.14))->toBeTrue();
            });

            test('creates matcher that matches boolean type', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::any('boolean');

                // Assert
                expect($matcher->matches(true))->toBeTrue();
                expect($matcher->matches(false))->toBeTrue();
            });

            test('creates matcher that matches bool type alias', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::any('bool');

                // Assert
                expect($matcher->matches(true))->toBeTrue();
                expect($matcher->matches(false))->toBeTrue();
            });

            test('creates matcher that matches array type', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::any('array');

                // Assert
                expect($matcher->matches([]))->toBeTrue();
                expect($matcher->matches([1, 2, 3]))->toBeTrue();
                expect($matcher->matches(['key' => 'value']))->toBeTrue();
            });

            test('creates matcher that matches object type', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::any('object');

                // Assert
                expect($matcher->matches(
                    new stdClass(),
                ))->toBeTrue();
                expect($matcher->matches(
                    Date::now(),
                ))->toBeTrue();
            });

            test('creates matcher that matches specific class', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::any(DateTime::class);

                // Assert
                expect($matcher->matches(
                    Date::now(),
                ))->toBeTrue();
            });

            test('creates matcher that matches null type', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::any('null');

                // Assert
                expect($matcher->matches(null))->toBeTrue();
            });

            test('creates matcher that matches numeric type', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::any('numeric');

                // Assert
                expect($matcher->matches(42))->toBeTrue();
                expect($matcher->matches(3.14))->toBeTrue();
                expect($matcher->matches('123'))->toBeTrue();
            });

            test('creates matcher that matches scalar type', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::any('scalar');

                // Assert
                expect($matcher->matches(42))->toBeTrue();
                expect($matcher->matches(3.14))->toBeTrue();
                expect($matcher->matches('string'))->toBeTrue();
                expect($matcher->matches(true))->toBeTrue();
            });

            test('creates matcher that matches callable type', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::any('callable');

                // Assert
                expect($matcher->matches(fn () => true))->toBeTrue();
                expect($matcher->matches('strlen'))->toBeTrue();
            });

            test('creates matcher that matches resource type', function (): void {
                // Arrange
                $resource = fopen('php://memory', 'rb');

                // Act
                $matcher = MatcherFactory::any('resource');

                // Assert
                expect($matcher->matches($resource))->toBeTrue();

                fclose($resource);
            });

            test('provides readable toString output', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::any('string');

                // Assert
                expect($matcher->toString())->toBe('any(string)');
            });
        });

        describe('Sad Paths', function (): void {
            test('creates matcher that rejects wrong string type', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::any('string');

                // Assert
                expect($matcher->matches(42))->toBeFalse();
                expect($matcher->matches(null))->toBeFalse();
                expect($matcher->matches([]))->toBeFalse();
            });

            test('creates matcher that rejects wrong integer type', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::any('integer');

                // Assert
                expect($matcher->matches('42'))->toBeFalse();
                expect($matcher->matches(3.14))->toBeFalse();
                expect($matcher->matches(null))->toBeFalse();
            });

            test('creates matcher that rejects wrong array type', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::any('array');

                // Assert
                expect($matcher->matches('[]'))->toBeFalse();
                expect($matcher->matches(
                    new ArrayObject([1, 2, 3]),
                ))->toBeFalse();
            });

            test('creates matcher that rejects wrong class type', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::any(DateTime::class);

                // Assert
                expect($matcher->matches(
                    new stdClass(),
                ))->toBeFalse();
                expect($matcher->matches('DateTime'))->toBeFalse();
            });

            test('creates matcher that rejects non-null for null type', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::any('null');

                // Assert
                expect($matcher->matches(0))->toBeFalse();
                expect($matcher->matches(''))->toBeFalse();
                expect($matcher->matches(false))->toBeFalse();
            });

            test('creates matcher that rejects non-numeric for numeric type', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::any('numeric');

                // Assert
                expect($matcher->matches('abc'))->toBeFalse();
                expect($matcher->matches([]))->toBeFalse();
            });

            test('creates matcher that rejects non-scalar for scalar type', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::any('scalar');

                // Assert
                expect($matcher->matches([]))->toBeFalse();
                expect($matcher->matches(
                    new stdClass(),
                ))->toBeFalse();
                expect($matcher->matches(null))->toBeFalse();
            });

            test('creates matcher that rejects non-callable for callable type', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::any('callable');

                // Assert
                expect($matcher->matches('not_a_function'))->toBeFalse();
                expect($matcher->matches(
                    new stdClass(),
                ))->toBeFalse();
            });
        });

        describe('Edge Cases', function (): void {
            test('handles case-sensitive class names', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::any('DateTime');

                // Assert
                expect($matcher->matches(
                    Date::now(),
                ))->toBeTrue();
            });

            test('handles empty resource after close', function (): void {
                // Arrange
                $resource = fopen('php://memory', 'rb');
                fclose($resource);

                // Act
                $matcher = MatcherFactory::any('resource');

                // Assert
                expect($matcher->matches($resource))->toBeFalse();
            });

            test('handles interface types', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::any(DateTimeInterface::class);

                // Assert
                expect($matcher->matches(
                    Date::now(),
                ))->toBeTrue();
                expect($matcher->matches(
                    CarbonImmutable::now(),
                ))->toBeTrue();
            });
        });
    });

    describe('anything()', function (): void {
        describe('Happy Paths', function (): void {
            test('creates AnythingMatcher instance', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::anything();

                // Assert
                expect($matcher)->toBeInstanceOf(AnythingMatcher::class);
            });

            test('creates matcher that matches any non-null value', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::anything();

                // Assert
                expect($matcher->matches('string'))->toBeTrue();
                expect($matcher->matches(42))->toBeTrue();
                expect($matcher->matches(3.14))->toBeTrue();
                expect($matcher->matches(true))->toBeTrue();
                expect($matcher->matches(false))->toBeTrue();
                expect($matcher->matches([]))->toBeTrue();
                expect($matcher->matches(
                    new stdClass(),
                ))->toBeTrue();
            });

            test('provides readable toString output', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::anything();

                // Assert
                expect($matcher->toString())->toBe('anything()');
            });
        });

        describe('Sad Paths', function (): void {
            test('creates matcher that rejects null', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::anything();

                // Assert
                expect($matcher->matches(null))->toBeFalse();
            });
        });

        describe('Edge Cases', function (): void {
            test('matches empty values that are not null', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::anything();

                // Assert
                expect($matcher->matches(''))->toBeTrue();
                expect($matcher->matches(0))->toBeTrue();
                expect($matcher->matches(0.0))->toBeTrue();
                expect($matcher->matches([]))->toBeTrue();
            });
        });
    });

    describe('stringContaining()', function (): void {
        describe('Happy Paths', function (): void {
            test('creates StringContainingMatcher instance', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::stringContaining('test');

                // Assert
                expect($matcher)->toBeInstanceOf(StringContainingMatcher::class);
            });

            test('creates matcher that matches strings containing substring', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::stringContaining('world');

                // Assert
                expect($matcher->matches('hello world'))->toBeTrue();
                expect($matcher->matches('world'))->toBeTrue();
                expect($matcher->matches('world hello'))->toBeTrue();
            });

            test('creates matcher that is case-sensitive', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::stringContaining('World');

                // Assert
                expect($matcher->matches('hello World'))->toBeTrue();
                expect($matcher->matches('hello world'))->toBeFalse();
            });

            test('creates matcher that matches empty substring in any string', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::stringContaining('');

                // Assert
                expect($matcher->matches('hello'))->toBeTrue();
                expect($matcher->matches(''))->toBeTrue();
            });

            test('provides readable toString output', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::stringContaining('test');

                // Assert
                expect($matcher->toString())->toBe("stringContaining('test')");
            });
        });

        describe('Sad Paths', function (): void {
            test('creates matcher that rejects strings not containing substring', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::stringContaining('world');

                // Assert
                expect($matcher->matches('hello'))->toBeFalse();
                expect($matcher->matches(''))->toBeFalse();
            });

            test('creates matcher that rejects non-string values', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::stringContaining('test');

                // Assert
                expect($matcher->matches(42))->toBeFalse();
                expect($matcher->matches(null))->toBeFalse();
                expect($matcher->matches([]))->toBeFalse();
                expect($matcher->matches(
                    new stdClass(),
                ))->toBeFalse();
            });
        });

        describe('Edge Cases', function (): void {
            test('handles special characters in substring', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::stringContaining('$#@!');

                // Assert
                expect($matcher->matches('test $#@! string'))->toBeTrue();
                expect($matcher->matches('test string'))->toBeFalse();
            });

            test('handles unicode characters', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::stringContaining('世界');

                // Assert
                expect($matcher->matches('你好世界'))->toBeTrue();
                expect($matcher->matches('hello world'))->toBeFalse();
            });

            test('handles newlines and whitespace in substring', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::stringContaining("test\nstring");

                // Assert
                expect($matcher->matches("hello test\nstring world"))->toBeTrue();
                expect($matcher->matches('hello test string world'))->toBeFalse();
            });
        });
    });

    describe('arrayContaining()', function (): void {
        describe('Happy Paths', function (): void {
            test('creates ArrayContainingMatcher instance', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::arrayContaining(['key' => 'value']);

                // Assert
                expect($matcher)->toBeInstanceOf(ArrayContainingMatcher::class);
            });

            test('creates matcher that matches arrays containing exact subset', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::arrayContaining(['name' => 'John']);

                // Assert
                expect($matcher->matches(['name' => 'John', 'age' => 30]))->toBeTrue();
                expect($matcher->matches(['name' => 'John']))->toBeTrue();
            });

            test('creates matcher that matches arrays with multiple subset keys', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::arrayContaining([
                    'name' => 'John',
                    'email' => 'john@example.com',
                ]);

                // Assert
                expect($matcher->matches([
                    'name' => 'John',
                    'email' => 'john@example.com',
                    'age' => 30,
                ]))->toBeTrue();
            });

            test('creates matcher that matches empty subset in any array', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::arrayContaining([]);

                // Assert
                expect($matcher->matches(['key' => 'value']))->toBeTrue();
                expect($matcher->matches([]))->toBeTrue();
            });

            test('creates matcher that works with nested matchers', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::arrayContaining([
                    'name' => MatcherFactory::stringContaining('John'),
                    'age' => MatcherFactory::any('integer'),
                ]);

                // Assert
                expect($matcher->matches([
                    'name' => 'John Doe',
                    'age' => 30,
                    'email' => 'john@example.com',
                ]))->toBeTrue();
            });

            test('provides readable toString output', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::arrayContaining(['key' => 'value']);

                // Assert
                expect($matcher->toString())->toBe('arrayContaining({"key":"value"})');
            });

            test('creates matcher that matches numeric keys', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::arrayContaining([0 => 'first', 2 => 'third']);

                // Assert
                expect($matcher->matches([0 => 'first', 1 => 'second', 2 => 'third']))->toBeTrue();
            });
        });

        describe('Sad Paths', function (): void {
            test('creates matcher that rejects arrays missing subset keys', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::arrayContaining(['name' => 'John']);

                // Assert
                expect($matcher->matches(['email' => 'john@example.com']))->toBeFalse();
                expect($matcher->matches([]))->toBeFalse();
            });

            test('creates matcher that rejects arrays with different values', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::arrayContaining(['name' => 'John']);

                // Assert
                expect($matcher->matches(['name' => 'Jane']))->toBeFalse();
            });

            test('creates matcher that rejects non-array values', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::arrayContaining(['key' => 'value']);

                // Assert
                expect($matcher->matches('string'))->toBeFalse();
                expect($matcher->matches(42))->toBeFalse();
                expect($matcher->matches(null))->toBeFalse();
                expect($matcher->matches(
                    new stdClass(),
                ))->toBeFalse();
            });

            test('creates matcher that rejects when nested matcher fails', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::arrayContaining([
                    'name' => MatcherFactory::stringContaining('John'),
                ]);

                // Assert
                expect($matcher->matches(['name' => 'Jane Doe']))->toBeFalse();
            });

            test('creates matcher that uses strict comparison for values', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::arrayContaining(['count' => 1]);

                // Assert
                expect($matcher->matches(['count' => 1]))->toBeTrue();
                expect($matcher->matches(['count' => '1']))->toBeFalse();
                expect($matcher->matches(['count' => true]))->toBeFalse();
            });
        });

        describe('Edge Cases', function (): void {
            test('handles nested arrays in subset', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::arrayContaining([
                    'user' => ['name' => 'John'],
                ]);

                // Assert
                expect($matcher->matches([
                    'user' => ['name' => 'John'],
                    'active' => true,
                ]))->toBeTrue();
                expect($matcher->matches([
                    'user' => ['name' => 'Jane'],
                ]))->toBeFalse();
            });

            test('handles null values in subset', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::arrayContaining(['value' => null]);

                // Assert
                expect($matcher->matches(['value' => null, 'other' => 'data']))->toBeTrue();
                expect($matcher->matches(['value' => 'not null']))->toBeFalse();
            });

            test('handles special characters in keys', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::arrayContaining(['key-with-dash' => 'value']);

                // Assert
                expect($matcher->matches(['key-with-dash' => 'value', 'other' => 'data']))->toBeTrue();
            });

            test('handles complex nested matcher scenarios', function (): void {
                // Arrange & Act
                $matcher = MatcherFactory::arrayContaining([
                    'items' => MatcherFactory::arrayContaining([
                        'id' => MatcherFactory::any('integer'),
                        'name' => MatcherFactory::stringContaining('Product'),
                    ]),
                ]);

                // Assert
                expect($matcher->matches([
                    'items' => [
                        'id' => 123,
                        'name' => 'Product A',
                        'price' => 99.99,
                    ],
                    'total' => 1,
                ]))->toBeTrue();
            });
        });
    });
});
