# Notifications

TUI provides terminal notification support through the `Notification` class, enabling audible alerts, visual flashes, and desktop notifications.

## Overview

The `Terminal\Notification` class offers multiple notification methods:
- **Bell** - Audible terminal bell sound
- **Flash** - Visual screen flash (reverse video)
- **Notify** - Desktop notifications via OSC sequences
- **Alert** - Combined bell + flash + desktop notification

## Basic Usage

```php
use Xocdr\Tui\Terminal\Notification;

// Simple audible alert
Notification::bell();

// Desktop notification
Notification::notify('Download Complete', 'Your file has been downloaded');
```

## Audible Alert (Bell)

The terminal bell is the classic way to get user attention:

```php
Notification::bell();
```

This outputs the BEL character (`\x07`), which:
- Plays a sound on most terminals
- May be muted in user preferences
- Works universally across all terminals

**Use cases:**
- Input validation errors
- Task completion alerts
- Warning notifications

## Visual Alert (Flash)

Screen flash provides a visual alternative for users who may not hear the bell:

```php
Notification::flash();
```

This uses DECSCNM (DEC Screen Mode) sequences to briefly reverse the screen colors.

**Use cases:**
- Accessibility-friendly alerts
- Silent notifications
- Combined with bell for emphasis

## Desktop Notifications

Send notifications that appear in the system notification center:

```php
// Basic notification
Notification::notify('Title', 'Body message');

// Title only
Notification::notify('Alert!');

// Urgent notification
Notification::notify('Critical', 'System error', Notification::PRIORITY_URGENT);
```

### Priority Levels

```php
Notification::PRIORITY_NORMAL  // 0 - Standard notification
Notification::PRIORITY_URGENT  // 1 - High priority
```

### Terminal Support

Desktop notifications use OSC (Operating System Command) sequences:

| Terminal | Support | Notes |
|----------|---------|-------|
| iTerm2 | Full | Uses OSC 9 and OSC 777 |
| Kitty | Full | Uses OSC 99 |
| WezTerm | Full | Uses OSC 99 |
| Terminal.app | Partial | OSC 9 only |
| Konsole | Limited | May require configuration |
| xterm | None | Not supported |

## Combined Alert

For maximum attention, use `alert()` which combines all notification methods:

```php
// Bell + flash + desktop notification
Notification::alert('Critical system error!');

// Bell + flash only (no desktop notification)
Notification::alert();
```

## Practical Examples

### Form Validation Error

```php
$onSubmit = function () use ($errors) {
    if (count($errors) > 0) {
        Notification::bell();
        return; // Show error messages
    }
    // Process form
};
```

### Background Task Completion

```php
$onComplete = function ($result) {
    if ($result['success']) {
        Notification::notify(
            'Build Complete',
            "Built {$result['files']} files in {$result['time']}s"
        );
    } else {
        Notification::alert('Build Failed: ' . $result['error']);
    }
};
```

### Silent Mode Support

```php
$silentMode = getenv('TUI_SILENT') === '1';

function notifyUser(string $message, bool $urgent = false) {
    global $silentMode;

    if ($silentMode) {
        // Visual only
        Notification::flash();
    } elseif ($urgent) {
        Notification::alert($message);
    } else {
        Notification::bell();
        Notification::notify('TUI App', $message);
    }
}
```

### Timer Alert

```php
$component = function () {
    [$timeLeft, $setTimeLeft] = useState(60);

    useInterval(function () use ($timeLeft, $setTimeLeft) {
        if ($timeLeft > 0) {
            $setTimeLeft($timeLeft - 1);
        } elseif ($timeLeft === 0) {
            Notification::alert('Timer finished!');
            $setTimeLeft(-1); // Prevent repeated alerts
        }
    }, 1000);

    return Text::create("Time left: {$timeLeft}s");
};
```

## OSC Sequences Reference

For advanced users, here are the underlying sequences:

```php
// OSC 9 (iTerm2 / Terminal.app)
echo "\033]9;{$message}\007";

// OSC 99 (Kitty / WezTerm)
echo "\033]99;i=1:u={$priority};{$message}\033\\";

// OSC 777 (notification with title)
echo "\033]777;notify;{$title};{$body}\007";
```

## API Reference

| Method | Description |
|--------|-------------|
| `bell(): bool` | Play terminal bell sound |
| `flash(): bool` | Flash screen (reverse video) |
| `notify(string $title, ?string $body = null, int $priority = 0): bool` | Send desktop notification |
| `alert(?string $message = null): void` | Combined bell + flash + notify |

### Constants

| Constant | Value | Description |
|----------|-------|-------------|
| `PRIORITY_NORMAL` | 0 | Standard notification priority |
| `PRIORITY_URGENT` | 1 | High priority notification |

## Best Practices

1. **Don't spam notifications** - Use sparingly for important events

2. **Provide visual alternatives** - Not all users hear audio
   ```php
   Notification::bell();
   Notification::flash();
   ```

3. **Use appropriate priority** - Reserve urgent for critical issues

4. **Support silent mode** - Allow users to disable audio alerts

5. **Keep messages concise** - Desktop notifications have limited space

6. **Test across terminals** - Notification support varies
