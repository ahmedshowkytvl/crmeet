<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\View;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // Handle database connection errors
        if ($exception instanceof QueryException) {
            $errorCode = $exception->getCode();
            
            // MySQL connection errors
            if (in_array($errorCode, [2002, 2003, 1045, 1049, 2006])) {
                return $this->renderDatabaseError($request, $exception);
            }
        }

        // Handle PDO exceptions
        if ($exception instanceof \PDOException) {
            $errorCode = $exception->getCode();
            
            // MySQL connection errors
            if (in_array($errorCode, [2002, 2003, 1045, 1049, 2006])) {
                return $this->renderDatabaseError($request, $exception);
            }
        }

        // Handle any exception with database connection error message
        if (str_contains($exception->getMessage(), 'No connection could be made because the target machine actively refused it') ||
            str_contains($exception->getMessage(), 'Connection refused') ||
            str_contains($exception->getMessage(), 'Access denied') ||
            str_contains($exception->getMessage(), 'Unknown database')) {
            return $this->renderDatabaseError($request, $exception);
        }

        // Handle disk space errors
        if (str_contains($exception->getMessage(), 'No space left on device') ||
            str_contains($exception->getMessage(), 'ENOSPC') ||
            str_contains($exception->getMessage(), 'file_put_contents') ||
            str_contains($exception->getMessage(), 'failed with errno=28')) {
            return $this->renderDiskSpaceError($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Render a database connection error page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\QueryException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function renderDatabaseError($request, $exception)
    {
        $errorDetails = [
            'error_code' => $exception->getCode(),
            'error_message' => $exception->getMessage(),
            'sql' => method_exists($exception, 'getSql') ? $exception->getSql() : 'N/A',
            'connection' => method_exists($exception, 'getConnectionName') ? $exception->getConnectionName() : 'mysql',
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'request_url' => $request->fullUrl(),
            'request_method' => $request->method(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'timestamp' => now()->format('Y-m-d H:i:s'),
        ];

        // Set locale based on request parameter or default
        $locale = $request->get('lang', app()->getLocale());
        app()->setLocale($locale);

        return response()->view('errors.500', [
            'error' => $errorDetails,
            'exception' => $exception
        ], 500);
    }

    /**
     * Render a disk space error page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response
     */
    protected function renderDiskSpaceError($request, $exception)
    {
        $errorDetails = [
            'error_type' => 'Disk Space Error',
            'error_message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'request_url' => $request->fullUrl(),
            'request_method' => $request->method(),
            'timestamp' => now()->format('Y-m-d H:i:s'),
        ];

        // Set locale based on request parameter or default
        $locale = $request->get('lang', app()->getLocale());
        app()->setLocale($locale);

        return response()->view('errors.disk-space', [
            'error' => $errorDetails,
            'exception' => $exception
        ], 500);
    }
}
