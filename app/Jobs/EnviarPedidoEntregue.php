<?php

namespace App\Jobs;

use App\Models\{EnvioPedido, User};
use App\Notifications\sendPedidoEntregue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};

class EnviarPedidoEntregue implements ShouldQueue
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

    protected User $user;

    /**
     * Create a new job instance.
     *
     * @param EnvioPedido $envio
     * @param User $user
     */
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
        $this->user->notify(new sendPedidoEntregue($this->envio, $this->user));

    }
}
