<?php

namespace App\Http\Controllers;

use Illuminate\Http\{JsonResponse};
use Illuminate\Support\Facades\Http;

class EnvioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $endpoint = env('API_MELHOR_ENVIO') . '/' . 'me/cart';

            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . env('TOKEN_MELHOR_ENVIO_SANBOX'),
            ])->get($endpoint);

            if ($response->failed()) {
                throw new \Exception($response->body());
            }

            return $response->json(); // This returns an array
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
