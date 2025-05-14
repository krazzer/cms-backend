<?php
if ( ! function_exists('dlog')) {
    /**
     * For quick debugging without dependencies
     *
     * @param mixed $data
     * @param string|null $label
     * @return void
     */
    function dlog(mixed $data, string $label = null): void
    {
        $logFile = __DIR__ . '/../var/log/dev.log';

        $timestamp = date('Y-m-d H:i:s');
        $output    = $label ? "[$timestamp] [$label] " : "[$timestamp] ";
        $output    .= is_scalar($data) ? $data : print_r($data, true);
        $output    .= PHP_EOL;

        error_log($output, 3, $logFile);
    }
}