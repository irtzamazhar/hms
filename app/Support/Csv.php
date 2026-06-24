<?php

namespace App\Support;

class Csv
{
    /**
     * Neutralise spreadsheet formula injection.
     *
     * A cell whose text begins with = + - @ (or tab/CR) is executed as a
     * formula by Excel/Sheets. Prefixing a single quote forces it to be
     * treated as literal text (the quote itself is not displayed).
     */
    public static function safe(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}
