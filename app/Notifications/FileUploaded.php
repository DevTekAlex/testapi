<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FileUploaded extends Notification
{
    use Queueable;

    private $fileData;

    /**
     * Create a new notification instance.
     */
    public function __construct($fileData)
    {
        $this->fileData = $fileData;
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
        return (new MailMessage)
            ->line('New file uploaded: ' . $this->fileData->file_name)
            ->line('Description: ' . $this->fileData->description)
            ->action('Download link: ', url('/download/'.$this->fileData->secret_key));
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
