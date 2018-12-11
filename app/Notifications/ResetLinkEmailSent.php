<?php

namespace App\Notifications;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetLinkEmailSent extends Notification
{
    use Queueable;

    protected $user;
    protected $token;

    /**
     * AccountCreated constructor.
     * @param User $user
     */
    public function __construct(User $user, $token)
    {
        $this->user = $user;
        $this->token = $token;
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
        $subject = trans('mail.reset_password') . " - " . $appName;

        return (new MailMessage)
            ->subject($subject)
            ->greeting(trans('mail.hello'))
            ->line(trans('mail.mail_cause'))
            ->action(trans('mail.reset_password'), env('URL_FRONTEND_BASE') . "/password/reset/{$this->user->token}")
            ->line(trans('mail.reset_password_footer'))
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
