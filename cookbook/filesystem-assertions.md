# File System Assertions

File system assertions validate files, directories, and file permissions.

## Available Assertions

### file()

Assert that a file exists.

```php
use Cline\Assert\Assertion;

Assertion::file('/path/to/file.txt');
Assertion::file($configPath, 'Config file not found');
```

### directory()

Assert that a directory exists.

```php
Assertion::directory('/path/to/dir');
Assertion::directory($uploadsPath, 'Uploads directory not found');
```

### readable()

Assert that a file or directory is readable.

```php
Assertion::readable('/path/to/file.txt');
Assertion::readable($logFile, 'Cannot read log file');
```

### writeable()

Assert that a file or directory is writeable.

```php
Assertion::writeable('/path/to/file.txt');
Assertion::writeable($cacheDir, 'Cache directory is not writeable');
```

## Chaining File System Assertions

```php
use Cline\Assert\Assert;

Assert::that($configFile)
    ->string()
    ->notEmpty()
    ->file('Config file does not exist')
    ->readable('Config file is not readable');

Assert::that($uploadDir)
    ->directory('Upload directory missing')
    ->writeable('Upload directory is not writeable');
```

## Common Patterns

### Configuration File Validation

```php
$configPath = __DIR__ . '/config/app.php';

Assert::that($configPath)
    ->file('Configuration file not found')
    ->readable('Cannot read configuration file');

$config = require $configPath;
```

### Upload Directory Validation

```php
$uploadPath = storage_path('uploads');

Assert::that($uploadPath)
    ->directory('Upload directory does not exist')
    ->writeable('Cannot write to upload directory');
```

### Log File Validation

```php
$logFile = storage_path('logs/app.log');

if (file_exists($logFile)) {
    Assert::that($logFile)
        ->file()
        ->writeable('Cannot write to log file');
}
```

### Multiple Directory Validation

```php
$directories = [
    storage_path('cache'),
    storage_path('sessions'),
    storage_path('views'),
];

foreach ($directories as $dir) {
    Assert::that($dir)
        ->directory("Directory does not exist: {$dir}")
        ->writeable("Directory not writeable: {$dir}");
}
```

### Template File Validation

```php
$templatePath = resource_path("views/{$template}.blade.php");

Assert::that($templatePath)
    ->file("Template not found: {$template}")
    ->readable('Template file is not readable');
```

## Permission Checks

### Read Permission

```php
Assert::that($file)
    ->file('File does not exist')
    ->readable('File is not readable - check permissions');
```

### Write Permission

```php
Assert::that($file)
    ->file('File does not exist')
    ->writeable('File is not writeable - check permissions');
```

### Both Read and Write

```php
Assert::that($dataFile)
    ->file()
    ->readable('Cannot read data file')
    ->writeable('Cannot write to data file');
```

## Working with Paths

### Absolute Paths

```php
$absolutePath = realpath($relativePath);

Assert::that($absolutePath)
    ->notFalse('Path does not exist')
    ->file();
```

### Path Validation Before Use

```php
function loadConfig(string $path): array
{
    Assert::that($path)
        ->string()
        ->notEmpty()
        ->file('Config file not found')
        ->readable('Config file not readable');
    
    return require $path;
}
```

### Directory Creation Check

```php
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

Assert::that($cacheDir)
    ->directory('Failed to create cache directory')
    ->writeable('Cache directory is not writeable');
```

## Best Practices

### Check Existence First

```php
// ✓ Clear error messages
Assert::that($file)
    ->file('File does not exist')
    ->readable('File is not readable');

// ✗ Confusing if file doesn't exist
Assert::that($file)
    ->readable(); // "is not readable" even if file doesn't exist
```

### Validate Before Operations

```php
// ✓ Validate first
Assert::that($sourceFile)
    ->file()
    ->readable();

Assert::that($destDir)
    ->directory()
    ->writeable();

copy($sourceFile, $destDir . '/' . basename($sourceFile));

// ✗ No validation
copy($sourceFile, $destDir . '/' . basename($sourceFile)); // May fail silently
```

### Use Descriptive Messages

```php
// ✗ Generic message
Assertion::file($path);

// ✓ Specific context
Assertion::file($path, "Required configuration file not found: {$path}");
```

## Application Examples

### Asset Loading

```php
public function loadAsset(string $name): string
{
    $assetPath = public_path("assets/{$name}");
    
    Assert::that($assetPath)
        ->file("Asset not found: {$name}")
        ->readable("Cannot read asset: {$name}");
    
    return file_get_contents($assetPath);
}
```

### Cache Management

```php
public function setupCache(): void
{
    $cacheDir = storage_path('framework/cache');
    
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    
    Assert::that($cacheDir)
        ->directory('Failed to create cache directory')
        ->writeable('Cache directory must be writeable');
}
```

### Log Rotation

```php
public function rotateLog(string $logFile): void
{
    Assert::that($logFile)
        ->file('Log file does not exist')
        ->readable('Cannot read log file')
        ->writeable('Cannot write to log file');
    
    $backupFile = $logFile . '.' . date('Y-m-d');
    rename($logFile, $backupFile);
}
```

### Database Backup

```php
public function backupDatabase(string $backupPath): void
{
    $backupDir = dirname($backupPath);
    
    Assert::that($backupDir)
        ->directory('Backup directory does not exist')
        ->writeable('Cannot write to backup directory');
    
    // Perform backup...
}
```

## Security Considerations

### Path Traversal Prevention

```php
function loadTemplate(string $template): string
{
    // Sanitize input
    $template = basename($template);
    $templatePath = resource_path("templates/{$template}.php");
    
    Assert::that($templatePath)
        ->file('Template not found')
        ->readable('Template not accessible');
    
    return file_get_contents($templatePath);
}
```

### Permission Validation

```php
// Ensure proper permissions for sensitive files
Assert::that($privateKeyFile)
    ->file('Private key file not found')
    ->readable('Cannot read private key');

// Check that sensitive files are NOT world-readable
$perms = fileperms($privateKeyFile);
assert(($perms & 0004) === 0, 'Private key file is world-readable');
```

## Common Mistakes

### Not Checking Existence

```php
// ✗ Fails if file doesn't exist
Assertion::readable($file);

// ✓ Check existence first
Assert::that($file)
    ->file()
    ->readable();
```

### Relative vs Absolute Paths

```php
// ✗ May fail depending on working directory
Assertion::file('config/app.php');

// ✓ Use absolute path
Assertion::file(__DIR__ . '/config/app.php');

// ✓ Or use path helper
Assertion::file(config_path('app.php'));
```

## Next Steps

- **[String Assertions](string-assertions.md)** - Path string validation
- **[Custom Assertions](custom-assertions.md)** - Create custom file validators
- **[Lazy Assertions](lazy-assertions.md)** - Validate multiple paths at once
