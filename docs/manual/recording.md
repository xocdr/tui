# Screen Recording

TUI provides terminal session recording through the `Recorder` class, enabling export to asciicast v2 format compatible with asciinema.

## Overview

The `Support\Recording\Recorder` class allows you to:
- Record terminal sessions frame by frame
- Export to asciicast v2 JSON format
- Save recordings as `.cast` files
- Play back recordings with asciinema

## Basic Usage

```php
use Xocdr\Tui\Support\Recording\Recorder;

// Create recorder
$recorder = new Recorder(80, 24, 'My Demo');

// Start recording
$recorder->start();

// Capture frames
$recorder->capture("Hello, World!\n");
$recorder->capture("More output here...\n");

// Stop and save
$recorder->stop();
$recorder->save('demo.cast');
```

## Creating a Recorder

```php
// With defaults (80x24)
$recorder = new Recorder();

// With custom dimensions
$recorder = new Recorder(120, 40);

// With title for metadata
$recorder = new Recorder(80, 24, 'Widget Demo Recording');
```

## Recording Lifecycle

### Starting a Recording

```php
$recorder->start();

// Check status
if ($recorder->isRecording()) {
    echo "Recording in progress...";
}
```

### Capturing Frames

Each captured frame represents terminal output at a point in time:

```php
// Capture text output
$recorder->capture("Processing item 1...\n");
$recorder->capture("Processing item 2...\n");

// Capture with ANSI codes
$recorder->capture("\033[32mSuccess!\033[0m\n");
```

### Pausing and Resuming

```php
$recorder->pause();

// Recording paused - no frames captured
if ($recorder->isPaused()) {
    echo "Paused";
}

$recorder->resume();
// Recording resumed
```

### Stopping

```php
$recorder->stop();

if ($recorder->isStopped()) {
    // Ready to export
}
```

## Exporting Recordings

### As JSON String

```php
$json = $recorder->export();

if ($json !== null) {
    // Process the asciicast JSON
    $data = json_decode($json, true);
}
```

### To File

```php
// Save as .cast file
if ($recorder->save('demo.cast')) {
    echo "Recording saved!";
}
```

## Recording Information

```php
// Get recording dimensions
$dims = $recorder->getDimensions();
echo "Size: {$dims['width']}x{$dims['height']}";

// Get recording title
$title = $recorder->getTitle();

// Get duration in seconds
$duration = $recorder->getDuration();
echo "Duration: {$duration}s";

// Get frame count
$frames = $recorder->getFrameCount();
echo "Frames: {$frames}";
```

## State Management

The recorder has four states:

| State | `isRecording()` | `isPaused()` | `isStopped()` |
|-------|----------------|--------------|---------------|
| Idle | false | false | false |
| Recording | true | false | false |
| Paused | false | true | false |
| Stopped | false | false | true |

## Asciicast v2 Format

The recorder exports in asciicast v2 format:

```json
{"version": 2, "width": 80, "height": 24, "title": "Demo"}
[0.0, "o", "Hello, World!\n"]
[0.5, "o", "Processing...\n"]
[1.2, "o", "Done!\n"]
```

Each line after the header is `[time, type, data]`:
- `time` - Seconds since recording start
- `type` - Event type (`"o"` for output)
- `data` - Terminal output data

## Interactive Recording Example

```php
use Xocdr\Tui\Support\Recording\Recorder;
use function Xocdr\Tui\Hooks\useState;
use function Xocdr\Tui\Hooks\useInput;
use function Xocdr\Tui\Hooks\useRef;

$component = function () {
    [$isRecording, $setIsRecording] = useState(false);
    $recorderRef = useRef(null);

    useInput(function ($key) use ($isRecording, $setIsRecording, $recorderRef) {
        if ($key === 'r' && !$isRecording) {
            // Start recording
            $recorder = new Recorder(80, 24, 'Interactive Demo');
            $recorder->start();
            $recorderRef->current = $recorder;
            $setIsRecording(true);
        }

        if ($key === 's' && $isRecording) {
            // Stop and save
            $recorder = $recorderRef->current;
            $recorder->stop();
            $recorder->save('recording-' . time() . '.cast');
            $recorderRef->current = null;
            $setIsRecording(false);
        }
    });

    // Capture each render frame
    if ($isRecording && $recorderRef->current) {
        $recorderRef->current->capture($currentOutput);
    }

    return Box::create()->children([
        Text::create($isRecording ? '[REC]' : 'Ready'),
    ]);
};
```

## Playback

Recordings can be played back using asciinema:

```bash
# Install asciinema
brew install asciinema  # macOS
pip install asciinema   # Python

# Play recording
asciinema play demo.cast

# Upload to asciinema.org
asciinema upload demo.cast
```

## Resource Management

Always clean up the recorder when done:

```php
// Explicit cleanup
$recorder->destroy();

// Or use destructor (automatic)
unset($recorder);
```

## API Reference

### Constructor

```php
new Recorder(int $width = 80, int $height = 24, ?string $title = null)
```

### Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `start()` | `bool` | Start recording |
| `pause()` | `bool` | Pause recording |
| `resume()` | `bool` | Resume recording |
| `stop()` | `bool` | Stop recording |
| `capture(string $data)` | `bool` | Capture a frame |
| `export()` | `?string` | Export as JSON |
| `save(string $path)` | `bool` | Save to file |
| `getDuration()` | `float` | Get duration in seconds |
| `getFrameCount()` | `int` | Get number of frames |
| `getDimensions()` | `array` | Get width/height |
| `getTitle()` | `?string` | Get recording title |
| `isRecording()` | `bool` | Check if recording |
| `isPaused()` | `bool` | Check if paused |
| `isStopped()` | `bool` | Check if stopped |
| `destroy()` | `void` | Clean up resources |

## Best Practices

1. **Match terminal dimensions** - Use actual terminal size for accurate playback
   ```php
   [$width, $height] = tui_get_size();
   $recorder = new Recorder($width, $height);
   ```

2. **Add descriptive titles** - Makes recordings easier to identify

3. **Clean up resources** - Call `destroy()` or let destructor handle it

4. **Handle errors** - Check return values from recording methods

5. **Keep recordings short** - Focus on specific features or workflows
