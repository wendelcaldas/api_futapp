<?php

namespace App\Http\Helpers;

class ApiResponse
{
    public static function success($code = 200, $message = 'Operação realizada com sucesso', $content = null)
    {
        return response()->json([
            'code' => $code,
            'message' => $message,
            'content' => $content
        ], $code, [], JSON_UNESCAPED_UNICODE);
    }

    public static function error($code = 400, $message = 'Erro na operação', $content = null)
    {
        return response()->json([
            'code' => $code,
            'message' => $message,
            'content' => $content
        ], $code, [], JSON_UNESCAPED_UNICODE);
    }
}