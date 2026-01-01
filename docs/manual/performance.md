# Performance Optimization

This guide covers performance optimization techniques for TUI applications.

## ext-tui INI Configuration

The ext-tui extension (v0.2.12+) provides INI options to tune runtime limits.

### Available INI Options

| Option | Default | Description |
|--------|---------|-------------|
| `tui.max_states` | 1000 | Maximum number of state slots per instance |
| `tui.max_timers` | 100 | Maximum number of active timers per instance |

### Configuring INI Options

**php.ini:**
```ini
tui.max_states = 2000
tui.max_timers = 200
```

**At runtime:**
```php
ini_set('tui.max_states', '2000');
ini_set('tui.max_timers', '200');
```

**When to increase limits:**
- `tui.max_states`: Increase if you have complex components with many state hooks
- `tui.max_timers`: Increase for applications with many concurrent animations or intervals

## Build-Time Optimization

### Disabling Metrics

For production builds, you can disable all metrics collection for zero overhead:

```bash
CFLAGS="-DTUI_DISABLE_METRICS" ./configure --enable-tui && make
```

When `TUI_DISABLE_METRICS` is defined, all `TUI_METRIC_*` macros expand to `((void)0)`.

## String Length Limits

ext-tui 0.2.12+ enforces string length limits for safety:

| Property | Max Length | Applies To |
|----------|------------|------------|
| `key` | 256 chars | Box, Text |
| `id` | 256 chars | Box, Text |
| `content` | 1 MB | Text |

Values exceeding these limits are truncated with a PHP warning. The xocdr/tui
library validates these limits at the PHP level to provide early warnings.

## Rendering Performance

### Minimize Re-renders

Use `memo()` to cache expensive computations:

```php
$this->hooks()->memo(function () use ($largeDataset) {
    return processExpensiveData($largeDataset);
}, [$largeDataset]);
```

### Use Keys for List Reconciliation

When rendering lists, always provide stable keys:

```php
Box::create([
    'item-1' => new ItemWidget($items[0]),
    'item-2' => new ItemWidget($items[1]),
]);

// Or with append:
$box = Box::create();
foreach ($items as $id => $item) {
    $box->append(new ItemWidget($item), "item-{$id}");
}
```

### Virtual Lists for Large Datasets

Use `VirtualList` for lists with many items:

```php
use Xocdr\Tui\Scroll\VirtualList;

$virtualList = new VirtualList(
    items: $thousandsOfItems,
    itemHeight: 1,
    visibleHeight: 20
);
```

## Memory Management

### State Cleanup

State and timers are automatically cleaned up when the application unmounts.
For long-running applications, be mindful of:

- Large objects stored in state (use WeakReference if appropriate)
- Intervals that accumulate data (clear periodically)

### Terminal Crash Recovery

ext-tui 0.2.12+ includes automatic crash recovery via `atexit()` handler.
The terminal will be restored to normal mode even after unexpected exits or SIGTERM.

## Profiling

### Memory Testing

Use the provided Makefile targets for memory analysis:

```bash
# In ext-tui directory
make test-valgrind        # Run all tests under valgrind
make test-asan            # Run with AddressSanitizer
make test-memory-quick    # Quick memory check
```

### Benchmarking

For performance regression testing, consider using PHPBench:

```php
class RenderBench
{
    /**
     * @Revs(1000)
     * @Iterations(5)
     */
    public function benchSimpleRender(): void
    {
        Tui::renderToString(new Text('Hello'));
    }
}
```
