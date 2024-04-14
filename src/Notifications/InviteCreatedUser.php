<?php

namespace ExpertShipping\Spl\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InviteCreatedUser extends Notification
{
    use Queueable;

    protected $activationUrl;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($activationUrl)
    {
        $this->activationUrl = $activationUrl;
        $this->queue = 'notifications';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('Your manager invited your to activate your account.')
            ->action('Activate my Account', $this->activationUrl ?: url("/activate-account/{$notifiable->activate_account_token}"))
            ->line('Thank you!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
