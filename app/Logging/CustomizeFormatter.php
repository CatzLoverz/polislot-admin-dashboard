<?php

namespace App\Logging;

use App\Logging\Processors\ScrubAndTraceProcessor;
use Monolog\Formatter\LineFormatter;

class CustomizeFormatter
{
    /**
     * Customize the given logger instance.
     *
     * @param  \Illuminate\Log\Logger  $logger
     * @return void
     */
    public function __invoke($logger)
    {
        // Standarisasi format log agar rapi dan teratur
        $format = "[%datetime%] %channel%.%level_name%: %message% | Context: %context% | Tracer: %extra%\n";
        $formatter = new LineFormatter($format, 'Y-m-d H:i:s', true, true);

        foreach ($logger->getHandlers() as $handler) {
            // Pasang processor untuk scrub & trace
            $handler->pushProcessor(new ScrubAndTraceProcessor());
            
            // Set format rapi untuk semua file log
            $handler->setFormatter($formatter);
        }
    }
}
