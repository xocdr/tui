<?php
/**
 * Debug the Tui rendering chain
 */

require __DIR__ . '/vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Tui;

echo "Testing Tui::render() chain...\n";

// Check extension
if (!extension_loaded('tui')) {
    die("Extension not loaded\n");
}
echo "Extension loaded: OK\n";

// Check interactive
if (!tui_is_interactive()) {
    die("Not interactive terminal\n");
}
echo "Interactive: OK\n";

// Try direct extension call first
echo "\nTesting direct tui_render()...\n";

try {
    $instance = tui_render(function($inst) {
        echo "Direct callback called\n";
        $box = new \Xocdr\Tui\Ext\Box(['width' => 40, 'height' => 3]);
        $box->children = [new \Xocdr\Tui\Ext\Text("Direct Test")];
        return $box;
    }, ['fullscreen' => true, 'exitOnCtrlC' => true]);

    echo "Instance created: " . get_class($instance) . "\n";
    $instance->waitUntilExit();
    echo "Exited\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
