<?php /** @noinspection PhpIllegalPsrClassPathInspection */

if ( ! function_exists('dlog')) {
    /**
     * For quick debugging without dependencies
     *
     * @param mixed $data
     * @param string|null $label
     * @param bool $once
     * @return void
     */
    function dlog(mixed $data, ?string $label = null, bool $once = false): void
    {
        $logFile = __DIR__ . '/../var/log/dev.log';

        if (is_bool($data)) $data = var_export($data, true);

        $timestamp = date('Y-m-d H:i:s');
        $output    = $label ? "[$timestamp] [$label] " : "[$timestamp] ";
        $output    .= is_scalar($data) ? $data : print_r($data, true);
        $output    .= PHP_EOL;

        if ($once) {
            if (DlogOnceChecker::isLogged($label)) {
                return;
            }

            DlogOnceChecker::setLogged($label);
        }

        error_log($output, 3, $logFile);
    }
}

class DlogOnceChecker
{
    public static array $logged = [];

    public static function setLogged(string $label): void
    {
        if ( ! isset(self::$logged[$label])) {
            self::$logged[$label] = true;
        }
    }

    public static function isLogged(string $label): bool
    {
        return isset(self::$logged[$label]);
    }
}
