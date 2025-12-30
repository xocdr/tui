<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Terminal;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Terminal\Accessibility;

class AccessibilityTest extends TestCase
{
    public function testRoleConstants(): void
    {
        $this->assertSame(0, Accessibility::ROLE_NONE);
        $this->assertSame(1, Accessibility::ROLE_BUTTON);
        $this->assertSame(2, Accessibility::ROLE_CHECKBOX);
        $this->assertSame(3, Accessibility::ROLE_DIALOG);
        $this->assertSame(4, Accessibility::ROLE_NAVIGATION);
        $this->assertSame(5, Accessibility::ROLE_MENU);
        $this->assertSame(6, Accessibility::ROLE_MENUITEM);
        $this->assertSame(7, Accessibility::ROLE_TEXTBOX);
        $this->assertSame(8, Accessibility::ROLE_ALERT);
        $this->assertSame(9, Accessibility::ROLE_STATUS);
    }

    public function testRoleToString(): void
    {
        // Skip if ext-tui handles roles differently
        if (function_exists('tui_aria_role_to_string')) {
            // Just verify it returns a string
            $this->assertIsString(Accessibility::roleToString(Accessibility::ROLE_BUTTON));

            return;
        }

        $this->assertSame('none', Accessibility::roleToString(Accessibility::ROLE_NONE));
        $this->assertSame('button', Accessibility::roleToString(Accessibility::ROLE_BUTTON));
        $this->assertSame('checkbox', Accessibility::roleToString(Accessibility::ROLE_CHECKBOX));
        $this->assertSame('dialog', Accessibility::roleToString(Accessibility::ROLE_DIALOG));
        $this->assertSame('navigation', Accessibility::roleToString(Accessibility::ROLE_NAVIGATION));
        $this->assertSame('menu', Accessibility::roleToString(Accessibility::ROLE_MENU));
        $this->assertSame('menuitem', Accessibility::roleToString(Accessibility::ROLE_MENUITEM));
        $this->assertSame('textbox', Accessibility::roleToString(Accessibility::ROLE_TEXTBOX));
        $this->assertSame('alert', Accessibility::roleToString(Accessibility::ROLE_ALERT));
        $this->assertSame('status', Accessibility::roleToString(Accessibility::ROLE_STATUS));
    }

    public function testRoleToStringUnknownRole(): void
    {
        if (function_exists('tui_aria_role_to_string')) {
            // Just verify it returns a string for unknown
            $this->assertIsString(Accessibility::roleToString(999));

            return;
        }

        $this->assertSame('none', Accessibility::roleToString(999));
    }

    public function testRoleFromString(): void
    {
        // Skip if ext-tui handles roles differently
        if (function_exists('tui_aria_role_from_string')) {
            // Just verify it returns an int
            $this->assertIsInt(Accessibility::roleFromString('button'));

            return;
        }

        $this->assertSame(Accessibility::ROLE_NONE, Accessibility::roleFromString('none'));
        $this->assertSame(Accessibility::ROLE_BUTTON, Accessibility::roleFromString('button'));
        $this->assertSame(Accessibility::ROLE_CHECKBOX, Accessibility::roleFromString('checkbox'));
        $this->assertSame(Accessibility::ROLE_DIALOG, Accessibility::roleFromString('dialog'));
        $this->assertSame(Accessibility::ROLE_NAVIGATION, Accessibility::roleFromString('navigation'));
        $this->assertSame(Accessibility::ROLE_MENU, Accessibility::roleFromString('menu'));
        $this->assertSame(Accessibility::ROLE_MENUITEM, Accessibility::roleFromString('menuitem'));
        $this->assertSame(Accessibility::ROLE_TEXTBOX, Accessibility::roleFromString('textbox'));
        $this->assertSame(Accessibility::ROLE_ALERT, Accessibility::roleFromString('alert'));
        $this->assertSame(Accessibility::ROLE_STATUS, Accessibility::roleFromString('status'));
    }

    public function testRoleFromStringUnknown(): void
    {
        if (function_exists('tui_aria_role_from_string')) {
            // Just verify it returns an int for unknown
            $this->assertIsInt(Accessibility::roleFromString('unknown'));

            return;
        }

        $this->assertSame(Accessibility::ROLE_NONE, Accessibility::roleFromString('unknown'));
    }

    public function testPrefersReducedMotionWithEnvVar(): void
    {
        // Skip if ext-tui handles this natively
        if (function_exists('tui_prefers_reduced_motion')) {
            // Just verify it returns a bool
            $this->assertIsBool(Accessibility::prefersReducedMotion());

            return;
        }

        // Save current value
        $original = getenv('REDUCE_MOTION');

        // Test with env var set
        putenv('REDUCE_MOTION=1');
        $this->assertTrue(Accessibility::prefersReducedMotion());

        putenv('REDUCE_MOTION=true');
        $this->assertTrue(Accessibility::prefersReducedMotion());

        putenv('REDUCE_MOTION=yes');
        $this->assertTrue(Accessibility::prefersReducedMotion());

        putenv('REDUCE_MOTION=0');
        $this->assertFalse(Accessibility::prefersReducedMotion());

        putenv('REDUCE_MOTION=false');
        $this->assertFalse(Accessibility::prefersReducedMotion());

        // Restore
        if ($original === false) {
            putenv('REDUCE_MOTION');
        } else {
            putenv("REDUCE_MOTION={$original}");
        }
    }

    public function testPrefersHighContrastWithEnvVar(): void
    {
        // Save current value
        $original = getenv('HIGH_CONTRAST');

        // Test with env var set
        putenv('HIGH_CONTRAST=1');
        $this->assertTrue(Accessibility::prefersHighContrast());

        putenv('HIGH_CONTRAST=true');
        $this->assertTrue(Accessibility::prefersHighContrast());

        putenv('HIGH_CONTRAST=0');
        $this->assertFalse(Accessibility::prefersHighContrast());

        // Restore
        if ($original === false) {
            putenv('HIGH_CONTRAST');
        } else {
            putenv("HIGH_CONTRAST={$original}");
        }
    }

    public function testGetFeatures(): void
    {
        $features = Accessibility::getFeatures();

        $this->assertIsArray($features);
        $this->assertArrayHasKey('reduced_motion', $features);
        $this->assertArrayHasKey('high_contrast', $features);
        $this->assertArrayHasKey('screen_reader', $features);
        $this->assertIsBool($features['reduced_motion']);
        $this->assertIsBool($features['high_contrast']);
        $this->assertIsBool($features['screen_reader']);
    }

    public function testAnnounceReturnsFalseWithoutExtension(): void
    {
        // Without ext-tui function, announce returns false
        if (!function_exists('tui_announce')) {
            $this->assertFalse(Accessibility::announce('test message'));
        } else {
            $this->assertTrue(Accessibility::announce('test message'));
        }
    }
}
