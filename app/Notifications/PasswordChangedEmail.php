<?php

namespace App\Notifications;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordChangedEmail extends Notification
{
    use Queueable;

    protected $user;

    /**
     * AccountCreated constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }


    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $appName = (app()->environment() == 'local' ? getenv('APP_NAME') : config('app.name'));
        $subject = trans('mail.password_changed') . " - " . $appName;

        return (new MailMessage)
            ->subject($subject)
            ->greeting(trans('mail.hello'))
            ->line(trans('mail.password_changed'))
            ->action(trans('mail.you_can_now_login'), env('URL_FRONTEND_BASE'))
            ->line($appName);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}