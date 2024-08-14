<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\sendNovoUsuario;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\{ShouldQueue};
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};

class EnviarNovoUsuario implements ShouldQueue
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
    protected $user;

    /**
     * Create a new job instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->user->notify(new sendNovoUsuario($this->user));
    }
}
