<?php

namespace App\Support;

class ContentHtml
{
    /** Decode legacy OpenCart HTML once or repeatedly encoded HTML for display only. */
    public static function decode(?string $value): string
    {
        $decoded = $value ?? '';

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $next = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($next === $decoded) break;
            $decoded = $next;
        }

        return $decoded;
    }

    /** A safe, readable one-line preview for every admin list table. */
    public static function preview(?string $value): string
    {
        return trim((string) preg_replace('/\s+/', ' ', strip_tags(self::decode($value))));
    }
}
