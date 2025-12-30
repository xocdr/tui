# Images

Display images in the terminal using the Kitty Graphics Protocol.

## Terminal Support

| Terminal | Kitty Graphics | Sixel | Status |
|----------|----------------|-------|--------|
| Kitty | ✅ | ❌ | Full support |
| WezTerm | ✅ | ✅ | Full support |
| Konsole | ⚠️ | ❌ | Partial support |
| iTerm2 | ❌ | ❌ | Different protocol (not yet supported) |
| xterm | ❌ | ✅ | Sixel planned for future |
| foot | ❌ | ✅ | Sixel planned for future |

## Basic Usage

```php
use Xocdr\Tui\Components\Image;

// Load from a local file
$image = Image::fromPath('/path/to/logo.png');

// Load from a URL
$image = Image::fromUrl('https://example.com/image.png');

// Create from raw RGBA data
$rgba = str_repeat("\xFF\x00\x00\xFF", 4); // 2x2 red pixels
$image = Image::fromData($rgba, 2, 2, 'rgba');
```

## Sizing

Control how the image is displayed in the terminal:

```php
// Set both width and height in terminal columns/rows
$image = Image::fromPath('photo.png')
    ->size(40, 20);

// Set just width (height auto-calculated)
$image = Image::fromPath('photo.png')
    ->width(30);

// Set just height (width auto-calculated)
$image = Image::fromPath('photo.png')
    ->height(15);
```

## Fallback Text

When the terminal doesn't support graphics, a placeholder is shown:

```php
$image = Image::fromPath('logo.png')
    ->alt('Company Logo')
    ->size(30, 10);

// In unsupported terminals, displays:
// ┌────────────────────────────┐
// │                            │
// │       Company Logo         │
// │                            │
// └────────────────────────────┘
```

## Checking Support

```php
use Xocdr\Tui\Components\Image;

if (Image::isSupported()) {
    // Terminal supports Kitty graphics
    $app->render(Image::fromPath('logo.png'));
} else {
    // Show text alternative
    $app->render(Text::create('Logo not available'));
}
```

## Image Information

Get metadata about a loaded image:

```php
$image = Image::fromPath('photo.png');
$info = $image->getInfo();

// Returns: ['width' => 640, 'height' => 480, 'format' => 'png', 'state' => 'loaded']
```

## Loading from Different Sources

### From File Path

```php
// PNG files (recommended)
$image = Image::fromPath('/path/to/image.png');

// JPEG files
$image = Image::fromPath('/path/to/photo.jpg');

// Relative paths
$image = Image::fromPath(__DIR__ . '/assets/logo.png');
```

### From URL

Images are downloaded and cached in a temporary file:

```php
$image = Image::fromUrl('https://example.com/image.png');

// With custom alt text
$image = Image::fromUrl('https://api.example.com/avatar/123')
    ->alt('User Avatar');
```

### From Raw Data

Create images from pixel data:

```php
// RGBA format (4 bytes per pixel)
$rgba = "\xFF\x00\x00\xFF"; // Red pixel (R=255, G=0, B=0, A=255)
$image = Image::fromData($rgba, 1, 1, 'rgba');

// RGB format (3 bytes per pixel)
$rgb = "\xFF\x00\x00"; // Red pixel
$image = Image::fromData($rgb, 1, 1, 'rgb');

// PNG data
$pngData = file_get_contents('image.png');
$image = Image::fromData($pngData, 0, 0, 'png');
```

## Resource Management

The Image component automatically cleans up resources:

```php
$image = Image::fromPath('photo.png');
// ... use image

// Explicit cleanup (optional)
$image->destroy();

// Or let PHP garbage collection handle it
unset($image);
```

## Performance Considerations

1. **Image Size**: Large images are transmitted to the terminal. Consider resizing images before display.

2. **Caching**: Images loaded from URLs are cached in temporary files. They're cleaned up when the Image object is destroyed.

3. **Re-rendering**: Images are transmitted to terminal memory on first render. Subsequent renders reuse the cached image.

4. **Multiple Images**: Each image uses terminal memory. Clear unused images when displaying many:
   ```php
   $image->destroy(); // Frees terminal memory
   ```

## Examples

### Basic Display

```php
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Image;
use Xocdr\Tui\Components\Text;

Box::column([
    Text::create('Product Image')->bold(),
    Image::fromPath('product.png')
        ->size(40, 20)
        ->alt('Product Photo'),
    Text::create('$29.99'),
]);
```

### Image Grid

```php
Box::row([
    Image::fromPath('img1.png')->size(20, 10),
    Image::fromPath('img2.png')->size(20, 10),
    Image::fromPath('img3.png')->size(20, 10),
]);
```

### Conditional Display

```php
$renderImage = fn() => Image::isSupported()
    ? Image::fromPath('logo.png')->size(30, 10)
    : Text::create('[Logo]')->dim();

Box::column([
    $renderImage(),
    Text::create('Welcome to MyApp'),
]);
```

## API Reference

### Static Methods

| Method | Description |
|--------|-------------|
| `fromPath(string $path)` | Create image from file path |
| `fromUrl(string $url)` | Create image from URL |
| `fromData(string $data, int $w, int $h, string $format)` | Create from raw data |
| `isSupported()` | Check if terminal supports graphics |

### Instance Methods

| Method | Description |
|--------|-------------|
| `size(int $cols, int $rows)` | Set display size |
| `width(int $cols)` | Set display width |
| `height(int $rows)` | Set display height |
| `alt(string $text)` | Set fallback text |
| `getInfo()` | Get image metadata |
| `getSourcePath()` | Get source path/URL |
| `getColumns()` | Get display width |
| `getRows()` | Get display height |
| `getAlt()` | Get alt text |
| `destroy()` | Free resources |
| `render()` | Render the component |

## Future: Sixel Support

Sixel support is planned for terminals like xterm, mlterm, and foot. The API will remain the same - the Image component will automatically select the best available protocol.
