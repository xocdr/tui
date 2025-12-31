# Image

A terminal image widget with protocol auto-detection.

## Namespace

```php
use Xocdr\Tui\Widgets\Visual\Image;
```

## Overview

The Image widget displays images in the terminal. Features include:

- iTerm2 and Kitty protocol support
- Auto-detection of terminal capabilities
- ASCII fallback for unsupported terminals
- URL and file path loading
- Size constraints

## Console Appearance

**In supported terminals:** Actual image display

**ASCII fallback:**
```
░▒▓█░▒▓█░▒▓█░▒▓█
▒▓█░▒▓█░▒▓█░▒▓█░
▓█░▒▓█░▒▓█░▒▓█░▒
[image.png]
```

## Basic Usage

```php
Image::fromFile('/path/to/image.png');

Image::fromUrl('https://example.com/image.jpg')
    ->maxWidth(80)
    ->maxHeight(24);

Image::create()
    ->path('/path/to/logo.png')
    ->width(40)
    ->preserveAspectRatio();
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `Image::create()` | Create empty image |
| `Image::fromFile(path)` | Create from file path |
| `Image::fromUrl(url)` | Create from URL |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `path(string)` | string | null | File path |
| `url(string)` | string | null | Image URL |
| `width(int)` | int | null | Exact width |
| `height(int)` | int | null | Exact height |
| `maxWidth(int)` | int | null | Maximum width |
| `maxHeight(int)` | int | null | Maximum height |
| `preserveAspectRatio(bool)` | bool | true | Keep aspect ratio |
| `protocol(string)` | string | 'auto' | Force protocol |
| `fallback(string)` | string | 'ascii' | Fallback mode |

## Supported Protocols

| Protocol | Description |
|----------|-------------|
| `auto` | Auto-detect terminal |
| `iterm` | iTerm2 inline images |
| `kitty` | Kitty graphics protocol |
| `sixel` | Sixel graphics (fallback) |
| `ascii` | ASCII art placeholder |

## Terminal Detection

The widget automatically detects:
- iTerm2 via `TERM_PROGRAM`
- Kitty via `TERM` or `KITTY_WINDOW_ID`

## See Also

- [BigText](./bigtext.md) - ASCII art text
