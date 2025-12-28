# Installation

## Requirements

- PHP 8.4 or higher
- ext-tui C extension (required)
- Terminal with Unicode support (recommended)

## Installing the C Extension

Before installing the Composer package, you need to install the `ext-tui` C extension:

```bash
# Clone the extension
git clone https://github.com/exocoder/ext-tui.git
cd ext-tui

# Build and install
phpize
./configure
make
sudo make install

# Enable the extension
echo "extension=tui.so" | sudo tee /etc/php/8.4/mods-available/tui.ini
sudo phpenmod tui
```

Verify the extension is loaded:

```bash
php -m | grep tui
```

## Installing the Composer Package

```bash
composer require exocoder/tui
```

## Verifying Installation

Create a simple test file:

```php
<?php
require 'vendor/autoload.php';

use Tui\Components\Box;
use Tui\Components\Text;
use Tui\Tui;

if (!extension_loaded('tui')) {
    die("ext-tui extension is not loaded\n");
}

$app = fn() => Box::column([
    Text::create('TUI is working!')->bold()->green(),
]);

$instance = Tui::render($app);
$instance->waitUntilExit();
```

Run the test:

```bash
php test.php
```

You should see "TUI is working!" displayed in green. Press ESC to exit.

## IDE Support

For better IDE support, you can install the IDE helper stubs:

```bash
composer require --dev exocoder/tui-stubs
```

This provides autocompletion for both the PHP package and the C extension.

## Docker Support

If using Docker, ensure your container has:

1. PHP 8.4+ with development headers
2. Build tools (gcc, make, autoconf)
3. The ext-tui extension built and enabled

Example Dockerfile snippet:

```dockerfile
FROM php:8.4-cli

# Install build dependencies
RUN apt-get update && apt-get install -y \
    git \
    autoconf \
    gcc \
    make

# Build ext-tui
RUN git clone https://github.com/exocoder/ext-tui.git /tmp/ext-tui \
    && cd /tmp/ext-tui \
    && phpize \
    && ./configure \
    && make \
    && make install \
    && docker-php-ext-enable tui

# Install Composer and the package
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY composer.json .
RUN composer install
```

## Troubleshooting

### Extension not found

If you see "ext-tui extension is not loaded", check:

1. The extension was compiled for the correct PHP version
2. The extension is enabled in php.ini
3. Run `php -i | grep tui` to see if it's loaded

### Terminal issues

If you see garbled output:

1. Ensure your terminal supports Unicode (UTF-8)
2. Use a modern terminal emulator (iTerm2, Windows Terminal, etc.)
3. Check that `LANG` environment variable includes UTF-8

### Permission denied

If you get permission errors when installing:

```bash
sudo make install
# or
make install INSTALL_ROOT=/home/user/.local
```
