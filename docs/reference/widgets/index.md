# Widget Reference

Complete API reference documentation for TUI widgets.

## Key Features

### VirtualList

Widgets with large lists use VirtualList for efficient windowed rendering. Only visible items are rendered, providing constant-time performance regardless of list size.

**Widgets using VirtualList**: SelectList, Autocomplete, MultiSelect, ItemList, Tree

### SmoothScroller

Spring physics-based smooth scrolling animations for fluid navigation.

**Widgets using SmoothScroller**: Scrollable, SelectList, Autocomplete, MultiSelect

### Color API

Unified color API supporting:
- Named colors: `'cyan'`, `'red'`, `'green'`
- Tailwind palettes: `('blue', 500)`, `('gray', 100)`
- Hex colors: `'#ff5500'`

All widgets accept colors via `->color()` and `->bgColor()` methods.

### Terminal Cursor Control

Input widget supports terminal cursor shape control via TerminalManager:
- Block cursor (default)
- Underline cursor
- Bar/beam cursor

---

## Input Widgets

| Widget | Description |
|--------|-------------|
| [Input](input.md) | Single-line text input with validation |
| [SelectList](selectlist.md) | Single-select dropdown list |
| [MultiSelect](multiselect.md) | Multi-select checkbox list |
| [Autocomplete](autocomplete.md) | Input with suggestions |
| [ConfirmInput](confirminput.md) | Yes/No confirmation prompt |
| [QuickSearch](quicksearch.md) | Fuzzy search with results |
| [OptionPrompt](optionprompt.md) | Multiple choice prompt |
| [Form](form.md) | Multi-field form container |

## Display Widgets

| Widget | Description |
|--------|-------------|
| [TodoList](todolist.md) | Task list with status indicators |
| [StatusBar](statusbar.md) | Bottom status bar with segments |
| [Tabs](tabs.md) | Tabbed navigation |
| [Checklist](checklist.md) | Checkbox item list |
| [Tree](tree.md) | Hierarchical tree view |
| [ItemList](itemlist.md) | Bulleted/numbered list |
| [Breadcrumb](breadcrumb.md) | Navigation breadcrumb trail |

## Layout Widgets

| Widget | Description |
|--------|-------------|
| [Divider](divider.md) | Horizontal line separator |
| [Section](section.md) | Titled content section |
| [Scrollable](scrollable.md) | Scrollable content container |
| [Collapsible](collapsible.md) | Expandable/collapsible section |

## Content Widgets

| Widget | Description |
|--------|-------------|
| [Paragraph](paragraph.md) | Wrapped text paragraph |
| [ContentBlock](contentblock.md) | Titled content block |
| [OutputBlock](outputblock.md) | Code/output display with syntax highlighting |
| [Markdown](markdown.md) | Markdown renderer |
| [Diff](diff.md) | Unified diff viewer |
| [Link](link.md) | Clickable hyperlink |

## Streaming Widgets

| Widget | Description |
|--------|-------------|
| [StreamingText](streamingtext.md) | Character-by-character text display |
| [ThinkingBlock](thinkingblock.md) | AI thinking/reasoning display |
| [ConversationThread](conversationthread.md) | Chat message thread |

## Visual Widgets

| Widget | Description |
|--------|-------------|
| [BigText](bigtext.md) | Large ASCII art text |
| [Image](image.md) | Terminal image display |
| [Shape](shape.md) | Geometric shapes |

## Feedback Widgets

| Widget | Description |
|--------|-------------|
| [Badge](badge.md) | Status badge/tag |
| [Alert](alert.md) | Bordered notification box |
| [KeyHint](keyhint.md) | Keyboard shortcut hint |
| [Toast](toast.md) | Temporary notification |
| [LoadingState](loadingstate.md) | Loading/success/error indicator |
| [Interruptible](interruptible.md) | Cancellable operation indicator |
| [Meter](meter.md) | Progress/usage meter bar |
| [ErrorBoundary](errorboundary.md) | Error catching wrapper |

## Modal Widgets

| Widget | Description |
|--------|-------------|
| [Modal](modal.md) | Abstract base class for modals |
| [PermissionDialog](permissiondialog.md) | Permission request dialog |

## Support

| Item | Description |
|------|-------------|
| [Icon](icon.md) | Icon rendering utility |
| [FuzzyMatcher](fuzzymatcher.md) | Fuzzy string matching |
| [Enums](enums.md) | Type-safe configuration enums |
| [Constants](constants.md) | Widget constants |
| [Contracts](contracts.md) | Widget capability interfaces |

---

## Core Components

The following components are part of the core TUI package. See the [Classes Reference](../classes.md) for full documentation.

### Base Components

| Component | Description |
|-----------|-------------|
| `Box` | Flexbox layout container |
| `Text` | Styled text display |
| `Fragment` | Invisible grouping container |
| `Newline` | Line break |
| `Spacer` | Flexible space filler |
| `Transform` | Output transformation wrapper |
| `StaticOutput` | Non-rerendering static content |

### Core Widgets

| Widget | Description |
|--------|-------------|
| `Spinner` | Animated loading spinner |
| `ProgressBar` | Determinate progress indicator |
| `BusyBar` | Indeterminate loading bar |
| `Table` | Tabular data display |
| `DebugPanel` | Live performance metrics panel |

### Testing Utilities

| Utility | Description |
|---------|-------------|
| `MockInstance` | Mock application for unit testing |
| `TestRenderer` | Render components to string |
| `TuiAssertions` | PHPUnit assertion trait |

### Style Utilities

| Utility | Description |
|---------|-------------|
| `Style` | Fluent style builder |
| `Color` | Color utilities with Tailwind palettes |
| `Border` | Border style constants |

---

## See Also

- [Classes Reference](../classes.md) - Core classes and APIs
- [Hooks Reference](../hooks.md) - State management hooks
- [Widget Manual](../../manual/widgets.md) - Widget tutorials and guides
