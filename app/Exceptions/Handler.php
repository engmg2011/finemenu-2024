<?php

namespace App\Exceptions;

use App\Mail\ExceptionOccurredMail;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Log;
use Mail;
use Throwable;

class Handler extends ExceptionHandler
{

    protected $dontReport = [
        \Illuminate\Validation\ValidationException::class,
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
    ];

    /**
     * Todo ::
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

    // override report method
    public function report(Throwable $e): void
    {
        parent::report($e);

        // Avoid spamming in the local environment
        if (app()->environment('local') || app()->environment('development')) {
            return;
        }

        // Respect $dontReport
        if (! $this->shouldReport($e)) {
            return;
        }

        try {
            $exceptionData = [
                'exceptionMessage' => $e->getMessage() ?? "Exception Message not found",
                'line' => $e->getLine() ?? "Line number not found",
                'trace' => $e->getTraceAsString() ?? "Trace not found",
                'url' => request()?->fullUrl() ?? "URL not found",
                'method' => request()?->method() ?? "Method not found",
                'ip' => request()?->ip() ?? "IP not found",
                'env' => app()->environment() ?? "Environment not found",
            ];
            Mail::to('barq.solutions25@gmail.com')
                ->queue(new ExceptionOccurredMail($exceptionData));

        } catch (Throwable $mailException) {
            Log::error('Failed to send exception email', [
                'mail_error' => $mailException->getMessage(),
            ]);
        }

    }

}
