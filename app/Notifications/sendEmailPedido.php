<?php

namespace App\Notifications;

use App\Models\Pedido;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class sendEmailPedido extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $pedido;

    /**
     * Create a new notification instance.
     *
     * @param User $user
     * @param Pedido $pedido
     */
    public function __construct(User $user, Pedido $pedido)
    {
        $this->user = $user;
        $this->pedido = $pedido;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param object $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param object $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        Log::info('chegou no email', ['pedido' => $this->pedido->itemPedido]);
        return (new MailMessage)
                    ->view('email.pedidoConfimado', ['user' => $this->user, 'pedido' => $this->pedido]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param object $notifiable
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            // Aqui você pode definir qualquer dado adicional que você queira representar como array
        ];
    }
}
