<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Snapshots;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function mkdir;

/**
 * Manages snapshot files for snapshot testing.
 */
final class SnapshotManager
{
    private static string $snapshotDir = '__snapshots__';

    public static function setSnapshotDirectory(string $dir): void
    {
        self::$snapshotDir = $dir;
    }

    public static function getSnapshotPath(string $testName): string
    {
        return self::$snapshotDir . '/' . $testName . '.snap';
    }

    public static function hasSnapshot(string $testName): bool
    {
        return file_exists(self::getSnapshotPath($testName));
    }

    public static function getSnapshot(string $testName): string
    {
        $path = self::getSnapshotPath($testName);
        if (!file_exists($path)) {
            throw new \RuntimeException("Snapshot {$testName} does not exist");
        }

        return file_get_contents($path);
    }

    public static function saveSnapshot(string $testName, string $content): void
    {
        if (!is_dir(self::$snapshotDir)) {
            mkdir(self::$snapshotDir, 0755, true);
        }

        file_put_contents(self::getSnapshotPath($testName), $content);
    }

    public static function formatValue(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        return (string) $value;
    }
}
