<?php

namespace App\Observers;

use App\Jobs\EnviarNovoUsuario;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        EnviarNovoUsuario::dispatch($user);
    }

}
