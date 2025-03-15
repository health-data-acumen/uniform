<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\TailwindRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TailwindExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('tw_merge', [TailwindRuntime::class, 'merge']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('tw_merge', [TailwindRuntime::class, 'merge']),
        ];
    }
}
