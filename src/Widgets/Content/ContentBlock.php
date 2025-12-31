<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Content;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Widget;

class ContentBlock extends Widget
{
    private ?string $title = null;

    private mixed $content = null;

    private ?string $language = null;

    private string|bool $border = false;

    private string $borderColor = 'gray';

    private int $padding = 0;

    private int $paddingX = 0;

    private int $paddingY = 0;

    private bool $showLineNumbers = false;

    private int $startLineNumber = 1;

    private bool $syntaxHighlight = false;

    private ?int $maxHeight = null;

    private bool $wrap = false;

    private ?string $backgroundColor = null;

    private ?string $headerColor = null;

    private ?string $footerText = null;

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function title(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function content(mixed $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function language(?string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function border(string|bool $border): self
    {
        $this->border = $border;

        return $this;
    }

    public function borderColor(string $color): self
    {
        $this->borderColor = $color;

        return $this;
    }

    public function padding(int $padding): self
    {
        $this->padding = $padding;

        return $this;
    }

    public function paddingX(int $padding): self
    {
        $this->paddingX = $padding;

        return $this;
    }

    public function paddingY(int $padding): self
    {
        $this->paddingY = $padding;

        return $this;
    }

    public function showLineNumbers(bool $show = true): self
    {
        $this->showLineNumbers = $show;

        return $this;
    }

    public function startLineNumber(int $line): self
    {
        $this->startLineNumber = $line;

        return $this;
    }

    public function syntaxHighlight(bool $highlight = true): self
    {
        $this->syntaxHighlight = $highlight;

        return $this;
    }

    public function maxHeight(?int $height): self
    {
        $this->maxHeight = $height;

        return $this;
    }

    public function wrap(bool $wrap = true): self
    {
        $this->wrap = $wrap;

        return $this;
    }

    public function backgroundColor(?string $color): self
    {
        $this->backgroundColor = $color;

        return $this;
    }

    public function headerColor(?string $color): self
    {
        $this->headerColor = $color;

        return $this;
    }

    public function footerText(?string $text): self
    {
        $this->footerText = $text;

        return $this;
    }

    public function build(): Component
    {
        $elements = [];

        if ($this->title !== null) {
            $elements[] = $this->renderHeader();
        }

        $elements[] = $this->renderContent();

        if ($this->footerText !== null) {
            $elements[] = $this->renderFooter();
        }

        $container = Box::column($elements);

        if ($this->border !== false) {
            $borderStyle = is_string($this->border) ? $this->border : 'single';
            $box = Box::create()
                ->border($borderStyle)
                ->borderColor($this->borderColor)
                ->children([$container]);

            if ($this->title !== null) {
                $box = $box->borderTitle($this->title);
            }

            return $box;
        }

        return $container;
    }

    private function renderHeader(): mixed
    {
        $parts = [];

        if ($this->language !== null) {
            $langText = Text::create('[' . $this->language . ']');
            $parts[] = $this->headerColor !== null
                ? $langText->color($this->headerColor)
                : $langText->dim();
        }

        if ($this->title !== null && $this->border === false) {
            $titleText = Text::create($this->title);
            $parts[] = $this->headerColor !== null
                ? $titleText->color($this->headerColor)->bold()
                : $titleText->bold();
        }

        return Box::row($parts);
    }

    private function renderContent(): mixed
    {
        if ($this->content === null) {
            return Text::create('');
        }

        if (!is_string($this->content)) {
            return $this->content;
        }

        $lines = explode("\n", $this->content);

        if ($this->maxHeight !== null && count($lines) > $this->maxHeight) {
            $lines = array_slice($lines, 0, $this->maxHeight);
            $lines[] = '...';
        }

        // Calculate max digits once for line number padding
        $maxLineNumber = $this->startLineNumber + count($lines) - 1;
        $maxDigits = strlen((string) $maxLineNumber);

        $elements = [];
        $lineNumber = $this->startLineNumber;

        foreach ($lines as $line) {
            if ($this->showLineNumbers) {
                $elements[] = $this->renderLineWithNumber($line, $lineNumber, $maxDigits);
            } else {
                $elements[] = $this->renderLine($line);
            }
            $lineNumber++;
        }

        $content = Box::column($elements);

        $px = $this->paddingX > 0 ? $this->paddingX : $this->padding;
        $py = $this->paddingY > 0 ? $this->paddingY : $this->padding;

        if ($px > 0 || $py > 0) {
            return Box::create()
                ->paddingX($px)
                ->paddingY($py)
                ->children([$content]);
        }

        return $content;
    }

    private function renderLineWithNumber(string $line, int $lineNumber, int $maxDigits): mixed
    {
        $formattedNumber = str_pad((string) $lineNumber, $maxDigits, ' ', STR_PAD_LEFT);

        return Box::row([
            Text::create($formattedNumber . ' | ')->dim(),
            $this->renderLine($line),
        ]);
    }

    private function renderLine(string $line): mixed
    {
        if ($this->syntaxHighlight && $this->language !== null) {
            return $this->highlightLine($line);
        }

        return Text::create($line);
    }

    private function highlightLine(string $line): mixed
    {
        $keywords = $this->getLanguageKeywords($this->language);

        if (empty($keywords)) {
            return Text::create($line);
        }

        $parts = [];
        $remaining = $line;

        while ($remaining !== '') {
            $matched = false;

            if (preg_match('/^(\s+)/', $remaining, $matches)) {
                $parts[] = Text::create($matches[1]);
                $remaining = substr($remaining, strlen($matches[1]));
                continue;
            }

            if (preg_match('/^(\/\/.*|#.*)$/', $remaining, $matches)) {
                $parts[] = Text::create($matches[1])->color('gray');
                $remaining = '';
                continue;
            }

            if (preg_match('/^(["\'])/', $remaining, $matches)) {
                $quote = $matches[1];
                if (preg_match('/^' . $quote . '([^' . $quote . '\\\\]|\\\\.)*' . $quote . '/', $remaining, $strMatch)) {
                    $parts[] = Text::create($strMatch[0])->color('green');
                    $remaining = substr($remaining, strlen($strMatch[0]));
                    continue;
                }
            }

            if (preg_match('/^(\d+\.?\d*)/', $remaining, $matches)) {
                $parts[] = Text::create($matches[1])->color('yellow');
                $remaining = substr($remaining, strlen($matches[1]));
                continue;
            }

            foreach ($keywords as $keyword => $color) {
                if (preg_match('/^(' . preg_quote($keyword, '/') . ')(?!\w)/', $remaining, $matches)) {
                    $parts[] = Text::create($matches[1])->color($color);
                    $remaining = substr($remaining, strlen($matches[1]));
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                if (preg_match('/^(\w+)/', $remaining, $matches)) {
                    $parts[] = Text::create($matches[1]);
                    $remaining = substr($remaining, strlen($matches[1]));
                } elseif (preg_match('/^([^\w\s]+)/', $remaining, $matches)) {
                    $parts[] = Text::create($matches[1]);
                    $remaining = substr($remaining, strlen($matches[1]));
                } else {
                    $parts[] = Text::create($remaining[0]);
                    $remaining = substr($remaining, 1);
                }
            }
        }

        return Box::row($parts);
    }

    /**
     * @return array<string, string>
     */
    private function getLanguageKeywords(?string $language): array
    {
        return match ($language) {
            'php' => [
                'function' => 'magenta',
                'class' => 'magenta',
                'interface' => 'magenta',
                'trait' => 'magenta',
                'extends' => 'magenta',
                'implements' => 'magenta',
                'public' => 'cyan',
                'private' => 'cyan',
                'protected' => 'cyan',
                'static' => 'cyan',
                'final' => 'cyan',
                'abstract' => 'cyan',
                'const' => 'cyan',
                'return' => 'magenta',
                'if' => 'magenta',
                'else' => 'magenta',
                'elseif' => 'magenta',
                'foreach' => 'magenta',
                'for' => 'magenta',
                'while' => 'magenta',
                'switch' => 'magenta',
                'case' => 'magenta',
                'break' => 'magenta',
                'continue' => 'magenta',
                'new' => 'magenta',
                'use' => 'cyan',
                'namespace' => 'cyan',
                'true' => 'yellow',
                'false' => 'yellow',
                'null' => 'yellow',
            ],
            'javascript', 'js', 'typescript', 'ts' => [
                'function' => 'magenta',
                'class' => 'magenta',
                'extends' => 'magenta',
                'const' => 'cyan',
                'let' => 'cyan',
                'var' => 'cyan',
                'return' => 'magenta',
                'if' => 'magenta',
                'else' => 'magenta',
                'for' => 'magenta',
                'while' => 'magenta',
                'switch' => 'magenta',
                'case' => 'magenta',
                'break' => 'magenta',
                'continue' => 'magenta',
                'new' => 'magenta',
                'import' => 'cyan',
                'export' => 'cyan',
                'from' => 'cyan',
                'async' => 'magenta',
                'await' => 'magenta',
                'true' => 'yellow',
                'false' => 'yellow',
                'null' => 'yellow',
                'undefined' => 'yellow',
            ],
            'bash', 'sh', 'shell' => [
                'if' => 'magenta',
                'then' => 'magenta',
                'else' => 'magenta',
                'elif' => 'magenta',
                'fi' => 'magenta',
                'for' => 'magenta',
                'while' => 'magenta',
                'do' => 'magenta',
                'done' => 'magenta',
                'case' => 'magenta',
                'esac' => 'magenta',
                'function' => 'magenta',
                'return' => 'magenta',
                'export' => 'cyan',
                'local' => 'cyan',
            ],
            default => [],
        };
    }

    private function renderFooter(): mixed
    {
        return Text::create($this->footerText ?? '')->dim();
    }
}
