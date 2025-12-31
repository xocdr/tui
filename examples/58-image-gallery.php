#!/usr/bin/env php
<?php

/**
 * Image Gallery Demo
 *
 * Interactive image gallery with keyboard navigation.
 * Demonstrates:
 * - Loading images from different sources
 * - Keyboard navigation between images
 * - Displaying image metadata
 * - Graceful fallback when graphics not supported
 *
 * Run in your terminal: php examples/26-image-gallery.php
 * Use arrow keys to navigate, ESC to exit.
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Image;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\UI;

// Sample images (in a real app, these would be actual image paths)
$sampleImages = [
    [
        'name' => 'Logo',
        'path' => __DIR__ . '/../docs/tui-logo.svg',
        'description' => 'TUI Framework Logo',
    ],
    [
        'name' => 'Sample 1',
        'path' => '/path/to/sample1.png',
        'description' => 'First sample image',
    ],
    [
        'name' => 'Sample 2',
        'path' => '/path/to/sample2.png',
        'description' => 'Second sample image',
    ],
    [
        'name' => 'From URL',
        'url' => 'https://via.placeholder.com/400x300.png',
        'description' => 'Placeholder image from URL',
    ],
];

class ImageGallery extends UI
{
    /** @var array<int, array{name: string, path?: string, url?: string, description: string}> */
    private array $images;

    public function __construct(array $images)
    {
        $this->images = $images;
    }

    public function build(): Component
    {
        [$index, $setIndex] = $this->state(0);
        [$showInfo, $setShowInfo] = $this->state(true);

        $imageCount = count($this->images);

        $this->onKeyPress(function ($input, $key) use ($index, $setIndex, $showInfo, $setShowInfo, $imageCount) {
            if ($key->escape) {
                $this->exit();
                return;
            }

            // Navigation
            if ($key->rightArrow || $input === 'l' || $input === 'n') {
                $setIndex(fn ($i) => ($i + 1) % $imageCount);
            } elseif ($key->leftArrow || $input === 'h' || $input === 'p') {
                $setIndex(fn ($i) => ($i - 1 + $imageCount) % $imageCount);
            } elseif ($key->home) {
                $setIndex(0);
            } elseif ($key->end) {
                $setIndex($imageCount - 1);
            }

            // Toggle info
            if ($input === 'i') {
                $setShowInfo(fn ($s) => !$s);
            }
        });

        $currentImage = $this->images[$index];
        $isSupported = Image::isSupported();

        return new Box([
            new BoxColumn([
                // Header
                new BoxRow([
                    (new Text('Image Gallery'))->bold()->color(Color::Cyan),
                    (new Box())->flexGrow(1),
                    (new Text(sprintf('[%d/%d]', $index + 1, $imageCount)))->dim(),
                ]),

                new Text(''),

                // Image display area
                (new Box([
                    $this->renderImage($currentImage),
                ]))->border('round')->padding(1),

                new Text(''),

                // Image info (toggleable)
                $showInfo ? $this->renderImageInfo($currentImage, $isSupported) : new Text(''),

                // Controls
                new Text(''),
                new BoxRow([
                    (new Text('←/→'))->bold()->dim(),
                    (new Text(' Navigate  '))->dim(),
                    (new Text('i'))->bold()->dim(),
                    (new Text(' Toggle info  '))->dim(),
                    (new Text('ESC'))->bold()->dim(),
                    (new Text(' Exit'))->dim(),
                ]),
            ]),
        ]);
    }

    /**
     * @param array{name: string, path?: string, url?: string, description: string} $imageData
     */
    private function renderImage(array $imageData): Image
    {
        if (isset($imageData['url'])) {
            return Image::fromUrl($imageData['url'])
                ->size(50, 15)
                ->alt($imageData['name']);
        }

        return Image::fromPath($imageData['path'] ?? '')
            ->size(50, 15)
            ->alt($imageData['name']);
    }

    /**
     * @param array{name: string, path?: string, url?: string, description: string} $imageData
     */
    private function renderImageInfo(array $imageData, bool $graphicsSupported): BoxColumn
    {
        $source = $imageData['url'] ?? $imageData['path'] ?? 'N/A';
        $sourceType = isset($imageData['url']) ? 'URL' : 'File';

        return new BoxColumn([
            (new Text($imageData['name']))->bold(),
            (new Text($imageData['description']))->dim(),
            new Text(''),
            new BoxRow([
                (new Text('Source: '))->dim(),
                new Text($sourceType),
            ]),
            new BoxRow([
                (new Text('Path: '))->dim(),
                new Text(strlen($source) > 40 ? '...' . substr($source, -37) : $source),
            ]),
            new BoxRow([
                (new Text('Graphics: '))->dim(),
                $graphicsSupported
                    ? (new Text('Kitty protocol'))->color(Color::Green)
                    : (new Text('Fallback mode'))->color(Color::Yellow),
            ]),
        ]);
    }
}

(new ImageGallery($sampleImages))->run();
