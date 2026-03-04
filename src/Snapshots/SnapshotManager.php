<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Snapshots;

use RuntimeException;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function is_dir;
use function is_object;
use function is_string;
use function json_encode;
use function mkdir;
use function sprintf;
use function throw_unless;

/**
 * Manages snapshot files for Jest/Vitest-style snapshot testing.
 *
 * Handles snapshot file storage, retrieval, and formatting for the toMatchSnapshot()
 * and toMatchInlineSnapshot() expectation methods. Snapshots are stored as files
 * in the configured snapshot directory, with automatic directory creation and
 * formatting for human-readable diffs.
 *
 * On first run, snapshots are automatically created. Subsequent runs compare against
 * the stored snapshots, failing the test if values don't match. This enables
 * regression testing for complex data structures without manual assertion writing.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class SnapshotManager
{
    /**
     * Directory where snapshot files are stored.
     */
    private static string $snapshotDir = '__snapshots__';

    /**
     * Configure the directory where snapshot files are stored.
     *
     * @param string $dir Directory path relative to the test file or absolute path
     */
    public static function setSnapshotDirectory(string $dir): void
    {
        self::$snapshotDir = $dir;
    }

    /**
     * Get the file path for a named snapshot.
     *
     * Constructs the full path by combining the snapshot directory with the
     * test name and .snap extension.
     *
     * @param string $testName Unique identifier for the snapshot
     *
     * @return string Full file path to the snapshot file
     */
    public static function getSnapshotPath(string $testName): string
    {
        return self::$snapshotDir.'/'.$testName.'.snap';
    }

    /**
     * Check if a snapshot exists for the given test name.
     *
     * @param string $testName Unique identifier for the snapshot
     *
     * @return bool True if snapshot file exists, false otherwise
     */
    public static function hasSnapshot(string $testName): bool
    {
        return file_exists(self::getSnapshotPath($testName));
    }

    /**
     * Retrieve the contents of a stored snapshot.
     *
     * @param string $testName Unique identifier for the snapshot
     *
     * @throws RuntimeException When snapshot file doesn't exist or cannot be read
     * @return string           The stored snapshot content
     */
    public static function getSnapshot(string $testName): string
    {
        $path = self::getSnapshotPath($testName);
        throw_unless(file_exists($path), RuntimeException::class, sprintf('Snapshot %s does not exist', $testName));

        $content = file_get_contents($path);
        throw_unless($content !== false, RuntimeException::class, sprintf('Failed to read snapshot %s', $testName));

        return $content;
    }

    /**
     * Save a snapshot to disk.
     *
     * Creates the snapshot directory if it doesn't exist, then writes the
     * formatted content to a .snap file. Used for initial snapshot creation
     * and snapshot updates.
     *
     * @param string $testName Unique identifier for the snapshot
     * @param string $content  Formatted snapshot content to save
     */
    public static function saveSnapshot(string $testName, string $content): void
    {
        if (!is_dir(self::$snapshotDir)) {
            mkdir(self::$snapshotDir, 0o755, true);
        }

        file_put_contents(self::getSnapshotPath($testName), $content);
    }

    /**
     * Format a value for snapshot storage.
     *
     * Converts values to human-readable string representations suitable for
     * snapshot storage and diff comparison. Arrays and objects are JSON-encoded
     * with pretty-printing for readability. Strings are stored as-is.
     *
     * @param mixed $value The value to format for snapshot storage
     *
     * @throws RuntimeException When JSON encoding fails for arrays/objects
     * @return string           Formatted string representation of the value
     */
    public static function formatValue(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_array($value) || is_object($value)) {
            $encoded = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            throw_unless($encoded !== false, RuntimeException::class, 'Failed to encode value as JSON');

            return $encoded;
        }

        return (string) $value;
    }
}
