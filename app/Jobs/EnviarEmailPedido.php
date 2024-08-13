<?php

namespace App\Jobs;

use App\Models\Pedido;
use App\Models\User;
use App\Notifications\sendEmailPedido;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EnviarEmailPedido implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $pedido;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param Pedido $pedido
     */
    public function __construct(User $user, Pedido $pedido)
    {
        $this->pedido = $pedido;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->user->notify(new sendEmailPedido($this->user, $this->pedido));

    }
}
