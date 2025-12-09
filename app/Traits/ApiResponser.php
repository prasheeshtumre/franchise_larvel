<?php

namespace App\Traits;

trait ApiResponser
{
    protected function successResponse($data, $message = 'Success', $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }
    protected function errorResponse($message = null, $code , $data = null)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data
        ], $code);
    }
    public function validationErrorResponse($errors, $message = 'Validation error', $code = 422)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors
        ], $code);
    }

    public function notFoundResponse($message = 'Resource not found', $code = 404)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message
        ], $code);
    }
}
