<?php
declare(strict_types=1);

namespace App\Service;


final class StringUtils
{
    public static function stripPrefix(string $text): string
    {
        $prefixes = collect([
            'fr' => ['Le ', 'La ', 'Les ', 'L\''],
            'en' => ['The '],
            'es' => ['El ', 'La ', 'Los ']
        ])
            ->values()
            ->flatten()
            ->unique()
        ;

        foreach ($prefixes as $prefix)
        {
            if (str_starts_with(strtolower($text), strtolower($prefix))) {
                return substr($text, strlen($prefix)) . ' (' . trim($prefix) . ')';
            }
        }

        return $text;
    }
}
