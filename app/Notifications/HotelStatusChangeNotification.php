<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HotelStatusChangeNotification extends Notification
{
    use Queueable;

    public $status; // 1 = active, 0 = inactive
    public $hotelName;

    /**
     * Create a new notification instance.
     */
    public function __construct($status, $hotelName)
    {
        $this->status = $status;
        $this->hotelName = $hotelName;
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
      if ($this->status == 1) {
            return (new MailMessage)
                ->subject('✅ Your Hotel Registration Approved')
                ->greeting('Hello ' . $this->hotelName . ',')
                ->line('Good news! Your hotel registration has been **approved**.')
                ->line('You can now log in to your account and start using our services.')
                ->action('Login Now', url('/login'))
                ->line('Thank you for choosing ' . config('app.name') . '!');
        } else {
            return (new MailMessage)
                ->subject('⚠️ Your Hotel Account Has Been Deactivated')
                ->greeting('Hello ' . $this->hotelName . ',')
                ->line('Your hotel account has been **deactivated**.')
                ->line('If you believe this is a mistake or want to activate your account again, please contact the admin.')
                ->line('Thank you.');
        }
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
