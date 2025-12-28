# ext-tui: Terminal UI C Extension - Complete Specification

## Overview

**ext-tui** is a PHP C extension that provides high-performance terminal UI capabilities with a component-based architecture, Yoga flexbox layout engine, and efficient terminal rendering. It enables building rich, interactive terminal applications in PHP with modern UI patterns.

**Key Stats:**
- **Language**: C with C++ (Yoga library)
- **Architecture**: Component-based with virtual DOM
- **Layout Engine**: Facebook Yoga (flexbox)
- **Rendering**: Double-buffered terminal output
- **Event System**: Poll-based event loop
- **PHP Support**: PHP 8.1+

---

## Architecture Overview

```
┌────────────────────────────────────────────────────────┐
│                    PHP Layer                           │
│  TuiBox │ TuiText │ TuiInstance │ TuiKey │ TuiFocusEvent│
└────┬───────────────────────────────────────────────────┘
     │
┌────▼────────────────────────────────────────────────────┐
│                  tui.c (PHP Bridge)                     │
│  - Class definitions & methods                          │
│  - Function exports                                     │
│  - PHP↔C object mapping                                 │
└────┬────────────────────────────────────────────────────┘
     │
┌────▼────────────────────────────────────────────────────┐
│              Core Systems (src/)                        │
├─────────────────────────────────────────────────────────┤
│ app/           │ Event loop coordination                │
│ node/          │ Virtual DOM & Yoga layout              │
│ event/         │ Input parsing & loop                   │
│ render/        │ Buffer & output                        │
│ terminal/      │ Raw mode & ANSI codes                  │
│ text/          │ Unicode width & wrapping               │
│ drawing/       │ Canvas, sprites, animation             │
│ yoga/          │ Flexbox layout (vendored)              │
└─────────────────────────────────────────────────────────┘
```

---

## PHP API Reference

### Core Functions

#### Terminal Information

**`tui_get_terminal_size(): array`**
- Returns `[int $width, int $height]`
- Gets current terminal dimensions
- Uses `ioctl(TIOCGWINSZ)`

**`tui_is_interactive(): bool`**
- Returns true if stdin/stdout are connected to TTY
- Checks `isatty(STDIN_FILENO)` and `isatty(STDOUT_FILENO)`

**`tui_is_ci(): bool`**
- Detects CI environments (GitHub Actions, GitLab CI, Travis, CircleCI, etc.)
- Checks environment variables like `CI`, `GITHUB_ACTIONS`, `GITLAB_CI`

#### Rendering & Lifecycle

**`tui_render(callable $component, array $options = []): TuiInstance`**
- Mounts and renders a component tree
- **Options:**
  - `fullscreen` (bool, default: true) - Use alternate screen buffer
  - `exitOnCtrlC` (bool, default: true) - Auto-exit on Ctrl+C
- **Flow:**
  1. Creates `tui_app` instance
  2. Calls `$component()` to get root node
  3. Converts PHP tree to C nodes via `php_to_tui_node()`
  4. Performs Yoga layout calculation
  5. Renders to terminal
  6. Starts event loop
- **Returns:** `TuiInstance` for control

**`tui_rerender(TuiInstance $instance): void`**
- Triggers immediate re-render
- Calls component callback again
- Reconciles old/new trees
- Marks affected cells as dirty
- Renders only changed cells to terminal

**`tui_unmount(TuiInstance $instance): void`**
- Stops the TUI
- Restores terminal raw mode
- Cleans up all resources
- Exits gracefully

**`tui_wait_until_exit(TuiInstance $instance): void`**
- Blocks execution until TUI exits
- Runs the event loop
- Processes input, resize, timer events
- Returns after `exit()` or `waitUntilExit()` called

#### Event Handling

**`tui_set_input_handler(TuiInstance $instance, callable $handler): void`**
- Registers keyboard input callback
- Signature: `function(TuiKey $key): void`
- Called for every keyboard event
- Can trigger `tui_rerender()` to update display

**`tui_set_focus_handler(TuiInstance $instance, callable $handler): void`**
- Registers focus change callback
- Signature: `function(TuiFocusEvent $event): void`
- Receives previous and current node info
- Direction: 'next', 'prev', or 'programmatic'

**`tui_set_resize_handler(TuiInstance $instance, callable $handler): void`**
- Registers terminal resize callback
- Signature: `function(int $width, int $height): void`
- Triggered on SIGWINCH

**`tui_set_tick_handler(TuiInstance $instance, callable $handler): void`**
- Registers tick/frame callback
- Called on each event loop iteration
- Useful for animations

#### Focus Management

**`tui_focus_next(TuiInstance $instance): void`**
- Moves focus to next focusable element
- Traverses depth-first through tree
- Wraps at end

