<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CredentialsNotification extends Notification
{
    use Queueable;

    public $name;
    public $email;
    public $password;

    public $phone;

    /**
     * Create a new notification instance.
     */
    public function __construct($name, $email, $password, $phone)
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->phone = $phone;
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
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to ' . config('app.name') . ' - Your Login Credentials')
            ->greeting('Hello ' . $this->name . ',')
            ->line('🎉 Congratulations! Your hotel has been successfully registered with us.')
            ->line('Here are your login credentials:')
            ->line('')
            ->line('**🔑 Email:** ' . $this->email)
            ->line('**📞 Phone Number:** ' . $this->phone)
            ->line('**🔒 Password:** ' . $this->password)
            ->line('')
            ->action('Login Now', url('/login'))
            ->line('For security, please change your password after your first login.')
            ->line('')
            ->line('If you face any issues, feel free to contact our support team.')
            ->salutation('Best Regards,  
        ' . config('app.name'));
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
