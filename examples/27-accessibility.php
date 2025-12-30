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
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Terminal\Accessibility;
use Xocdr\Tui\Tui;

// Check accessibility features
$features = Accessibility::getFeatures();

$component = function () use ($features) {
    return Box::create()
        ->flexDirection('column')
        ->padding(1)
        ->gap(1)
        ->children([
            Text::create('Accessibility Features')->bold()->underline(),
            Text::create(''),

            // Display detected preferences
            Box::create()->flexDirection('column')->children([
                Text::create('User Preferences:')->bold(),
                Text::create(sprintf(
                    '  Reduced Motion: %s',
                    $features['reduced_motion'] ? 'Yes (animations will be skipped)' : 'No'
                ))->color($features['reduced_motion'] ? 'yellow' : 'green'),
                Text::create(sprintf(
                    '  High Contrast: %s',
                    $features['high_contrast'] ? 'Yes (using high contrast colors)' : 'No'
                ))->color($features['high_contrast'] ? 'yellow' : 'green'),
                Text::create(sprintf(
                    '  Screen Reader: %s',
                    $features['screen_reader'] ? 'Detected' : 'Not detected'
                ))->color($features['screen_reader'] ? 'cyan' : 'gray'),
            ]),

            Text::create(''),

            // ARIA roles demo
            Box::create()->flexDirection('column')->children([
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
            ]),

            Text::create(''),

            // Instructions
            Box::create()->flexDirection('column')->children([
                Text::create('Test with environment variables:')->bold(),
                Text::create('  REDUCE_MOTION=1 php examples/27-accessibility.php')->italic()->dim(),
                Text::create('  HIGH_CONTRAST=1 php examples/27-accessibility.php')->italic()->dim(),
            ]),

            Text::create(''),
            Text::create('Press Ctrl+C to exit')->dim(),
        ]);
};

// Announce to screen reader when app starts
Accessibility::announce('Accessibility demo loaded');

Tui::render($component)->waitUntilExit();
