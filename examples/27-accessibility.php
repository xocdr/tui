#!/usr/bin/env php
<?php

/**
 * Accessibility Demo
 *
 * Demonstrates accessibility features:
 * - Screen reader announcements
 * - Reduced motion preference detection
 * - High contrast preference detection
 * - ARIA role utilities
 *
 * Try setting environment variables to test:
 *   REDUCE_MOTION=1 php examples/27-accessibility.php
 *   HIGH_CONTRAST=1 php examples/27-accessibility.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\Terminal\Accessibility;
use Xocdr\Tui\UI;

// Check accessibility features
$features = Accessibility::getFeatures();

class AccessibilityDemo extends UI
{
    /** @var array{reduced_motion: bool, high_contrast: bool, screen_reader: bool} */
    private array $features;

    /**
     * @param array{reduced_motion: bool, high_contrast: bool, screen_reader: bool} $features
     */
    public function __construct(array $features)
    {
        $this->features = $features;
    }

    public function build(): Component
    {
        $this->onKeyPress(function (string $input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        return Box::column([
            Text::create('Accessibility Features')->bold()->underline(),
            Newline::create(),

            // Display detected preferences
            Text::create('User Preferences:')->bold(),
            Text::create(sprintf(
                '  Reduced Motion: %s',
                $this->features['reduced_motion'] ? 'Yes (animations will be skipped)' : 'No'
            ))->color($this->features['reduced_motion'] ? Color::Yellow : Color::Green),
            Text::create(sprintf(
                '  High Contrast: %s',
                $this->features['high_contrast'] ? 'Yes (using high contrast colors)' : 'No'
            ))->color($this->features['high_contrast'] ? Color::Yellow : Color::Green),
            Text::create(sprintf(
                '  Screen Reader: %s',
                $this->features['screen_reader'] ? 'Detected' : 'Not detected'
            ))->color($this->features['screen_reader'] ? Color::Cyan : Color::Gray),

            Newline::create(),

            // ARIA roles demo
            Text::create('ARIA Role Constants:')->bold(),
            Text::create(sprintf(
                '  ROLE_BUTTON (%d) = "%s"',
                Accessibility::ROLE_BUTTON,
                Accessibility::roleToString(Accessibility::ROLE_BUTTON)
            ))->dim(),
            Text::create(sprintf(
                '  ROLE_DIALOG (%d) = "%s"',
                Accessibility::ROLE_DIALOG,
                Accessibility::roleToString(Accessibility::ROLE_DIALOG)
            ))->dim(),
            Text::create(sprintf(
                '  roleFromString("menu") = %d',
                Accessibility::roleFromString('menu')
            ))->dim(),

            Newline::create(),

            // Instructions
            Text::create('Test with environment variables:')->bold(),
            Text::create('  REDUCE_MOTION=1 php examples/27-accessibility.php')->italic()->dim(),
            Text::create('  HIGH_CONTRAST=1 php examples/27-accessibility.php')->italic()->dim(),

            Newline::create(),
            Text::create('Press q or ESC to exit')->dim(),
        ]);
    }
}

// Announce to screen reader when app starts
Accessibility::announce('Accessibility demo loaded');

AccessibilityDemo::run(new AccessibilityDemo($features));
