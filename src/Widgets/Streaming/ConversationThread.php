<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Streaming;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Support\Constants;
use Xocdr\Tui\Widgets\Widget;

class ConversationThread extends Widget
{
    /** @var array<ConversationMessage> */
    private array $messages = [];

    private bool $showTimestamps = false;

    private bool $showAvatars = true;

    private string $userLabel = 'You';

    private string $assistantLabel = 'Assistant';

    private ?string $userColor = null;

    private ?string $assistantColor = null;

    private int $maxWidth = Constants::DEFAULT_TERMINAL_WIDTH;

    private bool $alternateBackground = false;

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    /**
     * @param array<ConversationMessage|array{role: string, content: string, timestamp?: float|null, id?: string|null}> $messages
     */
    public function messages(array $messages): self
    {
        $this->messages = [];

        foreach ($messages as $message) {
            if ($message instanceof ConversationMessage) {
                $this->messages[] = $message;
            } elseif (is_array($message)) {
                $this->messages[] = ConversationMessage::from($message);
            }
        }

        return $this;
    }

    public function addMessage(ConversationMessage $message): self
    {
        $this->messages[] = $message;

        return $this;
    }

    public function addUserMessage(string $content): self
    {
        $this->messages[] = new ConversationMessage('user', $content);

        return $this;
    }

    public function addAssistantMessage(string $content): self
    {
        $this->messages[] = new ConversationMessage('assistant', $content);

        return $this;
    }

    public function showTimestamps(bool $show = true): self
    {
        $this->showTimestamps = $show;

        return $this;
    }

    public function showAvatars(bool $show = true): self
    {
        $this->showAvatars = $show;

        return $this;
    }

    public function userLabel(string $label): self
    {
        $this->userLabel = $label;

        return $this;
    }

    public function assistantLabel(string $label): self
    {
        $this->assistantLabel = $label;

        return $this;
    }

    public function userColor(string $color): self
    {
        $this->userColor = $color;

        return $this;
    }

    public function assistantColor(string $color): self
    {
        $this->assistantColor = $color;

        return $this;
    }

    public function maxWidth(int $width): self
    {
        $this->maxWidth = $width;

        return $this;
    }

    public function alternateBackground(bool $alternate = true): self
    {
        $this->alternateBackground = $alternate;

        return $this;
    }

    public function build(): Component
    {
        if (empty($this->messages)) {
            return Text::create('No messages yet')->dim();
        }

        $elements = [];

        foreach ($this->messages as $i => $message) {
            if ($i > 0) {
                $elements[] = Text::create('');
            }

            $elements[] = $this->renderMessage($message, $i);
        }

        return Box::column($elements);
    }

    private function renderMessage(ConversationMessage $message, int $index): mixed
    {
        $isUser = $message->role === 'user';
        $label = $isUser ? $this->userLabel : $this->assistantLabel;
        $color = $isUser ? ($this->userColor ?? 'blue') : ($this->assistantColor ?? 'green');

        $headerParts = [];

        if ($this->showAvatars) {
            $avatar = $isUser ? '●' : '◆';
            $headerParts[] = Text::create($avatar . ' ');
        }

        $labelText = Text::create($label)->bold()->color($color);
        $headerParts[] = $labelText;

        if ($this->showTimestamps && $message->timestamp !== null) {
            $time = date('H:i', (int) $message->timestamp);
            $headerParts[] = Text::create(' ' . $time)->dim();
        }

        $contentLines = $this->wrapText($message->content, $this->maxWidth);
        $contentElements = [];

        foreach ($contentLines as $line) {
            $contentElements[] = Text::create('  ' . $line);
        }

        return Box::column([
            Box::row($headerParts),
            Box::column($contentElements),
        ]);
    }

    /**
     * @return array<string>
     */
    private function wrapText(string $text, int $maxWidth): array
    {
        $lines = [];
        $paragraphs = explode("\n", $text);

        foreach ($paragraphs as $paragraph) {
            if ($paragraph === '') {
                $lines[] = '';
                continue;
            }

            $words = explode(' ', $paragraph);
            $currentLine = '';

            foreach ($words as $word) {
                $testLine = $currentLine === '' ? $word : $currentLine . ' ' . $word;

                if (mb_strlen($testLine) <= $maxWidth) {
                    $currentLine = $testLine;
                } else {
                    if ($currentLine !== '') {
                        $lines[] = $currentLine;
                    }
                    $currentLine = $word;
                }
            }

            if ($currentLine !== '') {
                $lines[] = $currentLine;
            }
        }

        return $lines;
    }
}

class ConversationMessage
{
    public function __construct(
        public readonly string $role,
        public readonly string $content,
        public readonly ?float $timestamp = null,
        public readonly ?string $id = null,
    ) {
    }

    /**
     * @param array{role?: string, content?: string, timestamp?: float|null, id?: string|null} $data
     */
    public static function from(array $data): self
    {
        return new self(
            role: $data['role'] ?? 'user',
            content: $data['content'] ?? '',
            timestamp: $data['timestamp'] ?? null,
            id: $data['id'] ?? null,
        );
    }

    public static function user(string $content): self
    {
        return new self('user', $content, microtime(true));
    }

    public static function assistant(string $content): self
    {
        return new self('assistant', $content, microtime(true));
    }

    public static function system(string $content): self
    {
        return new self('system', $content, microtime(true));
    }
}
