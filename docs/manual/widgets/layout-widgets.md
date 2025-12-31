# Layout Widgets

Widgets for layout and structural organization.

## Scrollable

Scrollable content container with keyboard navigation.

[[TODO:SCREENSHOT:scrollable container with scrollbar]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Layout\Scrollable;

Scrollable::create($contentItems)
    ->height(10);
```

### With Content Array

```php
Scrollable::create([
    Text::create('Line 1'),
    Text::create('Line 2'),
    Text::create('Line 3'),
    // ... many more lines
])
->height(5);
```

### Smooth Scrolling

```php
Scrollable::create($content)
    ->height(10)
    ->smoothScroll(true);
```

### Custom Scrollbar

```php
Scrollable::create($content)
    ->showScrollbar(true)
    ->scrollbarChar('█')
    ->trackChar('░');
```

### Scroll Indicators

```php
Scrollable::create($content)
    ->indicator('arrows');  // Shows ↑/↓ more content indicators
```

### Sticky Headers/Footers

```php
Scrollable::create($content)
    ->stickyTop(Text::create('Header')->bold())
    ->stickyBottom(Text::create('Footer')->dim());
```

### Configuration Options

| Method | Description |
|--------|-------------|
| `height($int)` | Viewport height |
| `width($int)` | Container width |
| `showScrollbar($bool)` | Show scrollbar |
| `scrollbarChar($char)` | Scrollbar thumb character |
| `trackChar($char)` | Scrollbar track character |
| `indicator('bar'\|'arrows')` | Scroll indicator style |
| `smoothScroll($bool)` | Enable smooth scrolling |
| `stickyTop($component)` | Fixed header |
| `stickyBottom($component)` | Fixed footer |

### Keyboard Navigation

| Key | Action |
|-----|--------|
| `↑` | Scroll up one line |
| `↓` | Scroll down one line |
| `Page Up` | Scroll up one page |
| `Page Down` | Scroll down one page |
| `Home` | Scroll to top |
| `End` | Scroll to bottom |

---

## Collapsible

Collapsible/expandable sections.

[[TODO:SCREENSHOT:collapsible in expanded and collapsed states]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Layout\Collapsible;

Collapsible::create()
    ->header('Section Title')
    ->content($sectionContent);
```

### Default Expanded

```php
Collapsible::create()
    ->header('Details')
    ->content($details)
    ->defaultExpanded(true);
```

### Controlled State

```php
Collapsible::create()
    ->header('Advanced Options')
    ->content($options)
    ->expanded($isExpanded)
    ->onToggle(fn($expanded) => setExpanded($expanded));
```

### Custom Icons

```php
Collapsible::create()
    ->header('More Info')
    ->content($info)
    ->expandedIcon('▼')
    ->collapsedIcon('▶');
```

### Styling

```php
Collapsible::create()
    ->header('Styled Section')
    ->content($content)
    ->headerStyle(['bold' => true, 'color' => 'cyan'])
    ->focusedHeaderStyle(['bold' => true, 'color' => 'yellow'])
    ->contentIndent(4);
```

### Focus State

```php
Collapsible::create()
    ->header('Focusable')
    ->content($content)
    ->isFocused($hasFocus)
    ->onFocus(fn() => handleFocus())
    ->onBlur(fn() => handleBlur());
```

### Configuration Options

| Method | Description |
|--------|-------------|
| `header($str)` | Header text |
| `content($mixed)` | Collapsible content |
| `expanded($bool)` | Controlled expand state |
| `defaultExpanded($bool)` | Initial expand state |
| `expandedIcon($str)` | Icon when expanded |
| `collapsedIcon($str)` | Icon when collapsed |
| `headerStyle($array)` | Header text style |
| `focusedHeaderStyle($array)` | Header style when focused |
| `contentIndent($int)` | Content left indentation |
| `isFocused($bool)` | Focus state |
| `onToggle($fn)` | Toggle callback |

### Keyboard Controls

When focused:
- `Space` or `Enter` - Toggle expand/collapse
- `←` - Collapse
- `→` - Expand

---

## Divider

Section separator lines.

[[TODO:SCREENSHOT:divider with different styles]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Layout\Divider;

Divider::create();
```

### With Title

```php
Divider::create()
    ->title('Section Title')
    ->titleAlign('center');  // 'left', 'center', 'right'
```

### Divider Styles

```php
use Xocdr\Tui\Widgets\Layout\DividerStyle;

Divider::create()->style(DividerStyle::SINGLE);   // ─
Divider::create()->style(DividerStyle::DOUBLE);   // ═
Divider::create()->style(DividerStyle::DASHED);   // - - -
Divider::create()->style(DividerStyle::DOTTED);   // · · ·
```

### Custom Character

```php
Divider::create()->character('=');
Divider::create()->character('*');
```

### Vertical Divider

```php
Divider::create()
    ->vertical()
    ->height(5);
```

### With Color

```php
Divider::create()
    ->title('Important')
    ->color('cyan');

Divider::create()->color('dim');  // Dimmed divider
```

### Fixed Width

```php
Divider::create()->width(40);
```

### Configuration Options

| Method | Description |
|--------|-------------|
| `title($str)` | Title text in divider |
| `titleAlign($str)` | 'left', 'center', 'right' |
| `style($style)` | Divider style enum |
| `character($char)` | Custom character |
| `color($color)` | Line color |
| `width($int)` | Fixed width |
| `vertical()` | Vertical orientation |
| `height($int)` | Height for vertical divider |

---

## Section

Titled content sections with optional borders.

[[TODO:SCREENSHOT:section with title and content]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Layout\Section;

Section::create()
    ->title('Configuration')
    ->content($configContent);
```

### With Level

```php
use Xocdr\Tui\Widgets\Layout\SectionLevel;

Section::create()
    ->title('Main Title')
    ->level(SectionLevel::H1)
    ->content($content);

Section::create()
    ->title('Subsection')
    ->level(SectionLevel::H2)
    ->content($content);
```

### With Description

```php
Section::create()
    ->title('Settings')
    ->description('Configure your preferences')
    ->content($settings);
```

### Bordered Section

```php
Section::create()
    ->title('Panel')
    ->border(true)
    ->borderStyle('round')
    ->content($panelContent);
```

### Collapsible Section

```php
Section::create()
    ->title('Advanced Options')
    ->collapsible(true)
    ->defaultCollapsed(true)
    ->content($advancedOptions);
```

---

## Stack

Layered content with z-index support.

[[TODO:SCREENSHOT:stack showing layered components]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Layout\Stack;

Stack::create([
    $backgroundLayer,
    $contentLayer,
    $overlayLayer,
]);
```

### With Alignment

```php
Stack::create($layers)
    ->alignItems('center')
    ->justifyContent('center');
```

Note: Stack implementation varies. Check actual widget for available methods.


---

## See Also

- [Layout API Reference](../../reference/widgets/scrollable.md) - Scrollable widget API
- [Divider Reference](../../reference/widgets/divider.md) - Divider widget API
- [Collapsible Reference](../../reference/widgets/collapsible.md) - Collapsible widget API
- [Box Component](../components.md) - Flexbox layout container
- [Widget Manual](index.md) - Widget overview
