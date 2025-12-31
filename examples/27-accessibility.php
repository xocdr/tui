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
use Xocdr\Tui\Components\BoxColumn;
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

        return new Box([
            new BoxColumn([
                (new Text('Accessibility Features'))->bold()->underline(),
                new Newline(),

                // Display detected preferences
                (new Text('User Preferences:'))->bold(),
                (new Text(sprintf(
                    '  Reduced Motion: %s',
                    $this->features['reduced_motion'] ? 'Yes (animations will be skipped)' : 'No'
                )))->color($this->features['reduced_motion'] ? Color::Yellow : Color::Green),
                (new Text(sprintf(
                    '  High Contrast: %s',
                    $this->features['high_contrast'] ? 'Yes (using high contrast colors)' : 'No'
                )))->color($this->features['high_contrast'] ? Color::Yellow : Color::Green),
                (new Text(sprintf(
                    '  Screen Reader: %s',
                    $this->features['screen_reader'] ? 'Detected' : 'Not detected'
                )))->color($this->features['screen_reader'] ? Color::Cyan : Color::Gray),

                new Newline(),

                // ARIA roles demo
                (new Text('ARIA Role Constants:'))->bold(),
                (new Text(sprintf(
                    '  ROLE_BUTTON (%d) = "%s"',
                    Accessibility::ROLE_BUTTON,
                    Accessibility::roleToString(Accessibility::ROLE_BUTTON)
                )))->dim(),
                (new Text(sprintf(
                    '  ROLE_DIALOG (%d) = "%s"',
                    Accessibility::ROLE_DIALOG,
                    Accessibility::roleToString(Accessibility::ROLE_DIALOG)
                )))->dim(),
                (new Text(sprintf(
                    '  roleFromString("menu") = %d',
                    Accessibility::roleFromString('menu')
                )))->dim(),

                new Newline(),

                // Instructions
                (new Text('Test with environment variables:'))->bold(),
                (new Text('  REDUCE_MOTION=1 php examples/27-accessibility.php'))->italic()->dim(),
                (new Text('  HIGH_CONTRAST=1 php examples/27-accessibility.php'))->italic()->dim(),

                new Newline(),
                (new Text('Press q or ESC to exit'))->dim(),
            ]),
        ]);
    }
}

// Announce to screen reader when app starts
Accessibility::announce('Accessibility demo loaded');

(new AccessibilityDemo($features))->run();
