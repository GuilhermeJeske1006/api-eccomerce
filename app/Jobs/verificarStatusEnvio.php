<?php

namespace App\Jobs;

use App\Models\{EnvioPedido, User};
use App\Services\EnvioService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};

class verificarStatusEnvio implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $envios = EnvioPedido::where('status', 'released')->get();

        $codigoRastreio = [];

        foreach ($envios as $item) {
            $codigoRastreio[] = $item->codigo_rastreio;
        }

        $envioService = EnvioService::rastrearEnvio($codigoRastreio);

        foreach ($envioService as $key => $value) {

            $envio = EnvioPedido::where('codigo_rastreio', $key)
            ->first();

            $usuario = User::find($envio->pedido->usuario_id);

            if ($envio) {
                $envio['status'] = $value['status'];
                $envio->save();

                if ($value['status'] == 'posted') {
                    EnviarPedidoPostado::dispatch($envio, $usuario);
                }

                if ($value['status'] == 'delivered') {
                    EnviarPedidoEntregue::dispatch($envio, $usuario);
                }
            }
        }
    }
}
