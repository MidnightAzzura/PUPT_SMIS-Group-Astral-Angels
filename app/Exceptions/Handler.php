<?php 

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Throwable;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof AuthorizationException || $exception instanceof PermissionDoesNotExist) {
            return response()->view('errors.403', ['message' => $exception->getMessage()], 403);
        }

        return parent::render($request, $exception);
    }
  }