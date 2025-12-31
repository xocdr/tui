<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Content;

class TextSegment
{
    public function __construct(
        public readonly string $text,
        public readonly ?string $color = null,
        public readonly bool $bold = false,
        public readonly bool $dim = false,
        public readonly bool $italic = false,
        public readonly bool $underline = false,
        public readonly ?string $link = null,
    ) {
    }

    /**
     * @param array{text?: string, color?: string|null, bold?: bool, dim?: bool, italic?: bool, underline?: bool, link?: string|null}|string $data
     */
    public static function from(array|string $data): self
    {
        if (is_string($data)) {
            return new self(text: $data);
        }

        return new self(
            text: $data['text'] ?? '',
            color: $data['color'] ?? null,
            bold: $data['bold'] ?? false,
            dim: $data['dim'] ?? false,
            italic: $data['italic'] ?? false,
            underline: $data['underline'] ?? false,
            link: $data['link'] ?? null,
        );
    }
}
