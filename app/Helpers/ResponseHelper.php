<?php

namespace App\Helpers;

class ResponseHelper
{
    /**
     * Success JSON response
     */
    public static function success($data = null, $message = 'Success', $code = 200)
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    /**
     * Error JSON response
     */
    public static function error($message = 'Error', $code = 400, $errors = null)
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
            'errors'  => $errors,
        ], $code);
    }

    /**
     * Redirect with flash message
     */
    public static function redirectWith($route, $type = 'success', $message = '')
    {
        return redirect()->route($route)->with($type, $message);
    }
}