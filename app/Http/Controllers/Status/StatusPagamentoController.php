<?php

namespace App\Http\Controllers\Status;

use App\Http\Controllers\Controller;
use App\Models\StatusPedido;
use Illuminate\Http\{JsonResponse, Request};

class StatusPagamentoController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/status-pagamento",
     *     operationId="getStatusPagamento",
     *     tags={"StatusPagamento"},
     *     summary="Retrieve all status pagamento data",
     *     description="Fetches all status pagamento records and caches them in Redis for 1 hour.",
     *     @OA\Response(
     *         response=200,
     *         description="A list of status pagamento records",
     *         @OA\JsonContent(
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function __invoke(Request $request): JsonResponse
    {
        $statusPagamento = getOrSetCache('status_pagamento', 3600, function () {
            return StatusPedido::all();
        });

        return response()->json($statusPagamento);
    }
}
