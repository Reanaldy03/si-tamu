<?php

namespace App\Application\Reporting\Services;

class FormulaSanitizer
{
    /**
     * Dangerous characters that can trigger formula execution in spreadsheet software.
     *
     * @var list<string>
     */
    private const DANGEROUS_PREFIXES = ['=', '+', '-', '@', "\t", "\r"];

    /**
     * Sanitize a cell value to prevent spreadsheet formula injection.
     */
    public static function sanitize(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        $str = (string) $value;
        if ($str === '') {
            return '';
        }

        $firstChar = $str[0];
        if (in_array($firstChar, self::DANGEROUS_PREFIXES, true)) {
            return "'".$str;
        }

        return $str;
    }
}
