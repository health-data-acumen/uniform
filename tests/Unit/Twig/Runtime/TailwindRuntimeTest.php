<?php

namespace App\Tests\Unit\Twig\Runtime;

use App\Twig\Runtime\TailwindRuntime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Twig\Extension\RuntimeExtensionInterface;

class TailwindRuntimeTest extends TestCase
{
    public function testImplementsRuntimeExtensionInterface(): void
    {
        $runtime = new TailwindRuntime();

        $this->assertInstanceOf(RuntimeExtensionInterface::class, $runtime);
    }

    public function testMergeReturnsString(): void
    {
        $runtime = new TailwindRuntime();

        $result = $runtime->merge('bg-red-500');

        $this->assertIsString($result);
    }

    public function testMergeCombinesClasses(): void
    {
        $runtime = new TailwindRuntime();

        $result = $runtime->merge('p-4', 'm-2');

        $this->assertStringContainsString('p-4', $result);
        $this->assertStringContainsString('m-2', $result);
    }

    public function testMergeHandlesNullValues(): void
    {
        $runtime = new TailwindRuntime();

        $result = $runtime->merge('bg-blue-500', null, 'text-white');

        $this->assertStringContainsString('bg-blue-500', $result);
        $this->assertStringContainsString('text-white', $result);
    }

    public function testMergeRemovesConflictingClasses(): void
    {
        $runtime = new TailwindRuntime();

        $result = $runtime->merge('bg-red-500', 'bg-blue-500');

        $this->assertStringContainsString('bg-blue-500', $result);
        $this->assertStringNotContainsString('bg-red-500', $result);
    }

    public function testMergePreservesLastOccurrenceOnConflict(): void
    {
        $runtime = new TailwindRuntime();

        $result = $runtime->merge('p-2', 'p-4');

        $this->assertStringContainsString('p-4', $result);
        $this->assertStringNotContainsString('p-2 ', $result);
    }

    public function testMergeWithEmptyStrings(): void
    {
        $runtime = new TailwindRuntime();

        $result = $runtime->merge('', 'bg-red-500', '');

        $this->assertStringContainsString('bg-red-500', $result);
    }

    public function testMergeWithSingleClass(): void
    {
        $runtime = new TailwindRuntime();

        $result = $runtime->merge('flex');

        $this->assertSame('flex', $result);
    }

    public function testMergeWithNoArguments(): void
    {
        $runtime = new TailwindRuntime();

        $result = $runtime->merge();

        $this->assertSame('', $result);
    }

    public function testConstructorUsesFallbackCache(): void
    {
        $runtime = new TailwindRuntime(null);

        $result = $runtime->merge('test-class');

        $this->assertStringContainsString('test-class', $result);
    }

    public function testConstructorUsesProvidedCache(): void
    {
        $cache = new ArrayAdapter();

        $runtime = new TailwindRuntime($cache);

        $result = $runtime->merge('custom-class');

        $this->assertStringContainsString('custom-class', $result);
    }

    public function testMergeWithMultipleClassesInOneString(): void
    {
        $runtime = new TailwindRuntime();

        $result = $runtime->merge('flex items-center', 'justify-between');

        $this->assertStringContainsString('flex', $result);
        $this->assertStringContainsString('items-center', $result);
        $this->assertStringContainsString('justify-between', $result);
    }
}
