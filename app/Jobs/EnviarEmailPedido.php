<?php

namespace App\Jobs;

use App\Models\{Pedido, User};
use App\Notifications\sendEmailPedido;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\{ShouldQueue};
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};

class EnviarEmailPedido implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The user instance.
     *
     * @var User
     */
    protected User $user;

    /**
     * The pedido instance.
     *
     * @var Pedido
     */
    protected Pedido $pedido;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param Pedido $pedido
     */
    public function __construct(User $user, Pedido $pedido)
    {
        $this->user   = $user;
        $this->pedido = $pedido;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->user->notify(new sendEmailPedido($this->user, $this->pedido));

    }
}
