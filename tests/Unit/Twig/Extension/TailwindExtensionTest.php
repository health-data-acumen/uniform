<?php

namespace App\Tests\Unit\Twig\Extension;

use App\Twig\Extension\TailwindExtension;
use App\Twig\Runtime\TailwindRuntime;
use PHPUnit\Framework\TestCase;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TailwindExtensionTest extends TestCase
{
    private TailwindExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new TailwindExtension();
    }

    public function testGetFiltersReturnsTwMergeFilter(): void
    {
        $filters = $this->extension->getFilters();

        $this->assertCount(1, $filters);
        $this->assertInstanceOf(TwigFilter::class, $filters[0]);
        $this->assertSame('tw_merge', $filters[0]->getName());
    }

    public function testGetFunctionsReturnsTwMergeFunction(): void
    {
        $functions = $this->extension->getFunctions();

        $this->assertCount(1, $functions);
        $this->assertInstanceOf(TwigFunction::class, $functions[0]);
        $this->assertSame('tw_merge', $functions[0]->getName());
    }

    public function testFilterCallablePointsToTailwindRuntime(): void
    {
        $filters = $this->extension->getFilters();
        $filter = $filters[0];

        $callable = $filter->getCallable();

        $this->assertIsArray($callable);
        $this->assertSame(TailwindRuntime::class, $callable[0]);
        $this->assertSame('merge', $callable[1]);
    }

    public function testFunctionCallablePointsToTailwindRuntime(): void
    {
        $functions = $this->extension->getFunctions();
        $function = $functions[0];

        $callable = $function->getCallable();

        $this->assertIsArray($callable);
        $this->assertSame(TailwindRuntime::class, $callable[0]);
        $this->assertSame('merge', $callable[1]);
    }

    public function testExtensionReturnsArrayOfFilters(): void
    {
        $filters = $this->extension->getFilters();

        $this->assertIsArray($filters);
        foreach ($filters as $filter) {
            $this->assertInstanceOf(TwigFilter::class, $filter);
        }
    }

    public function testExtensionReturnsArrayOfFunctions(): void
    {
        $functions = $this->extension->getFunctions();

        $this->assertIsArray($functions);
        foreach ($functions as $function) {
            $this->assertInstanceOf(TwigFunction::class, $function);
        }
    }
}
