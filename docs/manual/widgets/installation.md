# Installation

## Requirements

- PHP 8.4 or higher
- ext-tui C extension
- Composer

## Installing ext-tui

TUI requires the ext-tui C extension. Install it based on your platform:

### macOS (Homebrew)

```bash
brew tap xocdr/tui
brew install php-tui
```

### Linux (Ubuntu/Debian)

```bash
# Add the repository
curl -sSL https://packages.xocdr.dev/gpg.key | sudo apt-key add -
echo "deb https://packages.xocdr.dev/apt stable main" | sudo tee /etc/apt/sources.list.d/xocdr.list

# Install the extension
sudo apt update
sudo apt install php-tui
```

### From Source

```bash
git clone https://github.com/xocdr/ext-tui.git
cd ext-tui
phpize
./configure
make
sudo make install
```

Then add to your php.ini:

```ini
extension=tui
```

## Installing the Package

Use Composer to install TUI:

```bash
composer require xocdr/tui
```

## Verifying Installation

Create a simple test file:

```php
<?php

require 'vendor/autoload.php';

use Xocdr\Tui\Tui;
use Xocdr\Tui\Components\Text;

if (!Tui::isInteractive()) {
    echo "Error: Requires interactive terminal.\n";
    exit(1);
}

Tui::render(fn() => Text::create('Hello, TUI!'))->waitUntilExit();
```

Run it:

```bash
php test.php
```

If you see "Hello, TUI!" displayed in your terminal, the installation is complete.

## Development Setup

For development, clone the repository and install dependencies:

```bash
git clone https://github.com/xocdr/tui.git
cd tui
composer install
```

Run tests to verify everything works:

```bash
./vendor/bin/phpunit
```

## IDE Setup

For best development experience with PHPStan type hints:

### PHPStorm

1. Install the PHP Annotations plugin
2. Enable type inference from PHPDoc

### VS Code

1. Install PHP Intelephense
2. Configure `intelephense.stubs` to include your custom stubs

## Troubleshooting

### Extension not found

If PHP can't find the extension:

```bash
php -m | grep tui
```

If tui is not listed, check:
1. The extension is installed in the correct PHP extensions directory
2. php.ini includes `extension=tui`
3. You're using the correct PHP version

### Interactive terminal required

TUI requires an interactive terminal. This error occurs when:
- Running in a CI environment
- Piping input/output
- Running in certain IDEs' built-in terminals

Use `Tui::isInteractive()` to check before rendering.

## See Also

- [Getting Started](../getting-started.md) - Core concepts and first application
- [Widget Getting Started](getting-started.md) - First widget application