**`tui_focus_prev(TuiInstance $instance): void`**
- Moves focus to previous focusable element
- Wraps at beginning

**`tui_get_focused_node(TuiInstance $instance): ?array`**
- Returns info about currently focused node
- Returns null if no focus
- Array keys: `focusable`, `focused`, `x`, `y`, `width`, `height`, `type`, `content`

**`tui_get_size(TuiInstance $instance): ?array`**
- Returns render size
- Keys: `width`, `height`, `columns`, `rows`

#### Timers

**`tui_add_timer(TuiInstance $instance, int $intervalMs, callable $handler): int`**
- Creates repeating timer
- Signature: `function(): void`
- Returns timer ID

**`tui_remove_timer(TuiInstance $instance, int $timerId): void`**
- Cancels timer
- Safe to call on already-removed timers

#### Text Utilities

**`tui_string_width(string $text): int`**
- Returns display width of text
- Handles UTF-8 multi-byte characters
- Correct for wide characters (CJK, emoji)
- Strips zero-width characters

**`tui_wrap_text(string $text, int $width): array`**
- Wraps text to fit width
- Returns array of lines
- Uses default wrap mode (word-based)

**`tui_truncate(string $text, int $width, string $ellipsis = '...'): string`**
- Truncates text to width
- Adds ellipsis if truncated
- Respects text width calculation

**`tui_pad(string $text, int $width, string $align = 'left', string $padChar = ' '): string`**
- Pads text to width
- `$align`: 'left', 'right', 'center'
- Handles multi-byte characters correctly

#### Drawing Primitives (Buffer-based)

**`tui_buffer_create(int $width, int $height): mixed`**
- Creates character buffer resource
- Returns resource handle

**`tui_buffer_clear(mixed $buffer): void`**
- Clears buffer (all cells blank)
- Resets all styles

**`tui_buffer_render(mixed $buffer, int $x, int $y): void`**
- Renders buffer to screen at position
- Applies all cells' styles

**`tui_draw_line(mixed $buffer, int $x1, int $y1, int $x2, int $y2, string $char = '─', array $style = []): void`**
- Draws line using Bresenham's algorithm
- `$char`: character to use for line
- `$style`: color and attributes

**`tui_draw_rect(mixed $buffer, int $x, int $y, int $width, int $height, string $borderStyle = 'single', array $style = []): void`**
- Draws rectangle outline
- `$borderStyle`: 'single', 'double', 'round', 'bold', 'dashed'
- Uses box-drawing characters

**`tui_fill_rect(mixed $buffer, int $x, int $y, int $width, int $height, string $char = '█', array $style = []): void`**
- Fills rectangle with character

**`tui_draw_circle(mixed $buffer, int $cx, int $cy, int $radius, string $char = '●', array $style = []): void`**
- Draws circle outline (midpoint algorithm)

**`tui_fill_circle(mixed $buffer, int $cx, int $cy, int $radius, string $char = '●', array $style = []): void`**
- Fills circle

**`tui_draw_ellipse(mixed $buffer, int $cx, int $cy, int $rx, int $ry, string $char = '●', array $style = []): void`**
- Draws ellipse outline

**`tui_fill_ellipse(mixed $buffer, int $cx, int $cy, int $rx, int $ry, string $char = '●', array $style = []): void`**
- Fills ellipse

**`tui_draw_triangle(mixed $buffer, int $x1, int $y1, int $x2, int $y2, int $x3, int $y3, string $char = '▲', array $style = []): void`**
- Draws triangle outline

**`tui_fill_triangle(mixed $buffer, int $x1, int $y1, int $x2, int $y2, int $x3, int $y3, string $char = '▲', array $style = []): void`**
- Fills triangle (scanline algorithm)

#### Canvas API (High-Resolution Drawing)

**`tui_canvas_create(int $width, int $height, string $mode = 'braille'): mixed`**
- Creates canvas resource
- `$mode`: 'braille' (2x4), 'block' (2x2), 'ascii' (1x1)
- Braille: highest resolution via U+2800 to U+28FF

**`tui_canvas_set(mixed $canvas, int $x, int $y): void`**
- Sets pixel in canvas coordinates

**`tui_canvas_unset(mixed $canvas, int $x, int $y): void`**
- Clears pixel

**`tui_canvas_toggle(mixed $canvas, int $x, int $y): void`**
- Toggles pixel state

**`tui_canvas_get(mixed $canvas, int $x, int $y): bool`**
- Gets pixel state

**`tui_canvas_clear(mixed $canvas): void`**
- Clears all pixels

**`tui_canvas_line(mixed $canvas, int $x1, int $y1, int $x2, int $y2): void`**
- Draws line in canvas pixels

**`tui_canvas_rect(mixed $canvas, int $x, int $y, int $w, int $h): void`**
- Draws rectangle outline

