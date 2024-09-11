<?php

namespace App\Http\Controllers\Status;

use App\Http\Controllers\Controller;
use App\Models\StatusEnvio;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{Redis};

class StatusEnvioController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/status-envio",
     *     operationId="getStatusEnvio",
     *     tags={"StatusEnvio"},
     *     summary="Retrieve all status envio data",
     *     description="Fetches all status envio records and caches them in Redis for 1 hour.",
     *     @OA\Response(
     *         response=200,
     *         description="A list of status envio records",
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
        $statusEnvio = getOrSetCache('status_envio', 3600, function () {
            return StatusEnvio::all();
        });

        return response()->json($statusEnvio);
    }

}
