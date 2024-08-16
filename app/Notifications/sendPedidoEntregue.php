<?php

namespace App\Notifications;

use App\Models\{EnvioPedido, User};
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class sendPedidoEntregue extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected EnvioPedido $envio;

    protected User $user;

    public function __construct(EnvioPedido $envio, User $user)
    {
        $this->envio = $envio;
        $this->user  = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Seu pedido foi entregue')
            ->view('email.pedidoEntregue', ['envio' => $this->envio, 'user' => $this->user]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
