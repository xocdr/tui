# StatusBar

A configurable status bar widget with segments for displaying contextual information.

## Namespace

```php
use Xocdr\Tui\Widgets\Feedback\StatusBar;
use Xocdr\Tui\Widgets\Feedback\StatusBarSegment;
use Xocdr\Tui\Widgets\Feedback\StatusBarContext;
use Xocdr\Tui\Widgets\Feedback\Segments\TextSegment;
use Xocdr\Tui\Widgets\Feedback\Segments\MeterSegment;
use Xocdr\Tui\Widgets\Feedback\Segments\TimerSegment;
use Xocdr\Tui\Widgets\Feedback\Segments\GitSegment;
use Xocdr\Tui\Widgets\Feedback\Segments\CallbackSegment;
```

## Overview

The StatusBar widget displays contextual information in a horizontal bar with left and right aligned segments. Features include:

- Segment-based composition with left/right alignment
- Configurable separators
- Background color fill
- Dynamic content via context provider
- Periodic updates (default 300ms)
- Built-in segment types

## Console Appearance

**Basic with segments:**
```
│ Sonnet 4 │ my-project │ main ✓ │                           │ 5K/200K │
```

**Claude Code style:**
```
[Opus] 📁 my-project │ 🌿 main ✓ │               │ Context: 15% │ $0.02 │ 45s
```

## Basic Usage

```php
StatusBar::create()
    ->left([
        TextSegment::create('My App'),
        GitSegment::create(),
    ])
    ->right([
        TimerSegment::create()->since($startTime),
    ]);

// With dynamic context
StatusBar::create()
    ->contextProvider(fn() => [
        'model' => 'Opus',
        'tokens' => 15234,
    ])
    ->left([
        TextSegment::create(fn($ctx) => "[{$ctx->data['model']}]"),
    ]);
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `StatusBar::create()` | Create a new status bar |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `left(array)` | array | [] | Left-aligned segments |
| `right(array)` | array | [] | Right-aligned segments |
| `segment(string, StatusBarSegment)` | - | - | Add named segment |
| `removeSegment(string)` | - | - | Remove segment by ID |
| `separator(string)` | string | ' │ ' | Segment separator |
| `backgroundColor(string)` | string | null | Bar background color |
| `padding(int)` | int | 0 | Edge padding |
| `contextProvider(callable)` | callable | null | Dynamic data provider |
| `updateInterval(int)` | int | 300 | Update interval in ms |

## Built-in Segments

### TextSegment

Display static or dynamic text.

```php
TextSegment::create('Static text');
TextSegment::create(fn($ctx) => "Dynamic: {$ctx->data['value']}")
    ->color('cyan')
    ->bold();
```

| Method | Description |
|--------|-------------|
| `create(string\|callable)` | Create with content |
| `color(string)` | Text color |
| `bold(bool)` | Bold text |
| `dim(bool)` | Dimmed text |
| `visibleWhen(callable)` | Visibility condition |

### MeterSegment

Display progress or capacity.

```php
MeterSegment::create()
    ->current(fn($ctx) => $ctx->data['tokens'])
    ->max(fn($ctx) => $ctx->data['max_tokens'])
    ->format(fn($c, $m) => sprintf('Context: %d%%', ($c / $m) * 100));
```

| Method | Description |
|--------|-------------|
| `create()` | Create meter |
| `current(int\|callable)` | Current value |
| `max(int\|callable)` | Maximum value |
| `format(callable)` | Custom format function |
| `color(string)` | Text color |
| `visibleWhen(callable)` | Visibility condition |

### TimerSegment

Display elapsed time.

```php
TimerSegment::create()
    ->since($startTime)
    ->showHours(true)
    ->showMinutes(true)
    ->showSeconds(true);
```

| Method | Description |
|--------|-------------|
| `create()` | Create timer |
| `since(float)` | Start timestamp |
| `showHours(bool)` | Show hours |
| `showMinutes(bool)` | Show minutes |
| `showSeconds(bool)` | Show seconds |
| `color(string)` | Text color |
| `visibleWhen(callable)` | Visibility condition |

### GitSegment

Display git branch and status.

```php
GitSegment::create()
    ->icon('🌿')
    ->branchColor('green')
    ->dirtyColor('yellow');
```

| Method | Description |
|--------|-------------|
| `create()` | Create git segment |
| `icon(string)` | Branch icon |
| `branchColor(string)` | Branch name color |
| `dirtyColor(string)` | Dirty indicator color |
| `branchProvider(callable)` | Custom branch provider |
| `dirtyProvider(callable)` | Custom dirty state provider |
| `visibleWhen(callable)` | Visibility condition |

## StatusBarContext

Context object passed to all segments.

```php
class StatusBarContext
{
    public array $data;          // Data from contextProvider
    public int $terminalWidth;   // Terminal width
    public float $timestamp;     // Current timestamp
}
```

## Examples

### Claude Code Style

```php
$startTime = microtime(true);

StatusBar::create()
    ->contextProvider(fn() => [
        'model' => ['display_name' => 'Opus'],
        'workspace' => ['current_dir' => getcwd()],
        'context_window' => [
            'total_input_tokens' => 15234,
            'context_window_size' => 200000,
        ],
        'cost' => ['total_cost_usd' => 0.0123],
        'git' => ['branch' => 'main', 'dirty' => false],
    ])
    ->left([
        TextSegment::create(fn($ctx) => "[{$ctx->data['model']['display_name']}]")
            ->bold(),
        TextSegment::create(fn($ctx) => "📁 " . basename($ctx->data['workspace']['current_dir'])),
        GitSegment::create(),
    ])
    ->right([
        MeterSegment::create()
            ->current(fn($ctx) => $ctx->data['context_window']['total_input_tokens'])
            ->max(fn($ctx) => $ctx->data['context_window']['context_window_size'])
            ->format(fn($c, $m) => sprintf('Context: %d%%', (int)(($c / $m) * 100))),
        TextSegment::create(fn($ctx) => sprintf('$%.2f', $ctx->data['cost']['total_cost_usd'])),
        TimerSegment::create()->since($startTime),
    ]);
```

### Simple Status Bar

```php
StatusBar::create()
    ->backgroundColor('blue')
    ->left([
        TextSegment::create('My Application')->bold(),
    ])
    ->right([
        TextSegment::create('v1.0.0')->dim(),
    ]);
```

### Conditional Visibility

```php
StatusBar::create()
    ->contextProvider(fn() => ['connected' => true])
    ->left([
        TextSegment::create('🟢 Online')
            ->color('green')
            ->visibleWhen(fn($ctx) => $ctx->data['connected']),
        TextSegment::create('🔴 Offline')
            ->color('red')
            ->visibleWhen(fn($ctx) => !$ctx->data['connected']),
    ]);
```

## See Also

- [Meter](./meter.md) - Standalone progress meter
- [Badge](./badge.md) - Status indicators
- [LoadingState](./loadingstate.md) - Loading indicators