**`tui_canvas_fill_rect(mixed $canvas, int $x, int $y, int $w, int $h): void`**
- Fills rectangle

**`tui_canvas_circle(mixed $canvas, int $cx, int $cy, int $radius): void`**
- Draws circle outline

**`tui_canvas_fill_circle(mixed $canvas, int $cx, int $cy, int $radius): void`**
- Fills circle

**`tui_canvas_set_color(mixed $canvas, int $r, int $g, int $b): void`**
- Sets drawing color for rendering

**`tui_canvas_get_resolution(mixed $canvas): array`**
- Returns `[int $pixelWidth, int $pixelHeight]`

**`tui_canvas_render(mixed $canvas): array`**
- Renders canvas to array of UTF-8 strings
- Returns lines ready to display

#### Animation & Color Utilities

**`tui_ease(float $t, string $easing = 'linear'): float`**
- Applies easing function to progress value
- `$t`: progress 0.0 to 1.0
- **Easing types:**
  - `linear`
  - `in-quad`, `out-quad`, `in-out-quad`
  - `in-cubic`, `out-cubic`, `in-out-cubic`
  - `in-quart`, `out-quart`, `in-out-quart`
  - `in-sine`, `out-sine`, `in-out-sine`
  - `in-expo`, `out-expo`, `in-out-expo`
  - `in-circ`, `out-circ`, `in-out-circ`
  - `in-elastic`, `out-elastic`, `in-out-elastic`
  - `in-back`, `out-back`, `in-out-back`
  - `in-bounce`, `out-bounce`, `in-out-bounce`

**`tui_lerp(float $a, float $b, float $t): float`**
- Linear interpolation between two values
- Returns: `$a + ($b - $a) * $t`

**`tui_lerp_color(string|array $colorA, string|array $colorB, float $t): array`**
- Interpolates between two colors
- Accepts: '#rrggbb' or [r, g, b]
- Returns: [r, g, b] array

**`tui_color_from_hex(string $hex): array`**
- Converts hex color to RGB
- Format: '#rrggbb' or 'rrggbb'
- Returns: [r, g, b]

**`tui_gradient(array $colors, int $steps): array`**
- Generates gradient between colors
- `$colors`: array of hex strings or [r,g,b] arrays
- Returns: array of `$steps` colors

#### Table Rendering

**`tui_table_create(array $headers): mixed`**
- Creates table resource
- `$headers`: column names

**`tui_table_add_row(mixed $table, array $cells): void`**
- Adds row to table
- `$cells` count must match header count

**`tui_table_set_align(mixed $table, int $column, bool $rightAlign): void`**
- Sets column alignment

**`tui_table_render_to_buffer(mixed $table, mixed $buffer, int $x, int $y, string $borderStyle = 'single', array $headerStyle = [], array $cellStyle = []): int`**
- Renders table to buffer
- Returns height of rendered table

#### Progress & Spinners

**`tui_render_progress_bar(mixed $buffer, int $x, int $y, int $width, float $progress, string $filledChar = '█', string $emptyChar = '░', array $filledStyle = [], array $emptyStyle = []): void`**
- Renders progress bar
- `$progress`: 0.0 to 1.0

**`tui_render_busy_bar(mixed $buffer, int $x, int $y, int $width, int $frame, string $style = 'pulse', array $baseStyle = []): void`**
- Renders animated busy bar
- `$style`: 'pulse', 'snake', 'gradient', 'wave', 'shimmer', 'rainbow'

**`tui_spinner_frame(string $spinnerType, int $frame): string`**
- Gets spinner frame character
- Types: 'dots', 'line', 'circle', 'arrow', 'box', 'bounce', 'clock', 'moon', 'earth'
- Returns UTF-8 character

**`tui_spinner_frame_count(string $spinnerType): int`**
- Returns number of frames in spinner animation

**`tui_render_spinner(mixed $buffer, int $x, int $y, string $spinnerType, int $frame, array $style = []): void`**
- Renders spinner at position

#### Sprite API

**`tui_sprite_create(array $frames, string $name = 'default', bool $loop = true): mixed`**
- Creates sprite with animation frames
- `$frames`: array of frame definitions
- Returns sprite resource

**`tui_sprite_update(mixed $sprite, int $deltaMs): void`**
- Updates sprite animation state
- `$deltaMs`: milliseconds elapsed

**`tui_sprite_set_animation(mixed $sprite, string $name): void`**
- Switches to named animation

**`tui_sprite_set_position(mixed $sprite, int $x, int $y): void`**
- Sets sprite position

**`tui_sprite_flip(mixed $sprite, bool $flipped): void`**
- Horizontal flip

**`tui_sprite_set_visible(mixed $sprite, bool $visible): void`**
- Toggle visibility

**`tui_sprite_render(mixed $sprite, mixed $buffer): void`**
- Renders sprite to buffer

