<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Exceptions;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Support\Exceptions\ExtensionNotLoadedException;
use Xocdr\Tui\Support\Exceptions\RenderException;
use Xocdr\Tui\Support\Exceptions\TuiException;
use Xocdr\Tui\Support\Exceptions\ValidationException;

class ExceptionsTest extends TestCase
{
    public function testTuiExceptionIsBaseException(): void
    {
        $exception = new TuiException('Test error');

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertEquals('Test error', $exception->getMessage());
    }

    public function testExtensionNotLoadedExceptionHasDefaultMessage(): void
    {
        $exception = new ExtensionNotLoadedException();

        $this->assertInstanceOf(TuiException::class, $exception);
        $this->assertStringContainsString('ext-tui', $exception->getMessage());
    }

    public function testExtensionNotLoadedExceptionAcceptsCustomMessage(): void
    {
        $exception = new ExtensionNotLoadedException('Custom message');

        $this->assertEquals('Custom message', $exception->getMessage());
    }

    public function testRenderExceptionWithoutComponentName(): void
    {
        $exception = new RenderException('Something went wrong');

        $this->assertInstanceOf(TuiException::class, $exception);
        $this->assertEquals('Something went wrong', $exception->getMessage());
        $this->assertNull($exception->getComponentName());
    }

    public function testRenderExceptionWithComponentName(): void
    {
        $exception = new RenderException('Failed to render', 'MyComponent');

        $this->assertEquals('Render error in MyComponent: Failed to render', $exception->getMessage());
        $this->assertEquals('MyComponent', $exception->getComponentName());
    }

    public function testRenderExceptionWithPreviousException(): void
    {
        $previous = new \RuntimeException('Original error');
        $exception = new RenderException('Wrapper error', null, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testValidationExceptionWithoutErrors(): void
    {
        $exception = new ValidationException('Validation failed');

        $this->assertInstanceOf(TuiException::class, $exception);
        $this->assertEquals('Validation failed', $exception->getMessage());
        $this->assertEmpty($exception->getErrors());
    }

    public function testValidationExceptionWithErrors(): void
    {
        $errors = [
            'width' => 'Must be positive',
            'height' => 'Must be an integer',
        ];
        $exception = new ValidationException('Validation failed', $errors);

        $this->assertEquals($errors, $exception->getErrors());
        $this->assertStringContainsString('width: Must be positive', $exception->getMessage());
        $this->assertStringContainsString('height: Must be an integer', $exception->getMessage());
    }

    public function testValidationExceptionGetError(): void
    {
        $errors = ['field' => 'Error message'];
        $exception = new ValidationException('Failed', $errors);

        $this->assertEquals('Error message', $exception->getError('field'));
        $this->assertNull($exception->getError('nonexistent'));
    }

    public function testValidationExceptionHasError(): void
    {
        $errors = ['field' => 'Error message'];
        $exception = new ValidationException('Failed', $errors);

        $this->assertTrue($exception->hasError('field'));
        $this->assertFalse($exception->hasError('nonexistent'));
    }
}
