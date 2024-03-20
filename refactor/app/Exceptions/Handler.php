<?php

namespace App\Exceptions;

use App\Helpers\TransformHelper;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
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

    public function render($request, \Throwable $e)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            $status_code = $this->getStatusCode($e);
            
            return TransformHelper::failedResponse($status_code, $e->getMessage());
        }

        return parent::render($request, $e);
    }

    private function getStatusCode($exception)
    {
        return method_exists($exception, 'getStatusCode')
            ? $exception->getStatusCode()
            : 400;
    }
}
