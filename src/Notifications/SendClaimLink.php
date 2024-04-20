<?php

namespace ExpertShipping\Spl\Notifications;

use ExpertShipping\Spl\Models\Insurance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendClaimLink extends Notification implements ShouldQueue
{
    use Queueable;

    protected Insurance $insurance;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Insurance $insurance)
    {
        $this->insurance = $insurance;
        $this->locale = $insurance->company->local;
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
        $url = env('ES_PLATFORM_URL', env('APP_URL'));
        return (new MailMessage)
                    ->subject(__('ExpertShipping Insurance Claim Link'))
                    ->line(__('To initiate your insurance claim, please click the button below:'))
                    ->action(__('START'), "$url/manage-insurance/{$this->insurance->token}/claim")
                    ->line(__('Thank you for choosing Expert Sipping!'));
    }
}