**`tui_sprite_get_bounds(mixed $sprite): array`**
- Returns `[x, y, width, height]`

**`tui_sprite_collides(mixed $spriteA, mixed $spriteB): bool`**
- AABB collision detection

---

## PHP Classes

### TuiBox

Container component with flexbox layout.

**Constructor:**
```php
new TuiBox(array $props = [])
```

**Methods:**
- `addChild(TuiBox|TuiText $child): self` - Fluent API

**Properties:**

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `flexDirection` | string | 'column' | 'row', 'row-reverse', 'column', 'column-reverse' |
| `alignItems` | string\|null | null | 'flex-start', 'flex-end', 'center', 'stretch', 'baseline' |
| `justifyContent` | string\|null | null | 'flex-start', 'flex-end', 'center', 'space-between', 'space-around', 'space-evenly' |
| `flexGrow` | int | 0 | Growth factor |
| `flexShrink` | int | 1 | Shrink factor |
| `flexBasis` | int\|string\|null | null | Basis size |
| `alignSelf` | string\|null | null | Override alignItems |
| `width` | int\|string\|null | null | Cells or percent (e.g., 20, '50%') |
| `height` | int\|string\|null | null | Cells or percent |
| `minWidth` | int\|string\|null | null | Minimum width |
| `minHeight` | int\|string\|null | null | Minimum height |
| `maxWidth` | int\|string\|null | null | Maximum width |
| `maxHeight` | int\|string\|null | null | Maximum height |
| `padding` | int | 0 | All sides |
| `paddingTop` | int | 0 | Top padding |
| `paddingBottom` | int | 0 | Bottom padding |
| `paddingLeft` | int | 0 | Left padding |
| `paddingRight` | int | 0 | Right padding |
| `paddingX` | int | 0 | Left + right |
| `paddingY` | int | 0 | Top + bottom |
| `margin` | int | 0 | All sides |
| `marginTop` | int | 0 | Top margin |
| `marginBottom` | int | 0 | Bottom margin |
| `marginLeft` | int | 0 | Left margin |
| `marginRight` | int | 0 | Right margin |
| `marginX` | int | 0 | Left + right |
| `marginY` | int | 0 | Top + bottom |
| `gap` | int | 0 | Gap between children |
| `columnGap` | int | 0 | Horizontal gap |
| `rowGap` | int | 0 | Vertical gap |
| `flexWrap` | string\|null | null | 'nowrap', 'wrap', 'wrap-reverse' |
| `overflow` | string | 'visible' | 'visible', 'hidden' |
| `overflowX` | string\|null | null | Horizontal overflow |
| `overflowY` | string\|null | null | Vertical overflow |
| `display` | string | 'flex' | 'flex', 'none' |
| `position` | string | 'relative' | 'relative', 'absolute' |
| `borderStyle` | string\|null | null | 'single', 'double', 'round', 'bold', 'dashed' |
| `borderColor` | string\|array\|null | null | '#rrggbb' or [r, g, b] |
| `focusable` | bool | false | Can receive focus |
| `focused` | bool | false | Currently focused |
| `children` | array | [] | Child TuiBox/TuiText elements |

### TuiText

Text display component with styling.

**Constructor:**
```php
new TuiText(string $content = '', array $props = [])
```

**Properties:**

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `content` | string | '' | Text to display |
| `color` | string\|array\|null | null | '#rrggbb' or [r, g, b] |
| `backgroundColor` | string\|array\|null | null | '#rrggbb' or [r, g, b] |
| `bold` | bool | false | Bold styling |
| `dim` | bool | false | Dim/faint styling |
| `italic` | bool | false | Italic styling |
| `underline` | bool | false | Underline styling |
| `inverse` | bool | false | Inverse (swap fg/bg) |
| `strikethrough` | bool | false | Strikethrough styling |
| `wrap` | string\|null | null | 'none', 'char', 'word', 'word-char' |

### TuiInstance

Represents running TUI application.

**Methods:**
- `rerender(): void` - Force re-render
- `unmount(): void` - Stop and cleanup
- `waitUntilExit(): void` - Block until exit
- `exit(int $code = 0): void` - Exit with code

### TuiKey

Keyboard event object (read-only, received from input handler).

**Properties:**

| Property | Type | Description |
|----------|------|-------------|
| `key` | string | Pressed character or control name |
| `name` | string | Key name (e.g., "ArrowUp") |
| `upArrow` | bool | Up arrow key |
| `downArrow` | bool | Down arrow key |
| `leftArrow` | bool | Left arrow key |
| `rightArrow` | bool | Right arrow key |
| `return` | bool | Enter/Return |
| `escape` | bool | Escape |
| `backspace` | bool | Backspace |
| `delete` | bool | Delete |
| `tab` | bool | Tab |
| `home` | bool | Home key |
| `end` | bool | End key |
| `pageUp` | bool | Page Up |
| `pageDown` | bool | Page Down |
| `functionKey` | int | 0 = not F-key, 1-12 = F1-F12 |
| `ctrl` | bool | Ctrl modifier |
| `alt` | bool | Alt/Meta modifier |
| `meta` | bool | Meta modifier |
| `shift` | bool | Shift modifier |

