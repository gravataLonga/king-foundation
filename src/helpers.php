<?php

namespace Gravatalonga\KingFoundation {

    use Closure;

    if (! function_exists('env')) {
        function env(string $key, mixed $default = null): mixed
        {
            $value = getenv($key);

            if ($value === false) {
                return value($default);
            }

            switch (strtolower($value)) {
                case 'true':
                case '(true)':
                    return true;

                case 'false':
                case '(false)':
                    return false;

                case 'empty':
                case '(empty)':
                    return '';

                case 'null':
                case '(null)':
                    return;
            }

            if (strlen($value) > 1 && starts_with($value, '"') && ends_with($value, '"')) {
                return substr($value, 1, -1);
            }

            return $value;
        }
    }

    if (! function_exists('value')) {
        function value($value): mixed
        {
            return $value instanceof Closure ? $value() : $value;
        }
    }

    if (! function_exists('starts_with')) {
        function starts_with(string $haystack, string $needles): bool
        {
            foreach ((array) $needles as $needle) {
                if ($needle != '' && substr($haystack, 0, strlen($needle)) === (string) $needle) {
                    return true;
                }
            }

            return false;
        }
    }

    if (! function_exists('ends_with')) {
        function ends_with(string $haystack, string $needles): bool
        {
            foreach ((array) $needles as $needle) {
                if (substr($haystack, -strlen($needle)) === (string) $needle) {
                    return true;
                }
            }

            return false;
        }
    }
}