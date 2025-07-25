<?php

namespace App\Traits;

trait ApiResponseTrait
{
    protected function successResponse($data = null, $message = 'Success', $code = 200, $meta = [])
    {
        $response = [
            'status' => $code,
            'message' => $message,
            'data' => $data
        ];
    
        if (!empty($meta)) {
            $response['meta'] = $meta;
        }
    
        return response()->json($response, $code);
    }
    

    protected function errorResponse($message = 'Something went wrong', $code = 500, $errors = [])
    {
        return response()->json([
            'status' => $code,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }
}
