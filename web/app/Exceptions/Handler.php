<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
     * @param Throwable $e
     *
     * @return void
     *
     * @throws Exception
     */
    public function report(Throwable $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Throwable $e
     *
     * @return Response|JsonResponse
     *
     * @throws Throwable
     */
    public function render($request, Throwable $e): Response|JsonResponse
    {
        if ($e instanceof ModelNotFoundException) {
            $classFullName = $e->getModel();
            $className = substr($classFullName, strrpos($classFullName, '\\') + 1);

            return response()->json(config('constants.errors.' . $className . 'NotFound'), 404);
        }

        if ($e instanceof ValidationException) {
            return response()->json([
                'message' => 'YOUR CUSTOM MESSAGE HERE',
                'errors' => $e->validator->getMessageBag(),
                $e->errors(),
                $e->status
            ], 422);
        }

        return parent::render($request, $e);
    }
}