### TuiFocusEvent

Focus change event object.

**Properties:**

| Property | Type | Description |
|----------|------|-------------|
| `previous` | array\|null | Previous node info (null if first focus) |
| `current` | array\|null | New node info (null if no focusable element) |
| `direction` | string | 'next', 'prev', or 'programmatic' |

**Node Info Array:**
- `focusable` (bool)
- `focused` (bool)
- `x`, `y` (int) - Position
- `width`, `height` (int) - Size
- `type` (string) - 'box' or 'text'
- `content` (string) - Text content (for text nodes)

---

## Internal Data Structures

### tui_app

Application state management.

```c
typedef struct {
    /* Terminal state */
    int fullscreen;
    int exit_on_ctrl_c;
    int running;
    int should_exit;
    int exit_code;

    /* Layout dimensions */
    int width, height;

    /* Callbacks (PHP) */
    zend_fcall_info component_fci;
    zend_fcall_info_cache component_fcc;

    zend_fcall_info input_fci;
    zend_fcall_info_cache input_fcc;
    int has_input_handler;

    zend_fcall_info focus_fci;
    zend_fcall_info_cache focus_fcc;
    int has_focus_handler;

    zend_fcall_info resize_fci;
    zend_fcall_info_cache resize_fcc;
    int has_resize_handler;

    zend_fcall_info tick_fci;
    zend_fcall_info_cache tick_fcc;
    int has_tick_handler;

    /* Virtual DOM */
    tui_node *root_node;
    tui_node *focused_node;

    /* Rendering */
    tui_buffer *buffer;
    tui_output *output;
    tui_loop *loop;

    /* Render throttling */
    int render_pending;
    int min_render_interval_ms;  /* 16ms = 60fps */

    /* Timers */
    #define TUI_MAX_TIMERS 32
    struct {
        int id;
        zend_fcall_info fci;
        zend_fcall_info_cache fcc;
        int active;
    } timer_callbacks[TUI_MAX_TIMERS];
    int timer_callback_count;

    int destroyed;  /* Prevent double-free */
} tui_app;
```

### tui_node

Virtual DOM node.

```c
typedef struct tui_node {
    tui_node_type type;  /* BOX or TEXT */
    char *key;           /* Node identity for reconciler */
    tui_style style;     /* Colors, bold, etc. */

    /* For text nodes */
    char *text;
    tui_wrap_mode wrap_mode;

    /* For box nodes with borders */
    tui_border_style border_style;
    tui_color border_color;

    /* Focus management */
    int focusable;
    int focused;

    /* Yoga layout node */
    YGNodeRef yoga_node;

    /* Tree structure */
    struct tui_node *parent;
    struct tui_node **children;
    int child_count;
    int child_capacity;

    /* Computed layout (from Yoga) */
    float x, y, width, height;
} tui_node;
```

### tui_buffer

Character cell buffer.

```c
typedef struct {
    uint32_t codepoint;  /* Unicode codepoint */
    tui_style style;     /* Colors and attributes */
    uint8_t dirty;       /* Needs redraw */
} tui_cell;

typedef struct {
    tui_cell *cells;
    int width, height;
} tui_buffer;
```

### tui_style

Text styling.

```c
typedef struct {
    tui_color fg;        /* Foreground */
    tui_color bg;        /* Background */
    uint8_t bold;
    uint8_t dim;
    uint8_t italic;
    uint8_t underline;
    uint8_t inverse;
    uint8_t strikethrough;
} tui_style;

typedef struct {
    uint8_t r, g, b;
    uint8_t is_set;
} tui_color;
```

### tui_canvas

High-resolution drawing canvas.

```c
typedef enum {
    TUI_CANVAS_BRAILLE,  /* 2x4 resolution per cell */
    TUI_CANVAS_BLOCK,    /* 2x2 resolution */
    TUI_CANVAS_ASCII     /* 1x1 resolution */
} tui_canvas_mode;

typedef struct {
    uint8_t *pixels;          /* Bit array */
    int pixel_width;
    int pixel_height;
    int char_width;
    int char_height;
    tui_canvas_mode mode;
    tui_color color;          /* Drawing color */
} tui_canvas;
```

### tui_sprite

Animated sprite.

