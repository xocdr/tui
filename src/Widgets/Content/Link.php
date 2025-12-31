<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Content;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Widget;

class Link extends Widget
{
    private string $url = '';

    private ?string $text = null;

    private string $color = 'cyan';

    private bool $underlineEnabled = true;

    private bool $showUrlEnabled = false;

    private string $urlColor = 'dim';

    private function __construct()
    {
    }

    public static function create(string $url = ''): self
    {
        $instance = new self();
        $instance->url = $url;

        return $instance;
    }

    public static function email(string $email): self
    {
        $instance = new self();
        $instance->url = 'mailto:' . $email;
        $instance->text = $email;

        return $instance;
    }

    public static function file(string $path): self
    {
        $instance = new self();
        $instance->url = 'file://' . $path;
        $instance->text = basename($path);

        return $instance;
    }

    public function url(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function text(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function label(string $label): self
    {
        $this->text = $label;

        return $this;
    }

    public function color(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function underline(bool $underline = true): self
    {
        $this->underlineEnabled = $underline;

        return $this;
    }

    public function showUrl(bool $show = true): self
    {
        $this->showUrlEnabled = $show;

        return $this;
    }

    public function urlColor(string $color): self
    {
        $this->urlColor = $color;

        return $this;
    }

    public function openOnClick(bool $open = true): self
    {
        // Terminal hyperlinks are handled by the terminal emulator
        return $this;
    }

    public function build(): Component
    {
        $displayText = $this->text ?? $this->url;

        // OSC 8 hyperlink escape sequence
        $osc8Start = "\e]8;;" . $this->url . "\e\\";
        $osc8End = "\e]8;;\e\\";

        $linkText = Text::create($osc8Start . $displayText . $osc8End);
        $linkText = $linkText->color($this->color);

        if ($this->underlineEnabled) {
            $linkText = $linkText->underline();
        }

        if ($this->showUrlEnabled && $this->text !== null) {
            return Box::row([
                $linkText,
                Text::create(' ('),
                Text::create($this->url)->dim(),
                Text::create(')'),
            ]);
        }

        return $linkText;
    }
}
