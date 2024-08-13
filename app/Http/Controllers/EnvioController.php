<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\PedidoResource;
use App\Models\Pedido;
use App\Services\EnvioService;
use Illuminate\Http\Request;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Http;

class EnvioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
          
            $endpoint = env('API_MELHOR_ENVIO') . '/' . 'me/cart';
    
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . env('TOKEN_MELHOR_ENVIO_SANBOX')
            ])->get($endpoint);
    
            if ($response->failed()) {
                throw new \Exception($response->body());
            }
    
            return $response->json();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // $pedido = EnvioService::inserirFretesCarrinho($request);
        // $pedido = EnvioService::gerarEtiqueta();
        // $pedido = EnvioService::pegarEnvio();
        // $pedido = EnvioService::imprimirEtiqueta();
        //  $pedido = EnvioService::visualizarEtiqueta();
        //  $pedido = EnvioService::rastrearEnvio();

        return response()->json();
    }

}
