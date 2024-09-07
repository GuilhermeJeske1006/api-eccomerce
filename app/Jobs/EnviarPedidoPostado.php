<?php

namespace App\Jobs;

use App\Models\{EnvioPedido, User};
use App\Notifications\sendPedidoPostado;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Log;

class EnviarPedidoPostado implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The EnvioPedido instance.
     *
     * @var EnvioPedido
     */
    protected EnvioPedido $envio;

    /**
     * Create a new job instance.
     */
    protected User $user;

    public function __construct(EnvioPedido $envio, User $user)
    {
        $this->envio = $envio;
        $this->user  = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Pedido postado para o usuário: ' . $this->envio);

        $this->user->notify(new sendPedidoPostado($this->envio, $this->user));

    }
}
