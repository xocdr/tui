# Accessibility

TUI provides comprehensive accessibility support through the `Accessibility` class, enabling screen reader announcements, preference detection, and ARIA role utilities.

## Overview

The `Terminal\Accessibility` class helps create inclusive terminal applications by:
- Announcing messages to screen readers
- Detecting user accessibility preferences
- Providing ARIA role utilities for semantic markup

## Basic Usage

```php
use Xocdr\Tui\Terminal\Accessibility;

// Screen reader announcement
Accessibility::announce('File saved successfully');

// Check user preferences
if (Accessibility::prefersReducedMotion()) {
    // Skip or simplify animations
}
```

## Screen Reader Announcements

Use `announce()` to communicate with assistive technologies:

```php
// Polite announcement (default) - waits for idle
Accessibility::announce('3 items loaded');

// Assertive announcement - interrupts immediately
Accessibility::announce('Error: Connection lost', 'assertive');
```

**Priority levels:**
- `'polite'` - Waits for screen reader to finish current task
- `'assertive'` - Interrupts immediately for urgent messages

## Preference Detection

### Reduced Motion

Respects the user's system preference for reduced motion:

```php
if (Accessibility::prefersReducedMotion()) {
    // Use instant transitions instead of animations
    $opacity = 1.0;
} else {
    // Use animation
    $anim = animation(0, 1.0, 300);
    $opacity = $anim['value'];
}
```

This preference can be set via:
- System accessibility settings (macOS, Windows, Linux)
- Environment variable: `REDUCE_MOTION=1`

### High Contrast

Detects preference for high contrast colors:

```php
if (Accessibility::prefersHighContrast()) {
    $textColor = 'white';
    $bgColor = 'black';
    $borderColor = 'white';
} else {
    $textColor = 'gray-300';
    $bgColor = 'gray-800';
    $borderColor = 'gray-600';
}
```

This preference can be set via:
- System accessibility settings
- Environment variable: `HIGH_CONTRAST=1`

### Get All Features

Retrieve all accessibility preferences at once:

```php
$features = Accessibility::getFeatures();
// [
//     'reduced_motion' => bool,
//     'high_contrast' => bool,
//     'screen_reader' => bool,
// ]
```

## ARIA Role Utilities

ARIA roles help screen readers understand the purpose of UI elements.

### Role Constants

```php
Accessibility::ROLE_NONE        // 0 - No specific role
Accessibility::ROLE_BUTTON      // 1 - Interactive button
Accessibility::ROLE_CHECKBOX    // 2 - Toggle checkbox
Accessibility::ROLE_DIALOG      // 3 - Modal dialog
Accessibility::ROLE_NAVIGATION  // 4 - Navigation container
Accessibility::ROLE_MENU        // 5 - Menu container
Accessibility::ROLE_MENUITEM    // 6 - Menu item
Accessibility::ROLE_TEXTBOX     // 7 - Text input
Accessibility::ROLE_ALERT       // 8 - Alert message
Accessibility::ROLE_STATUS      // 9 - Status indicator
```

### Converting Between Formats

```php
// Integer to string
$roleString = Accessibility::roleToString(Accessibility::ROLE_BUTTON);
// Returns: 'button'

// String to integer
$roleInt = Accessibility::roleFromString('dialog');
// Returns: Accessibility::ROLE_DIALOG (3)
```

## Integration with Hooks

The `animation()` hook automatically respects reduced motion:

```php
use Xocdr\Tui\Widgets\Widget;

class FadeInWidget extends Widget
{
    public function build(): Component
    {
        $fade = $this->hooks()->animation(0, 1.0, 500);

        if ($fade['prefersReducedMotion']) {
            // Animation completed instantly
        }

        // Use $fade['value'] for opacity...
    }
}
```

To override this behavior:

```php
// Force animation even with reduced motion preference
$fade = $this->hooks()->animation(0, 1.0, 500, 'linear', false);
```

## Best Practices

1. **Announce state changes** - Notify users when important changes occur
   ```php
   Accessibility::announce('Form submitted successfully');
   ```

2. **Respect reduced motion** - Check the preference before animating
   ```php
   if (!Accessibility::prefersReducedMotion()) {
       // Start animation
   }
   ```

3. **Use semantic roles** - Apply appropriate ARIA roles to components

4. **Provide high contrast alternatives** - Support users who need it

5. **Test with screen readers** - Verify announcements work correctly

## Example: Accessible Dialog

```php
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Terminal\Accessibility;
use Xocdr\Tui\Widgets\Widget;

class AccessibleDialog extends Widget
{
    public function build(): Component
    {
        [$isOpen, $setIsOpen] = $this->hooks()->state(false);

        $this->hooks()->onRender(function () use ($isOpen) {
            if ($isOpen) {
                Accessibility::announce('Dialog opened');
            }
        }, [$isOpen]);

        if (!$isOpen) {
            return Text::create('');
        }

        $instant = Accessibility::prefersReducedMotion();
        $animation = $this->hooks()->animation(0, 1, 200);
        $opacity = $instant ? 1.0 : $animation['value'];

        return Box::create()
            ->border('round')
            ->children([
                Text::create('Confirm Action')->bold(),
                Text::create('Are you sure you want to proceed?'),
            ]);
    }
}
```

## Environment Variables

| Variable | Values | Description |
|----------|--------|-------------|
| `REDUCE_MOTION` | `1`, `true`, `yes` | Enable reduced motion |
| `HIGH_CONTRAST` | `1`, `true`, `yes` | Enable high contrast |

## API Reference

| Method | Description |
|--------|-------------|
| `announce(string $message, string $priority = 'polite'): bool` | Send screen reader announcement |
| `prefersReducedMotion(): bool` | Check reduced motion preference |
| `prefersHighContrast(): bool` | Check high contrast preference |
| `getFeatures(): array` | Get all accessibility features |
| `roleToString(int $role): string` | Convert role constant to string |
| `roleFromString(string $role): int` | Convert role string to constant |