```c
typedef struct {
    char **lines;           /* Frame lines */
    int width, height;
    tui_color color;
    int duration;           /* Milliseconds */
} tui_sprite_frame;

typedef struct {
    char *name;
    tui_sprite_frame *frames;
    int frame_count;
    int loop;
} tui_sprite_animation;

typedef struct {
    tui_sprite_animation *animations;
    int animation_count;
    int current_animation;
    int current_frame;
    int frame_timer;
    int x, y;               /* Position */
    int visible;
    int flipped;            /* Horizontal flip */
    tui_color default_color;
} tui_sprite;
```

### tui_table

Table data structure.

```c
typedef struct {
    char **headers;
    int header_count;
    char ***rows;           /* Array of row arrays */
    int row_count;
    int *column_widths;     /* Calculated widths */
    int *column_align_right;
} tui_table;
```

### tui_key_event

Keyboard input event.

```c
typedef struct {
    char key[8];            /* UTF-8 character */
    bool ctrl, meta, shift;
    bool upArrow, downArrow, leftArrow, rightArrow;
    bool enter, escape, backspace, delete, tab;
    bool home, end, pageUp, pageDown;
    int functionKey;        /* 0 = not F-key, 1-12 = F1-F12 */
} tui_key_event;
```

---

## Core Subsystems

### 1. Event Loop (src/event/loop.h)

Poll-based, non-blocking event loop.

**Features:**
- `poll()` on stdin for input (configurable timeout)
- Signal handler for SIGWINCH (resize)
- Timer management
- Callback dispatch

**Key Functions:**
- `tui_loop_create()` / `tui_loop_destroy()`
- `tui_loop_run()` / `tui_loop_stop()`
- `tui_loop_on_input()` / `tui_loop_on_resize()` / `tui_loop_on_tick()`
- `tui_loop_add_timer()` / `tui_loop_remove_timer()`

### 2. Input Parser (src/event/input.h)

Parses terminal escape sequences.

**Handles:**
- Single characters (a-z, 0-9, symbols)
- Control characters (Ctrl+A through Ctrl+Z)
- Arrow keys (up, down, left, right)
- Special keys (Home, End, Page Up/Down, F1-F12)
- Modifiers (Shift, Ctrl, Alt/Meta)
- UTF-8 multi-byte sequences

**Function:**
- `tui_input_parse(const char *buf, int len, tui_key_event *event): int` - Returns bytes consumed

### 3. Virtual DOM & Layout (src/node/)

**Node Operations:**
- `tui_node_create_box()` / `tui_node_create_text()`
- `tui_node_destroy()`
- `tui_node_append_child()` / `tui_node_remove_child()`
- `tui_node_set_style()`

**Yoga Integration:**
- Each node has `YGNodeRef yoga_node`
- Yoga calculates layout from flex properties
- Results copied to `node->x`, `node->y`, `node->width`, `node->height`

**Reconciler (src/node/reconciler.h):**
- Diffs old and new trees
- Reuses nodes when possible
- Minimal updates to DOM

### 4. Rendering Pipeline (src/render/)

**Buffer (buffer.h):**
- Double-buffered character grid
- Each cell: codepoint + style + dirty flag
- Functions: `tui_buffer_set_cell()`, `tui_buffer_write_text()`, `tui_buffer_fill_rect()`

**Output (output.h):**
- Generates ANSI escape sequences
- Cursor movement (`\x1b[y;xH`)
- Color codes (`\x1b[38;2;r;g;bm` for RGB)
- Attributes (bold, dim, italic, underline, etc.)
- Only writes dirty cells (optimization)

### 5. Terminal Control (src/terminal/)

**Raw Mode (terminal.h):**
```c
void tui_terminal_enable_raw_mode()
- Disables canonical mode (ICANON)
- Disables echo (ECHO)
- Disables signals (ISIG)
- Sets non-blocking input (VMIN=0, VTIME=0)

void tui_terminal_disable_raw_mode()
- Restores original termios
```

**ANSI Codes (ansi.h):**
- Safe buffer-based API
- Cursor movement, clear screen
- Colors (256-color, true color)
- Text attributes

### 6. Text Processing (src/text/)

**Width Measurement (measure.h):**
- `tui_char_width(uint32_t codepoint): int`
  - Returns 0 for control/combining
  - Returns 1 for most characters
  - Returns 2 for CJK, emoji
- `tui_string_width(const char *str): int`
- UTF-8 encoding/decoding

**Text Wrapping (wrap.h):**
- `tui_wrap_text(const char *text, int width, tui_wrap_mode mode)`
- Modes: NONE, CHAR, WORD, WORD_CHAR
- Returns array of wrapped lines

### 7. Drawing & Animation (src/drawing/)

**Primitives (primitives.h):**
- Bresenham's line algorithm
- Midpoint circle algorithm
- Scanline triangle fill
- Rectangle, ellipse drawing

