<?php

declare(strict_types=1);

namespace Xocdr\Tui\Styling\Animation;

/**
 * Easing functions for smooth animations.
 *
 * Provides 27 standard easing functions commonly used in animation.
 * All functions take a normalized time value (0.0 to 1.0) and return
 * the eased value (also typically 0.0 to 1.0).
 *
 * @example
 * $progress = 0.5;
 * $eased = Easing::ease($progress, 'out-cubic');
 * // Or use the static methods directly:
 * $eased = Easing::outCubic($progress);
 */
class Easing
{
    public const LINEAR = 'linear';
    public const IN_QUAD = 'in-quad';
    public const OUT_QUAD = 'out-quad';
    public const IN_OUT_QUAD = 'in-out-quad';
    public const IN_CUBIC = 'in-cubic';
    public const OUT_CUBIC = 'out-cubic';
    public const IN_OUT_CUBIC = 'in-out-cubic';
    public const IN_QUART = 'in-quart';
    public const OUT_QUART = 'out-quart';
    public const IN_OUT_QUART = 'in-out-quart';
    public const IN_SINE = 'in-sine';
    public const OUT_SINE = 'out-sine';
    public const IN_OUT_SINE = 'in-out-sine';
    public const IN_EXPO = 'in-expo';
    public const OUT_EXPO = 'out-expo';
    public const IN_OUT_EXPO = 'in-out-expo';
    public const IN_CIRC = 'in-circ';
    public const OUT_CIRC = 'out-circ';
    public const IN_OUT_CIRC = 'in-out-circ';
    public const IN_ELASTIC = 'in-elastic';
    public const OUT_ELASTIC = 'out-elastic';
    public const IN_OUT_ELASTIC = 'in-out-elastic';
    public const IN_BACK = 'in-back';
    public const OUT_BACK = 'out-back';
    public const IN_OUT_BACK = 'in-out-back';
    public const IN_BOUNCE = 'in-bounce';
    public const OUT_BOUNCE = 'out-bounce';
    public const IN_OUT_BOUNCE = 'in-out-bounce';

    /**
     * Apply an easing function by name.
     */
    public static function ease(float $t, string $easing = self::LINEAR): float
    {
        if (function_exists('tui_ease')) {
            return tui_ease($t, $easing);
        }

        return match ($easing) {
            self::LINEAR => self::linear($t),
            self::IN_QUAD => self::inQuad($t),
            self::OUT_QUAD => self::outQuad($t),
            self::IN_OUT_QUAD => self::inOutQuad($t),
            self::IN_CUBIC => self::inCubic($t),
            self::OUT_CUBIC => self::outCubic($t),
            self::IN_OUT_CUBIC => self::inOutCubic($t),
            self::IN_QUART => self::inQuart($t),
            self::OUT_QUART => self::outQuart($t),
            self::IN_OUT_QUART => self::inOutQuart($t),
            self::IN_SINE => self::inSine($t),
            self::OUT_SINE => self::outSine($t),
            self::IN_OUT_SINE => self::inOutSine($t),
            self::IN_EXPO => self::inExpo($t),
            self::OUT_EXPO => self::outExpo($t),
            self::IN_OUT_EXPO => self::inOutExpo($t),
            self::IN_CIRC => self::inCirc($t),
            self::OUT_CIRC => self::outCirc($t),
            self::IN_OUT_CIRC => self::inOutCirc($t),
            self::IN_ELASTIC => self::inElastic($t),
            self::OUT_ELASTIC => self::outElastic($t),
            self::IN_OUT_ELASTIC => self::inOutElastic($t),
            self::IN_BACK => self::inBack($t),
            self::OUT_BACK => self::outBack($t),
            self::IN_OUT_BACK => self::inOutBack($t),
            self::IN_BOUNCE => self::inBounce($t),
            self::OUT_BOUNCE => self::outBounce($t),
            self::IN_OUT_BOUNCE => self::inOutBounce($t),
            default => self::linear($t),
        };
    }

    /**
     * Get all available easing function names.
     *
     * @return array<string>
     */
    public static function getAvailable(): array
    {
        return [
            self::LINEAR,
            self::IN_QUAD, self::OUT_QUAD, self::IN_OUT_QUAD,
            self::IN_CUBIC, self::OUT_CUBIC, self::IN_OUT_CUBIC,
            self::IN_QUART, self::OUT_QUART, self::IN_OUT_QUART,
            self::IN_SINE, self::OUT_SINE, self::IN_OUT_SINE,
            self::IN_EXPO, self::OUT_EXPO, self::IN_OUT_EXPO,
            self::IN_CIRC, self::OUT_CIRC, self::IN_OUT_CIRC,
            self::IN_ELASTIC, self::OUT_ELASTIC, self::IN_OUT_ELASTIC,
            self::IN_BACK, self::OUT_BACK, self::IN_OUT_BACK,
            self::IN_BOUNCE, self::OUT_BOUNCE, self::IN_OUT_BOUNCE,
        ];
    }

    // Linear

    public static function linear(float $t): float
    {
        return $t;
    }

    // Quadratic

    public static function inQuad(float $t): float
    {
        return $t * $t;
    }

    public static function outQuad(float $t): float
    {
        return $t * (2 - $t);
    }

    public static function inOutQuad(float $t): float
    {
        return $t < 0.5
            ? 2 * $t * $t
            : -1 + (4 - 2 * $t) * $t;
    }

