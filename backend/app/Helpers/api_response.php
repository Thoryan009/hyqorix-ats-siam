<?php

use Illuminate\Http\JsonResponse;

if (! function_exists('apiSuccess')) {
    function apiSuccess(
        mixed $data = null,
        string $action = 'fetched',
        int $statusCode = 200,
        string $entity = 'Record'
    ): JsonResponse {

        $messages = [
            'fetched' => "{$entity} fetched successfully.",
            'created' => "{$entity} created successfully.",
            'updated' => "{$entity} updated successfully.",
            'deleted' => "{$entity} deleted successfully.",

        ];

        return response()->json([
            'data'    => $data,
            'message' => $messages[$action] ?? "{$entity} processed successfully.",
            'success' => true,
        ], $statusCode);
    }
}
