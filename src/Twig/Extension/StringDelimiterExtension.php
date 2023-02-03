<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\StringDelimiterExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class StringDelimiterExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('delimitString', [StringDelimiterExtensionRuntime::class, 'delimitString']),
        ];
    }
}