    // Cubic

    public static function inCubic(float $t): float
    {
        return $t * $t * $t;
    }

    public static function outCubic(float $t): float
    {
        $t1 = $t - 1;
        return $t1 * $t1 * $t1 + 1;
    }

    public static function inOutCubic(float $t): float
    {
        return $t < 0.5
            ? 4 * $t * $t * $t
            : ($t - 1) * (2 * $t - 2) * (2 * $t - 2) + 1;
    }

    // Quartic

    public static function inQuart(float $t): float
    {
        return $t * $t * $t * $t;
    }

    public static function outQuart(float $t): float
    {
        $t1 = $t - 1;
        return 1 - $t1 * $t1 * $t1 * $t1;
    }

    public static function inOutQuart(float $t): float
    {
        $t1 = $t - 1;
        return $t < 0.5
            ? 8 * $t * $t * $t * $t
            : 1 - 8 * $t1 * $t1 * $t1 * $t1;
    }

    // Sine

    public static function inSine(float $t): float
    {
        return 1 - cos($t * M_PI / 2);
    }

    public static function outSine(float $t): float
    {
        return sin($t * M_PI / 2);
    }

    public static function inOutSine(float $t): float
    {
        return -(cos(M_PI * $t) - 1) / 2;
    }

    // Exponential

    public static function inExpo(float $t): float
    {
        return $t === 0.0 ? 0.0 : pow(2, 10 * ($t - 1));
    }

    public static function outExpo(float $t): float
    {
        return $t === 1.0 ? 1.0 : 1 - pow(2, -10 * $t);
    }

    public static function inOutExpo(float $t): float
    {
        if ($t === 0.0) {
            return 0.0;
        }
        if ($t === 1.0) {
            return 1.0;
        }

        return $t < 0.5
            ? pow(2, 20 * $t - 10) / 2
            : (2 - pow(2, -20 * $t + 10)) / 2;
    }

    // Circular

    public static function inCirc(float $t): float
    {
        return 1 - sqrt(1 - $t * $t);
    }

    public static function outCirc(float $t): float
    {
        $t1 = $t - 1;
        return sqrt(1 - $t1 * $t1);
    }

    public static function inOutCirc(float $t): float
    {
        return $t < 0.5
            ? (1 - sqrt(1 - 4 * $t * $t)) / 2
            : (sqrt(1 - pow(-2 * $t + 2, 2)) + 1) / 2;
    }

    // Elastic

    public static function inElastic(float $t): float
    {
        if ($t === 0.0) {
            return 0.0;
        }
        if ($t === 1.0) {
            return 1.0;
        }

        $c4 = (2 * M_PI) / 3;
        return -pow(2, 10 * $t - 10) * sin(($t * 10 - 10.75) * $c4);
    }

    public static function outElastic(float $t): float
    {
        if ($t === 0.0) {
            return 0.0;
        }
        if ($t === 1.0) {
            return 1.0;
        }

        $c4 = (2 * M_PI) / 3;
        return pow(2, -10 * $t) * sin(($t * 10 - 0.75) * $c4) + 1;
    }

    public static function inOutElastic(float $t): float
    {
        if ($t === 0.0) {
            return 0.0;
        }
        if ($t === 1.0) {
            return 1.0;
        }

        $c5 = (2 * M_PI) / 4.5;

        return $t < 0.5
            ? -(pow(2, 20 * $t - 10) * sin((20 * $t - 11.125) * $c5)) / 2
            : (pow(2, -20 * $t + 10) * sin((20 * $t - 11.125) * $c5)) / 2 + 1;
    }

    // Back (overshoots)

    public static function inBack(float $t): float
    {
        $c1 = 1.70158;
        $c3 = $c1 + 1;
        return $c3 * $t * $t * $t - $c1 * $t * $t;
    }

    public static function outBack(float $t): float
    {
        $c1 = 1.70158;
        $c3 = $c1 + 1;
        $t1 = $t - 1;
        return 1 + $c3 * $t1 * $t1 * $t1 + $c1 * $t1 * $t1;
    }

    public static function inOutBack(float $t): float
    {
        $c1 = 1.70158;
        $c2 = $c1 * 1.525;

        return $t < 0.5
            ? (pow(2 * $t, 2) * (($c2 + 1) * 2 * $t - $c2)) / 2
            : (pow(2 * $t - 2, 2) * (($c2 + 1) * (2 * $t - 2) + $c2) + 2) / 2;
    }

    // Bounce

    public static function outBounce(float $t): float
    {
        $n1 = 7.5625;
        $d1 = 2.75;

        if ($t < 1 / $d1) {
            return $n1 * $t * $t;
        } elseif ($t < 2 / $d1) {
            $t -= 1.5 / $d1;
            return $n1 * $t * $t + 0.75;
        } elseif ($t < 2.5 / $d1) {
            $t -= 2.25 / $d1;
            return $n1 * $t * $t + 0.9375;
        } else {
            $t -= 2.625 / $d1;
            return $n1 * $t * $t + 0.984375;
        }
    }

    public static function inBounce(float $t): float
    {
        return 1 - self::outBounce(1 - $t);
    }

    public static function inOutBounce(float $t): float
    {
        return $t < 0.5
            ? (1 - self::outBounce(1 - 2 * $t)) / 2
            : (1 + self::outBounce(2 * $t - 1)) / 2;
    }
}
