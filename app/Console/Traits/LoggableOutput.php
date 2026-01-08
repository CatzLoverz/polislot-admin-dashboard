<?php

namespace App\Console\Traits;

use Illuminate\Support\Facades\Log;

trait LoggableOutput
{
    /**
     * Write a string as information output (CLI Only).
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function info($string, $verbosity = null)
    {
        // Default info: CLI only to prevent log spam
        parent::info($string, $verbosity);
    }

    /**
     * Write a string as information output and log it to file.
     * Use this for "Success" or important result messages.
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function logInfo($string, $verbosity = null)
    {
        $context = class_basename($this);
        Log::info("{$context}: {$string}");
        parent::info($string, $verbosity);
    }

    /**
     * Write a string as warning output (CLI Only).
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function warn($string, $verbosity = null)
    {
        // Warnings (like 'no files found') often don't need to clog logs
        parent::warn($string, $verbosity);
    }

    /**
     * Write a string as error output and log it.
     *
     * @param  string  $string
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function error($string, $verbosity = null)
    {
        $context = class_basename($this);
        Log::error("{$context}: {$string}");
        parent::error($string, $verbosity);
    }
}
