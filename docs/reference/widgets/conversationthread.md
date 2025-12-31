# ConversationThread

A chat-style conversation display widget.

## Namespace

```php
use Xocdr\Tui\Widgets\Streaming\ConversationThread;
use Xocdr\Tui\Widgets\Streaming\ConversationMessage;
```

## Overview

The ConversationThread widget displays a chat conversation. Features include:

- User/assistant message styling
- Avatars and labels
- Timestamps
- Word wrapping

## Console Appearance

```
👤 You
  Hello, can you help me with a task?

🤖 Assistant
  Of course! I'd be happy to help. What
  do you need assistance with?
```

## Basic Usage

```php
ConversationThread::create()
    ->addUserMessage('Hello!')
    ->addAssistantMessage('Hi there! How can I help?');

ConversationThread::create()
    ->messages([
        ConversationMessage::user('What is PHP?'),
        ConversationMessage::assistant('PHP is a server-side language...'),
    ])
    ->showTimestamps();
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `ConversationThread::create()` | Create thread |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `messages(array)` | array | [] | Set messages |
| `addMessage(ConversationMessage)` | - | - | Add message |
| `addUserMessage(string)` | - | - | Add user message |
| `addAssistantMessage(string)` | - | - | Add assistant message |
| `showTimestamps(bool)` | bool | false | Show timestamps |
| `showAvatars(bool)` | bool | true | Show avatars |
| `userLabel(string)` | string | 'You' | User label |
| `assistantLabel(string)` | string | 'Assistant' | Assistant label |
| `userColor(string)` | string | 'blue' | User color |
| `assistantColor(string)` | string | 'green' | Assistant color |
| `maxWidth(int)` | int | 80 | Max message width |
| `alternateBackground(bool)` | bool | false | Alternate backgrounds |

## ConversationMessage Class

```php
class ConversationMessage {
    public readonly string $role;
    public readonly string $content;
    public readonly ?float $timestamp;
    public readonly ?string $id;

    public static function user(string $content): self;
    public static function assistant(string $content): self;
    public static function system(string $content): self;
    public static function from(array $data): self;
}
```

## See Also

- [StreamingText](./streamingtext.md) - Streaming content
- [ThinkingBlock](./thinkingblock.md) - AI thinking indicator
