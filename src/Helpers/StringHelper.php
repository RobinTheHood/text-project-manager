<?php

namespace RobinTheHood\TextProjectManager\Helpers;

class StringHelper
{
    /**
     * Trennt einen String anhand von ; auf und trimmt jeden Teil.
     */
    public static function getTrimmedLineParts(string $string, string $separator): array
    {
        $stringParts = explode($separator, $string);
        $trimmedStringParts = array_map('trim', $stringParts);
        return $trimmedStringParts;
    }

    /**
     * Gibt den Rest eines Strings zurück und überspringt eine
     * beliebige Anzahl am Anfang des Strings.
     */
    public static function skipLetters(string $string, int $count): string
    {
        return substr($string, $count, strlen($string) - $count);
    }

    public static function startsWith(string $haystack, string $needle)
    {
        return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}
