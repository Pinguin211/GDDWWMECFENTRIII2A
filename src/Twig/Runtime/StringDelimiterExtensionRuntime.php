<?php

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;

class StringDelimiterExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct()
    {
        // Inject dependencies if needed
    }

    public function delimitString(string $value, int $maxlenght = 500)
    {
        if (strlen($value) > $maxlenght)
            return substr($value, 0, $maxlenght - 3) . "...";
        else
            return $value;
    }
}
