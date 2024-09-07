<?php

namespace App\Observers;

use App\Jobs\EnviarNovoUsuario;
use App\Models\User;
use Illuminate\Support\Facades\Redis;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        EnviarNovoUsuario::dispatch($user);
    }

    /**
     * Handle the User "updated" event.
     */

    public function updated(User $user): void
    {
        $usuarioKey = "usuario_{$user->id}";

        if (Redis::exists($usuarioKey)) {

            Redis::del($usuarioKey);
        }

    }

}
