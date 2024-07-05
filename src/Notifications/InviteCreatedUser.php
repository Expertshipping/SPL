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
            ->subject(__('Final Step to Set Up Your Password on Shippayless'))
            ->greeting(__('Hi :name,', ['name' => $notifiable->name]))
            ->line(__('Welcome to Shippayless!'))
            ->line(__('We are thrilled to have you join our platform. To complete your account setup, there\'s just one simple step left: setting up your password.'))
            ->action(__('Please follow this link to configure your password'), $this->activationUrl ?: url("/activate-account/{$notifiable->activate_account_token}"))
            ->line(__('If you encounter any issues or have any questions, feel free to contact us at support@shippayless.com. We are here to help.'))
            ->line(__('Thank you for your trust and welcome to the Shippayless community.'))
            ->salutation(__('Best regards,'))
            ->salutation(__('The Shippayless Team'));
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
