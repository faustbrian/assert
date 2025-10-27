<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Assert\Exceptions\InvalidArgumentException;
use Cline\Assert\Expectations\Expectation;
use Cline\Assert\Snapshots\SnapshotManager;

use function Cline\Assert\expect as assertExpect;

describe('Snapshot Testing', function (): void {
    beforeEach(function (): void {
        SnapshotManager::setSnapshotDirectory(__DIR__ . '/__test_snapshots__');
    });

    afterEach(function (): void {
        // Clean up test snapshots
        $dir = __DIR__ . '/__test_snapshots__';
        if (is_dir($dir)) {
            $files = glob($dir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($dir);
        }
    });

    test('creates snapshot on first run', function (): void {
        $data = ['name' => 'John', 'age' => 30];

        expect(fn (): Expectation => assertExpect($data)->toMatchSnapshot('user-data'))
            ->not->toThrow(Throwable::class);

        expect(SnapshotManager::hasSnapshot('user-data'))->toBeTrue();
    });

    test('matches existing snapshot', function (): void {
        $data = ['name' => 'John', 'age' => 30];

        // Create snapshot
        assertExpect($data)->toMatchSnapshot('user-data');

        // Should match on second run
        expect(fn (): Expectation => assertExpect($data)->toMatchSnapshot('user-data'))
            ->not->toThrow(Throwable::class);
    });

    test('fails when snapshot does not match', function (): void {
        $original = ['name' => 'John', 'age' => 30];
        $modified = ['name' => 'Jane', 'age' => 25];

        // Create snapshot
        assertExpect($original)->toMatchSnapshot('user-data');

        // Should fail with different data
        expect(fn (): Expectation => assertExpect($modified)->toMatchSnapshot('user-data'))
            ->toThrow(InvalidArgumentException::class);
    });

    test('formats strings correctly', function (): void {
        assertExpect('hello world')->toMatchSnapshot('string-test');

        $snapshot = SnapshotManager::getSnapshot('string-test');
        expect($snapshot)->toBe('hello world');
    });

    test('formats arrays as JSON', function (): void {
        $data = ['name' => 'John', 'numbers' => [1, 2, 3]];

        assertExpect($data)->toMatchSnapshot('array-test');

        $snapshot = SnapshotManager::getSnapshot('array-test');
        expect($snapshot)->toContain('"name": "John"');
        expect($snapshot)->toContain('"numbers": [');
    });

    test('formats objects as JSON', function (): void {
        $obj = (object) ['name' => 'John', 'age' => 30];

        assertExpect($obj)->toMatchSnapshot('object-test');

        $snapshot = SnapshotManager::getSnapshot('object-test');
        expect($snapshot)->toContain('"name": "John"');
    });

    test('handles numeric values', function (): void {
        assertExpect(123)->toMatchSnapshot('number-test');

        $snapshot = SnapshotManager::getSnapshot('number-test');
        expect($snapshot)->toBe('123');
    });

    test('toMatchInlineSnapshot matches expected string', function (): void {
        $data = ['name' => 'John'];
        $expected = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        expect(fn (): Expectation => assertExpect($data)->toMatchInlineSnapshot($expected))
            ->not->toThrow(Throwable::class);
    });

    test('toMatchInlineSnapshot fails with different data', function (): void {
        $data = ['name' => 'John'];
        $expected = json_encode(['name' => 'Jane'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        expect(fn (): Expectation => assertExpect($data)->toMatchInlineSnapshot($expected))
            ->toThrow(InvalidArgumentException::class);
    });

    test('error message shows diff for toMatchSnapshot', function (): void {
        $original = ['name' => 'John'];
        $modified = ['name' => 'Jane'];

        assertExpect($original)->toMatchSnapshot('diff-test');

        try {
            assertExpect($modified)->toMatchSnapshot('diff-test');
            throw new Exception('Should have thrown');
        } catch (InvalidArgumentException $e) {
            expect($e->getMessage())->toContain('Snapshot "diff-test" does not match');
            expect($e->getMessage())->toContain('Expected:');
            expect($e->getMessage())->toContain('Received:');
        }
    });

    test('error message shows diff for toMatchInlineSnapshot', function (): void {
        $data = ['name' => 'John'];
        $expected = json_encode(['name' => 'Jane'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        try {
            assertExpect($data)->toMatchInlineSnapshot($expected);
            throw new Exception('Should have thrown');
        } catch (InvalidArgumentException $e) {
            expect($e->getMessage())->toContain('Inline snapshot does not match');
            expect($e->getMessage())->toContain('Expected:');
            expect($e->getMessage())->toContain('Received:');
        }
    });

    test('handles nested data structures', function (): void {
        $data = [
            'user' => [
                'name' => 'John',
                'profile' => [
                    'age' => 30,
                    'tags' => ['admin', 'verified'],
                ],
            ],
        ];

        assertExpect($data)->toMatchSnapshot('nested-test');

        // Should match on second run
        expect(fn (): Expectation => assertExpect($data)->toMatchSnapshot('nested-test'))
            ->not->toThrow(Throwable::class);
    });

    test('multiple snapshots are independent', function (): void {
        assertExpect(['a' => 1])->toMatchSnapshot('snapshot-1');
        assertExpect(['b' => 2])->toMatchSnapshot('snapshot-2');

        expect(SnapshotManager::hasSnapshot('snapshot-1'))->toBeTrue();
        expect(SnapshotManager::hasSnapshot('snapshot-2'))->toBeTrue();

        $snap1 = SnapshotManager::getSnapshot('snapshot-1');
        $snap2 = SnapshotManager::getSnapshot('snapshot-2');

        expect($snap1)->not->toBe($snap2);
    });
});
