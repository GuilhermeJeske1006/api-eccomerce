<?php

namespace App\Http\Controllers\Pedidos;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __invoke(Request $request): \Illuminate\Http\JsonResponse
    {
        if (isset($request->charges[0]['status'])) {
            $pedido = Pedido::where('reference', $request->reference_id)->first();

            if (!$pedido) {
                Log::error('Pedido não encontrado', $request->all());

                return response()->json(['status' => 'error', 'message' => 'Pedido não encontrado'], 404);
            }

            Log::info('Webhook recebido', $request->all());

            $pedido->update([
                'status' => $request->charges[0]['status'],
            ]);

            return response()->json(['status' => 'ok', 'pedido' => $pedido], 200);
        }

        // Default response if the condition is not met
        return response()->json(['status' => 'error', 'message' => 'Status not found in request'], 400);
    }
}
