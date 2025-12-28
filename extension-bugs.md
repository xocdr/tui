# ext-tui Extension Bugs

This file tracks bugs discovered in the ext-tui C extension.

---

## Fixed Bugs (2024-12-28)

### tui_string_width - ANSI Escape Sequences ✓ FIXED

**Issue:** ANSI escape sequences (like `\033[31m` for color) were counted as characters.

**Fix:** Added `skip_ansi_sequence()` function in `src/text/measure.c` that properly skips:
- CSI sequences (`ESC [` ... letter)
- OSC sequences (`ESC ]` ... `BEL` or `ST`)
- Single-character escapes

**Verification:**
```php
$ansi = "\033[31mRed\033[0m";
$width = tui_string_width($ansi);
// Now correctly returns: 3
```

---

### tui_wrap_text - Long Word Breaking ✓ FIXED

**Issue:** Words longer than wrap width were not broken.

**Fix:** Changed wrap mode from `TUI_WRAP_WORD` to `TUI_WRAP_WORD_CHAR` in `tui.c:932`. This prefers word breaks but falls back to character breaks for long words.

**Verification:**
```php
$lines = tui_wrap_text('supercalifragilisticexpialidocious', 10);
// Now returns 4 lines, each <= 10 characters
```

---

### tui_gradient - Single Stop Handling ✓ FIXED

**Issue:** PHP function returned empty array for single-color gradients.

**Fix:** Changed condition in `tui.c:2241` from `color_count < 2` to `color_count < 1`. The C function already handles single-color case correctly.

**Verification:**
```php
$colors = tui_gradient(['#ff0000'], 5);
// Now returns: ['#ff0000', '#ff0000', '#ff0000', '#ff0000', '#ff0000']
```

---

## Not Bugs (Clarifications)

### tui_string_width - CJK Width

**Status:** Working correctly. The code at `src/text/measure.c:46-54` correctly identifies CJK ranges and returns width 2.

```php
$width = tui_string_width('Hello 世界');
// Returns: 10 (6 + 2 + 2) - correct!
// Note: "Hello " is 6 chars, each CJK character is 2 cells
```

---

### tui_canvas_get_resolution

**Status:** Working correctly. Returns `[width => int, height => int, char_width => int, char_height => int]`. The C function handles null canvas by setting width/height to 0, and the PHP binding always returns an array.

---

### tui_sprite_render

**Status:** Designed behavior. Requires 2 arguments: `(resource $buffer, resource $sprite)`. You need a buffer to render the sprite to - this is intentional.

---

### tui_draw_* Functions

**Status:** Documentation issue, not a bug. The API expects:
- `tui_draw_line($buffer, $x1, $y1, $x2, $y2, $char, $style)`
- Last parameter is a style array `['fg' => [r,g,b], ...]`

The PHP wrapper classes handle this correctly.

---

## Notes

All critical bugs have been fixed. The extension is now working as expected.
