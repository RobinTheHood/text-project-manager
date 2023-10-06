<?php

namespace RobinTheHood\TextProjectManager\Helpers;

class StringFilter
{
    public function extractTextBetweenMarkers(string $string, string $marker): string
    {
        $startPos = strpos($string, $marker);
        $endPos = strrpos($string, $marker);

        if ($startPos === false || $endPos === false || $startPos === $endPos) {
            return '';
        }

        $startPos += strlen($marker);
        $length = $endPos - $startPos;

        return substr($string, $startPos, $length);
    }
}
