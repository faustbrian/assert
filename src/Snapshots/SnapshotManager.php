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
 * Manages snapshot files for snapshot testing.
 *
 * @author Brian Faust <brian@cline.sh>
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
        return self::$snapshotDir.'/'.$testName.'.snap';
    }

    public static function hasSnapshot(string $testName): bool
    {
        return file_exists(self::getSnapshotPath($testName));
    }

    public static function getSnapshot(string $testName): string
    {
        $path = self::getSnapshotPath($testName);
        throw_unless(file_exists($path), RuntimeException::class, sprintf('Snapshot %s does not exist', $testName));

        $content = file_get_contents($path);
        throw_unless($content !== false, RuntimeException::class, sprintf('Failed to read snapshot %s', $testName));

        return $content;
    }

    public static function saveSnapshot(string $testName, string $content): void
    {
        if (!is_dir(self::$snapshotDir)) {
            mkdir(self::$snapshotDir, 0o755, true);
        }

        file_put_contents(self::getSnapshotPath($testName), $content);
    }

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