**Canvas (canvas.h):**
- Braille (2x4 dots per cell): U+2800-U+28FF
- Block (2x2 dots): U+2590, U+2595, etc.
- High-resolution pixel drawing

**Animation (animation.h):**
- 28 easing functions (linear, quad, cubic, elastic, bounce, etc.)
- Linear interpolation (lerp)
- Color interpolation with RGB

**Progress/Spinners (progress.h):**
- Progress bars with gradient support
- Busy bars (pulse, snake, gradient, wave, shimmer, rainbow)
- Spinners: dots, line, circle, arrow, box, bounce, clock, moon, earth

**Sprites (sprite.h):**
- Multi-animation sprites
- Frame timing in milliseconds
- Visibility and flip support
- AABB collision detection
- Position management

**Tables (table.h):**
- Column width calculation
- Header/cell styling
- Border styles
- Alignment per column

### 8. Yoga Layout Engine (src/yoga/)

Vendored Facebook Yoga library for flexbox.

**Key Components:**
- `YGNode` - Layout node
- `YGNodeStyle` - Flex properties
- `YGNodeLayout` - Computed layout
- `YGNodeCalculateLayout()` - Main layout algorithm
- Enums: Direction, FlexDirection, Align, Justify, Display, Overflow, etc.

---

## Rendering Flow

### Initial Render

1. **Mount Component**
   - `tui_render($component, $options)` called
   - Creates `tui_app` instance
   - Stores component callback

2. **Call Component**
   - Invokes `$component()`
   - Returns PHP object tree (TuiBox/TuiText)

3. **Convert to Nodes**
   - `php_to_tui_node()` recursively converts
   - Creates `tui_node` C structures
   - Reads PHP properties into C structs
   - Builds child relationships

4. **Calculate Layout**
   - `tui_node_calculate_layout(root, width, height)`
   - Yoga calculates flexbox layout
   - Updates `node->x`, `node->y`, `node->width`, `node->height`

5. **Render to Buffer**
   - `render_node_to_buffer()` recursively renders
   - Writes characters to `tui_buffer`
   - Applies styles from `tui_style`
   - Marks cells as dirty

6. **Output to Terminal**
   - `tui_output_render()` generates ANSI codes
   - Only outputs changed cells
   - Flushes to stdout

7. **Start Event Loop**
   - `tui_loop_run()` starts
   - Enters `tui_wait_until_exit()`
   - Waits for input/resize/timers

### Re-render

1. Input handler calls `tui_rerender($instance)`
2. Steps 2-6 repeat
3. Old tree compared with new
4. Yoga recalculates only affected nodes
5. Only changed cells sent to terminal

### Event Loop

```c
while (running) {
    struct pollfd fds[1] = {{STDIN_FILENO, POLLIN, 0}};
    int ret = poll(fds, 1, timeout_ms);

    if (ret > 0 && (fds[0].revents & POLLIN)) {
        char buf[64];
        int n = read(STDIN_FILENO, buf, sizeof(buf));
        tui_input_parse(buf, n, &key_event);
        input_callback(&key_event);  // PHP handler
    }

    if (resize_pending) {
        resize_callback(new_w, new_h);
    }

    if (tick_handler) {
        tick_callback();
    }

    // Update timers
    // Re-render if pending
}
```

---

## Memory Management

### Reference Counting

PHP callbacks properly managed:
```c
// When setting callback
Z_TRY_ADDREF(fci.function_name);

// When replacing/destroying
zval_ptr_dtor(&fci.function_name);
```

### Resource Cleanup

TuiInstance has custom destructor:
```c
static void tui_instance_free_object(zend_object *obj) {
    tui_instance_object *intern = tui_instance_from_obj(obj);
    if (intern->app) {
        tui_app_stop(intern->app);      // Restore terminal
        tui_app_destroy(intern->app);    // Free all resources
    }
    zend_object_std_dtor(&intern->std);
}
```

### Node Tree Cleanup

Recursive destruction with Yoga cleanup:
```c
void tui_node_destroy(tui_node *node) {
    if (!node) return;
    for (int i = 0; i < node->child_count; i++) {
        tui_node_destroy(node->children[i]);
    }
    YGNodeFree(node->yoga_node);  // Important!
    free(node->text);
    free(node->key);
    free(node->children);
    free(node);
}
```

---

## Limitations & Constraints

1. **Single App Per Process** - Only one TUI can run at a time (terminal state is global)
2. **Not Thread-Safe** - Use separate processes for concurrent TUIs
3. **Terminal Only** - Requires TTY (no GUI, no file redirection)
4. **No Clipboard** - Cannot access system clipboard
5. **No Mouse** - Keyboard input only
6. **ANSI Only** - Requires terminal supporting ANSI escape codes
7. **Raw Mode** - Disables Ctrl+S/Q, custom Ctrl+C handling

---

## Build System

