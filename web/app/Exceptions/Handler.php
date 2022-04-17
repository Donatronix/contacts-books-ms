<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof UnableToExecuteRequestException) {
            return new Response(json_encode(['errors' => [$exception->getMessage()]]), $exception->getCode());
        }

        if ($exception instanceof NotFoundHttpException) {
            return new Response(json_encode(['errors' => ['Resource not found']]), 404);
        }

        if ($exception instanceof ModelNotFoundException) {
            $classFullName = $exception->getModel();
            $className = substr($classFullName, strrpos($classFullName, '\\') + 1);

            return response()->json(config('constants.errors.' . $className . 'NotFound'), 404);
        }

        if ($exception instanceof ValidationException) {
            return response()->json([
                'message' => 'YOUR CUSTOM MESSAGE HERE',
                'errors' => $exception->validator->getMessageBag(),
                $exception->errors(),
                $exception->status
            ], 422);
        }

        return parent::render($request, $exception);
    }
}
