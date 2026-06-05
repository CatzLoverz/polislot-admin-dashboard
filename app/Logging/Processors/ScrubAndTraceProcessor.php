<?php

namespace App\Logging\Processors;

use Illuminate\Support\Str;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Exception;

class ScrubAndTraceProcessor implements ProcessorInterface
{
    /**
     * Keys to scrub from log contexts to prevent credential leaks.
     */
    protected array $scrubKeys = [
        'password',
        'password_confirmation',
        'token',
        'api_key',
        'secret',
        'authorization',
        'payload',
        'x-auth-token',
        'x-session-key',
    ];

    /**
     * Process the log record.
     *
     * @param  LogRecord|array  $record  (Monolog 3 passes LogRecord, Monolog 2 passes array)
     * @return LogRecord|array
     */
    public function __invoke($record)
    {
        // Handle both Monolog 2 (array) and Monolog 3 (LogRecord)
        $isMonolog3 = $record instanceof LogRecord;

        $context = $isMonolog3 ? $record->context : $record['context'];
        $extra = $isMonolog3 ? $record->extra : $record['extra'];
        $message = $isMonolog3 ? $record->message : $record['message'];

        // 1. Scrub Context Data
        $context = $this->scrubArray($context);

        // 2. Add Tracing Data (Repudiation)
        if (app()->runningInConsole()) {
            $extra['environment'] = 'CLI';
            // You can add more CLI specific data if needed
        } else {
            $request = request();
            $extra['environment'] = 'HTTP';
            if ($request) {
                $extra['ip'] = $request->ip();
                $extra['user_agent'] = $request->userAgent();
                $extra['url'] = $request->fullUrl();
                $extra['method'] = $request->method();
                
                // Try to get user ID safely
                try {
                    $extra['user_id'] = auth()->check() ? auth()->id() : 'guest';
                } catch (Exception $e) {
                    $extra['user_id'] = 'unknown';
                }
            }
        }
        
        // Include unique request ID for tracing across multiple logs in same request
        $extra['request_id'] = $this->getRequestId();

        // 3. Auto-Format Message Prefix
        $message = $this->injectPrefix((string) $message);

        // 4. Optional: Filter out excessively long "spam" string logs
        // e.g., if message itself is a giant base64 or json string
        if (is_string($message) && strlen($message) > 5000) {
            $message = substr($message, 0, 500) . '... [TRUNCATED DUE TO SPAM FILTER]';
        }

        if ($isMonolog3) {
            return $record->with(
                message: $message,
                context: $context,
                extra: $extra
            );
        }

        $record['context'] = $context;
        $record['extra'] = $extra;
        $record['message'] = $message;

        return $record;
    }

    /**
     * Recursively scrub sensitive keys from an array.
     */
    protected function scrubArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->scrubArray($value);
            } elseif (is_string($key) && $this->shouldScrub($key)) {
                $data[$key] = '[SCRUBBED]';
            }
        }

        return $data;
    }

    /**
     * Check if a key should be scrubbed.
     */
    protected function shouldScrub(string $key): bool
    {
        $normalizedKey = strtolower($key);
        foreach ($this->scrubKeys as $scrubKey) {
            if (Str::contains($normalizedKey, strtolower($scrubKey))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get or generate a unique request ID for the lifecycle.
     */
    protected function getRequestId(): string
    {
        if (app()->bound('request_id')) {
            return app('request_id');
        }

        $requestId = (string) Str::uuid();
        app()->instance('request_id', $requestId);

        return $requestId;
    }

    /**
     * Inject the automatically generated [PLATFORM Class@method] prefix into the message.
     */
    protected function injectPrefix(string $message): string
    {
        // Temukan dari mana log ini dipanggil
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15);
        $caller = null;

        // Cari class pertama yang bukan bawaan Illuminate\Log, Facades, atau Monolog
        foreach ($trace as $frame) {
            if (isset($frame['class'])) {
                $class = $frame['class'];
                if (!Str::startsWith($class, [
                    'Illuminate\Log', 
                    'Illuminate\Support\Facades', 
                    'Monolog\\', 
                    'App\Logging'
                ])) {
                    $caller = $frame;
                    break;
                }
            }
        }

        if (!$caller) {
            return $message; // Fallback jika tidak ditemukan
        }

        $className = class_basename($caller['class']);
        $methodName = $caller['function'] ?? 'unknown';

        // Tentukan platform berdasarkan namespace
        $platform = 'APP';
        $namespace = $caller['class'];

        if (Str::contains($namespace, 'App\Http\Controllers\Api')) {
            $platform = 'API';
        } elseif (Str::contains($namespace, 'App\Http\Controllers\Web')) {
            $platform = 'WEB';
        } elseif (Str::contains($namespace, 'App\Console')) {
            $platform = 'CLI';
        } elseif (Str::contains($namespace, 'App\Services')) {
            $platform = 'SERVICE';
        } elseif (Str::contains($namespace, 'App\Models')) {
            $platform = 'MODEL';
        } elseif (Str::contains($namespace, 'App\Http\Middleware')) {
            $platform = 'MIDDLEWARE';
        }

        $generatedPrefix = "[{$platform} {$className}@{$methodName}]";

        // Hilangkan prefix manual dari user jika sudah ada (contoh: "[WEB Controller@method] " atau "[API Controller]")
        $message = preg_replace('/^\[.*?\]\s*/', '', trim($message));

        return "{$generatedPrefix} {$message}";
    }
}
