<?php

namespace App\Notifications;

use App\Models\{Pedido, User};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class sendEmailPedido extends Notification implements ShouldQueue
{
    use Queueable;

    protected User $user;   // Specify the type for the property

    protected Pedido $pedido; // Specify the type for the property

    /**
     * Create a new notification instance.
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
     * Get the notification's delivery channels.
     *
     * @param object $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param object $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        // Corrected logging to reference the $pedido property
        Log::info('chegou no email', ['pedido' => $this->pedido->itemPedido]);

        return (new MailMessage())
                    ->subject('Pedido confirmado!')
                    ->view('email.pedidoConfimado', ['user' => $this->user, 'pedido' => $this->pedido]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param object $notifiable
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            // Define any additional data to be represented as an array
        ];
    }
}