**config.m4:**
```m4
PHP_ARG_ENABLE(tui, whether to enable tui support)

if test "$PHP_TUI" != "no"; then
    PHP_REQUIRE_CXX()
    PHP_ADD_LIBRARY(stdc++, 1, TUI_SHARED_LIBADD)

    PHP_NEW_EXTENSION(tui, [
        tui.c
        src/app/app.c
        src/event/loop.c
        src/event/input.c
        src/node/node.c
        src/node/reconciler.c
        src/render/buffer.c
        src/render/output.c
        src/terminal/terminal.c
        src/terminal/ansi.c
        src/text/measure.c
        src/text/wrap.c
        src/drawing/primitives.c
        src/drawing/canvas.c
        src/drawing/animation.c
        src/drawing/table.c
        src/drawing/progress.c
        src/drawing/sprite.c
        src/yoga/*.cpp
    ], $ext_shared,, [-I$ext_srcdir/src])
fi
```

**Compilation:**
```bash
phpize
./configure --enable-tui
make
make test
```

---

## Resource Types

| Type | Resource Name | Destructor |
|------|---------------|-----------|
| Canvas | `TuiCanvas` | `tui_canvas_dtor` |
| Table | `TuiTable` | `tui_table_dtor` |
| Sprite | `TuiSprite` | `tui_sprite_dtor` |
| Buffer | `TuiBuffer` | `tui_buffer_dtor` |

---

## Color Support

**Formats:**
- Hex: `'#ff0000'` or `'ff0000'`
- RGB Array: `[255, 0, 0]`
- 256-color supported
- True color (24-bit RGB) supported
- Fallback to basic colors if not supported

**ANSI Generation:**
```
\x1b[38;2;r;g;bm    (foreground)
\x1b[48;2;r;g;bm    (background)
```

---

## Performance Characteristics

- **Layout:** O(n) Yoga flexbox algorithm
- **Rendering:** O(cells) only dirty cells drawn
- **Input:** O(1) event dispatch
- **Memory:** ~1KB per node + buffer (width × height)
- **Frame Rate:** 60fps max (16ms throttle)

---

## Usage Examples

### Basic Application

```php
<?php

$counter = 0;

$instance = tui_render(function() use (&$counter) {
    return new TuiBox([
        'flexDirection' => 'column',
        'padding' => 1,
        'children' => [
            new TuiText("Counter: $counter", ['bold' => true]),
            new TuiText("Press q to quit, +/- to change", ['dim' => true]),
        ],
    ]);
});

tui_set_input_handler($instance, function(TuiKey $key) use ($instance, &$counter) {
    if ($key->key === 'q') {
        $instance->exit();
    } elseif ($key->key === '+') {
        $counter++;
        tui_rerender($instance);
    } elseif ($key->key === '-') {
        $counter--;
        tui_rerender($instance);
    }
});

tui_wait_until_exit($instance);
```

### With Timer Animation

```php
<?php

$frame = 0;

$instance = tui_render(function() use (&$frame) {
    $spinner = tui_spinner_frame('dots', $frame);
    return new TuiBox([
        'children' => [
            new TuiText("$spinner Loading...", ['color' => '#00ff00']),
        ],
    ]);
});

$timerId = tui_add_timer($instance, 100, function() use ($instance, &$frame) {
    $frame++;
    tui_rerender($instance);
});

tui_wait_until_exit($instance);
```

### Focus Navigation

```php
<?php

$instance = tui_render(function() {
    return new TuiBox([
        'flexDirection' => 'column',
        'children' => [
            new TuiBox(['focusable' => true, 'borderStyle' => 'single', 'children' => [
                new TuiText('Option 1'),
            ]]),
            new TuiBox(['focusable' => true, 'borderStyle' => 'single', 'children' => [
                new TuiText('Option 2'),
            ]]),
            new TuiBox(['focusable' => true, 'borderStyle' => 'single', 'children' => [
                new TuiText('Option 3'),
            ]]),
        ],
    ]);
});

tui_set_input_handler($instance, function(TuiKey $key) use ($instance) {
    if ($key->tab || $key->downArrow) {
        tui_focus_next($instance);
        tui_rerender($instance);
    } elseif ($key->upArrow) {
        tui_focus_prev($instance);
        tui_rerender($instance);
    }
});

tui_set_focus_handler($instance, function(TuiFocusEvent $event) {
    // Handle focus changes
});

tui_wait_until_exit($instance);
```

---

## Conclusion

ext-tui provides a complete, high-performance terminal UI framework comparable to modern web UI systems. Its Yoga integration, virtual DOM reconciliation, and efficient rendering make it suitable for complex terminal applications including games, dashboards, forms, and interactive tools.

The extension maintains low-level C performance while exposing a high-level PHP API, making it accessible to PHP developers while enabling advanced terminal capabilities.
