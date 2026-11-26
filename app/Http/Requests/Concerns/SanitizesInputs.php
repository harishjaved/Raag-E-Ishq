<?php

namespace App\Http\Requests\Concerns;

trait SanitizesInputs
{
    protected function sanitizeString(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $cleaned = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $value);
        return strip_tags(trim((string) $cleaned));
    }
}
