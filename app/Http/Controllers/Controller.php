<?php

namespace App\Http\Controllers;

use App\Domain\Common\Enums\ErrorMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class Controller
{
    protected function successResponse(mixed $data = null, ?string $message = null, int $status = 200): JsonResponse
    {
        $response = [];

        if ($message !== null) {
            $response['message'] = $message;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    protected function errorResponse(string $message, int $status = 400, ?array $errors = null): JsonResponse
    {
        $response = [
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    protected function handleException(Throwable $e, string $context = 'Operation'): JsonResponse
    {
        Log::error("{$context} failed", [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        $message = app()->environment('production')
            ? ErrorMessage::SERVER_ERROR->value
            : $e->getMessage();

        return $this->errorResponse($message, 500);
    }

    protected function paginatedResponse($paginator, ?string $resourceClass = null): JsonResponse
    {
        $items = $paginator->items();

        if ($resourceClass !== null) {
            $items = $resourceClass::collection($items);
        }

        return response()->json([
            'data' => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }
}
