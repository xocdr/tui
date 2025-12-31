<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Display;

class TodoItem
{
    /**
     * @param array<TodoItem> $subtasks
     */
    public function __construct(
        public readonly string $id,
        public readonly string $content,
        public readonly ?string $activeForm = null,
        public readonly TodoStatus $status = TodoStatus::PENDING,
        public readonly ?string $duration = null,
        public readonly array $subtasks = [],
    ) {
    }

    /**
     * Get the active form text, falling back to content.
     */
    public function getActiveForm(): string
    {
        return $this->activeForm ?? $this->content;
    }

    /**
     * Create a TodoItem from an array of data.
     *
     * @param array{id?: string, content?: string, activeForm?: string, status?: TodoStatus|string, subtasks?: array<mixed>, duration?: string|null} $data
     * @throws \ValueError If the status string is invalid
     */
    public static function from(array $data): self
    {
        $status = $data['status'] ?? 'pending';
        if (is_string($status)) {
            // Use PHP's built-in BackedEnum::from() which throws ValueError for invalid values
            $status = TodoStatus::from($status);
        }

        $subtasks = [];
        foreach ($data['subtasks'] ?? [] as $subtask) {
            $subtasks[] = self::from($subtask);
        }

        return new self(
            id: $data['id'] ?? uniqid(),
            content: $data['content'] ?? '',
            activeForm: $data['activeForm'] ?? $data['content'] ?? '',
            status: $status,
            duration: $data['duration'] ?? null,
            subtasks: $subtasks,
        );
    }

    /**
     * Create a new TodoItem with a different status.
     */
    public function withStatus(TodoStatus $status): self
    {
        return new self(
            id: $this->id,
            content: $this->content,
            activeForm: $this->activeForm ?? $this->content,
            status: $status,
            duration: $this->duration,
            subtasks: $this->subtasks,
        );
    }
}
