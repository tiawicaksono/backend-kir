<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $levels = [];

    protected $dontReport = [];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
    public function render($request, \Throwable $exception)
    {
        if ($exception instanceof ValidationException) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $exception->errors()
            ], 422);
        }

        if ($request->expectsJson() && $exception instanceof \Illuminate\Auth\AuthenticationException) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        return parent::render($request, $exception);
    }
}
