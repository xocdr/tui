# Icon

A reusable component for displaying static icons, emoji icons, and animated icon sequences.

## Namespace

```php
use Xocdr\Tui\Widgets\Support\Icon;
use Xocdr\Tui\Widgets\Support\IconPresets;
```

## Overview

The Icon component provides a consistent interface for displaying icons throughout your TUI application. It supports:

- Static text icons (single characters like `✓`, `✗`, `●`, `○`)
- Emoji icons (`🚀`, `✅`, `❌`, `📁`)
- Animated icon sequences (frame-by-frame animation)
- Built-in spinner presets (dots, line, arc, circle, etc.)
- Color and styling options

## Basic Usage

### Static Icons

```php
// Simple text icon
Icon::text('✓')->render();

// With color
Icon::text('✓')->color('green')->render();

// Emoji icon
Icon::emoji('🚀')->render();
```

### Preset Icons

```php
// Status presets (with default colors)
Icon::success();   // Green ✓
Icon::error();     // Red ✗
Icon::warning();   // Yellow ⚠
Icon::info();      // Blue ℹ
Icon::pending();   // Dim ○
Icon::active();    // Cyan ●
Icon::complete();  // Green ◉
```

### Animated Spinners

```php
// Built-in spinner preset
$spinner = Icon::spinner('dots');

// In a component with hooks
[$frame, $setFrame] = $hooks->state(0);
$hooks->interval(fn() => $setFrame(fn($f) => $f + 1), $spinner->getSpeed());
$spinner->renderFrame($frame);

// Custom animation frames
Icon::animated(['◐', '◓', '◑', '◒'])->speed(100);

// Loading preset (alias for dots spinner)
Icon::loading();
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `Icon::text(string $char)` | Create icon from a single character |
| `Icon::emoji(string $emoji)` | Create icon from an emoji |
| `Icon::animated(array $frames)` | Create animated icon from frame array |
| `Icon::spinner(string $preset)` | Create spinner from preset name |
| `Icon::success()` | Green checkmark (✓) |
| `Icon::error()` | Red cross (✗) |
| `Icon::warning()` | Yellow warning (⚠) |
| `Icon::info()` | Blue info (ℹ) |
| `Icon::loading()` | Animated dots spinner |
| `Icon::pending()` | Dim circle (○) |
| `Icon::active()` | Cyan filled circle (●) |
| `Icon::complete()` | Green target (◉) |
| `Icon::fromPreset(string $name)` | Create from any preset name |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `speed(int $ms)` | int | Constants::DEFAULT_SPINNER_INTERVAL_MS (80) | Frame duration in milliseconds |
| `reverse(bool)` | bool | false | Reverse animation direction |
| `color(?string)` | string | null | Icon color |
| `dim(bool)` | bool | false | Apply dim styling |
| `bold(bool)` | bool | false | Apply bold styling |

## Query Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `isAnimated()` | bool | True if icon has multiple frames |
| `getFrames()` | array | All animation frames |
| `getFrameAt(int $index)` | string | Frame at index (wraps around) |
| `getFrameCount()` | int | Number of frames |
| `getSpeed()` | int | Animation speed in ms |
| `getColor()` | ?string | Current color |
| `isDim()` | bool | Is dim styling applied |
| `isBold()` | bool | Is bold styling applied |

## Rendering Methods

| Method | Description |
|--------|-------------|
| `render()` | Render first frame as Text component |
| `renderFrame(int $frame)` | Render specific frame as Text component |

## Available Spinner Presets

Access via `Icon::spinner($name)` or `IconPresets::SPINNERS[$name]`:

| Name | Frames | Style |
|------|--------|-------|
| `dots` | `⠋⠙⠹⠸⠼⠴⠦⠧⠇⠏` | Braille dots (default) |
| `dots2` | `⣾⣽⣻⢿⡿⣟⣯⣷` | Braille dots variant |
| `line` | `-\|/` | Classic line spinner |
| `arc` | `◜◠◝◞◡◟` | Arc spinner |
| `circle` | `◐◓◑◒` | Quarter circle |
| `square` | `◰◳◲◱` | Quarter square |
| `arrows` | `←↖↑↗→↘↓↙` | Rotating arrows |
| `bounce` | `⠁⠂⠄⠂` | Bouncing dot |
| `clock` | `🕐🕑...🕛` | Clock faces |
| `earth` | `🌍🌎🌏` | Rotating earth |
| `moon` | `🌑🌒...🌘` | Moon phases |

## Available Status Icons

Access via `Icon::success()`, etc. or `IconPresets::STATUS[$name]`:

| Name | Icon | Description |
|------|------|-------------|
| `success` | ✓ | Checkmark |
| `success_emoji` | ✅ | Emoji checkmark |
| `error` | ✗ | Cross |
| `error_emoji` | ❌ | Emoji cross |
| `warning` | ⚠ | Warning sign |
| `warning_emoji` | ⚠️ | Emoji warning |
| `info` | ℹ | Info symbol |
| `info_emoji` | ℹ️ | Emoji info |
| `pending` | ○ | Empty circle |
| `active` | ● | Filled circle |
| `complete` | ◉ | Target circle |

## Available Common Icons

Access via `IconPresets::COMMON[$name]`:

| Name | Icon | Name | Icon |
|------|------|------|------|
| `folder` | 📁 | `file` | 📄 |
| `git` | 🌿 | `star` | ⭐ |
| `rocket` | 🚀 | `lightning` | ⚡ |
| `bulb` | 💡 | `gear` | ⚙️ |
| `lock` | 🔒 | `key` | 🔑 |
| `check` | ✔ | `cross` | ✘ |
| `arrow_right` | → | `arrow_left` | ← |
| `arrow_up` | ↑ | `arrow_down` | ↓ |
| `play` | ▶ | `pause` | ⏸ |
| `stop` | ■ | | |

## Animation in Components

When using animated icons in a component, the parent component handles the animation loop:

```php
public function render(): mixed
{
    $hooks = $this->hooks();
    $icon = Icon::spinner('dots');

    if ($icon->isAnimated()) {
        [$frame, $setFrame] = $hooks->state(0);

        $hooks->interval(function () use ($setFrame, $icon) {
            $setFrame(fn($f) => ($f + 1) % $icon->getFrameCount());
        }, $icon->getSpeed());

        $iconElement = $icon->renderFrame($frame);
    } else {
        $iconElement = $icon->render();
    }

    return Box::row([
        $iconElement,
        Text::create(' Loading...'),
    ]);
}
```

## Examples

### Status Indicator

```php
$status = match ($state) {
    'success' => Icon::success(),
    'error' => Icon::error(),
    'loading' => Icon::loading(),
    default => Icon::pending(),
};

Box::row([
    $status->renderFrame($frame),
    Text::create(' ' . $message),
]);
```

### File Browser Icons

```php
$icon = $isDirectory
    ? Icon::emoji(IconPresets::COMMON['folder'])
    : Icon::emoji(IconPresets::COMMON['file']);

Box::row([
    $icon->render(),
    Text::create(' ' . $name),
]);
```

### Custom Styled Spinner

```php
Icon::spinner('arc')
    ->color('magenta')
    ->bold()
    ->speed(100)
    ->renderFrame($frame);
```

## See Also

- [Badge](./badge.md) - Uses icons for status indicators
- [TodoList](./todolist.md) - Uses icons for task status
- [LoadingState](./loadingstate.md) - Uses animated icons for loading states
