<?php

declare(strict_types=1);

namespace App\Twig\Runtime;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Contracts\Cache\CacheInterface;
use TailwindMerge\Factory;
use TailwindMerge\TailwindMerge;
use Twig\Extension\RuntimeExtensionInterface;

class TailwindRuntime implements RuntimeExtensionInterface
{
    private Factory $factory;

    public function __construct(?CacheInterface $cache = null)
    {
        $cache ??= new FilesystemAdapter();

        $this->factory = TailwindMerge::factory()->withCache(new Psr16Cache($cache));
    }

    public function merge(?string ...$classes): string
    {
        return $this->factory
            ->make()
            ->merge($classes)
        ;
    }
}
